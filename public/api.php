<?php
session_start();

class YatzyGame {
    public $diceValues = [1, 1, 1, 1, 1];
    public $rollsRemaining = 3;
    public $score = 0;
    public $selectedCategory = null;
    public $leaderboard = [];
    public $heldDice = [];
}

// Ensure the class is defined before accessing session data
if (!isset($_SESSION['game']) || !($_SESSION['game'] instanceof YatzyGame)) {
    $_SESSION['game'] = new YatzyGame();
}

$game = $_SESSION['game'];

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'roll':
            rollDice($game);
            break;
        case 'score':
            scoreDice($game);
            break;
        case 'selectCategory':
            selectCategory($game);
            break;
        case 'reset':
            resetGame($game);
            break;
        case 'getGameState':
            getGameState($game);
            break;
        case 'getLeaderboard':
            getLeaderboard($game);
            break;
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

function rollDice($game) {
    if ($game->rollsRemaining > 0) {
        for ($i = 0; $i < count($game->diceValues); $i++) {
            if (!in_array($i, $game->heldDice)) {
                $game->diceValues[$i] = rand(1, 6);
            }
        }
        $game->rollsRemaining--;
        $_SESSION['game'] = $game;
        echo json_encode($game);
    }
}

function scoreDice($game) {
    if ($game->rollsRemaining < 3 && $game->selectedCategory) {
        $turnScore = 0;
        $category = $game->selectedCategory;
        $diceValues = $game->diceValues;
        switch ($category) {
            case 'threeOfAKind':
                $turnScore = calculateThreeOfAKind($diceValues);
                break;
            case 'fourOfAKind':
                $turnScore = calculateFourOfAKind($diceValues);
                break;
            case 'fullHouse':
                $turnScore = calculateFullHouse($diceValues);
                break;
            case 'smallStraight':
                $turnScore = calculateSmallStraight($diceValues);
                break;
            case 'largeStraight':
                $turnScore = calculateLargeStraight($diceValues);
                break;
            case 'yahtzee':
                $turnScore = calculateYahtzee($diceValues);
                break;
            case 'chance':
                $turnScore = calculateChance($diceValues);
                break;
        }
        $game->score += $turnScore;
        $game->leaderboard[] = [
            'category' => $category,
            'score' => $turnScore
        ];

        resetDice($game);
        $_SESSION['game'] = $game;
        echo json_encode($game);
    }
}

function selectCategory($game) {
    $game->selectedCategory = $_GET['category'];
    $_SESSION['game'] = $game;
    echo json_encode(['status' => 'success']);
}

function resetGame($game) {
    $game->diceValues = [1, 1, 1, 1, 1];
    $game->rollsRemaining = 3;
    $game->score = 0;
    $game->selectedCategory = null;
    $game->leaderboard = [];
    $game->heldDice = [];
    $_SESSION['game'] = $game;
    echo json_encode($game);
}

function getGameState($game) {
    echo json_encode($game);
}

function getLeaderboard($game) {
    usort($game->leaderboard, function ($a, $b) {
        return $b['score'] - $a['score'];
    });
    echo json_encode(array_slice($game->leaderboard, 0, 10));
}

function resetDice($game) {
    $game->diceValues = [1, 1, 1, 1, 1];
    $game->rollsRemaining = 3;
    $game->selectedCategory = null;
    $game->heldDice = [];
    $_SESSION['game'] = $game;
}

function calculateThreeOfAKind($diceValues) {
    $counts = array_count_values($diceValues);
    foreach ($counts as $val => $count) {
        if ($count >= 3) {
            return array_sum($diceValues);
        }
    }
    return 0;
}

function calculateFourOfAKind($diceValues) {
    $counts = array_count_values($diceValues);
    foreach ($counts as $val => $count) {
        if ($count >= 4) {
            return array_sum($diceValues);
        }
    }
    return 0;
}

function calculateFullHouse($diceValues) {
    $counts = array_count_values($diceValues);
    if (in_array(3, $counts) && in_array(2, $counts)) {
        return 25;
    }
    return 0;
}

function calculateSmallStraight($diceValues) {
    $straights = [
        [1, 2, 3, 4],
        [2, 3, 4, 5],
        [3, 4, 5, 6]
    ];
    foreach ($straights as $straight) {
        if (count(array_intersect($straight, $diceValues)) == 4) {
            return 30;
        }
    }
    return 0;
}

function calculateLargeStraight($diceValues) {
    $straights = [
        [1, 2, 3, 4, 5],
        [2, 3, 4, 5, 6]
    ];
    foreach ($straights as $straight) {
        if (count(array_intersect($straight, $diceValues)) == 5) {
            return 40;
        }
    }
    return 0;
}

function calculateYahtzee($diceValues) {
    if (count(array_unique($diceValues)) == 1) {
        return 50;
    }
    return 0;
}

function calculateChance($diceValues) {
    return array_sum($diceValues);
}
