<?php
class Game {
    public $dice;
    public $rollsRemaining;
    public $score;
    public $selectedCategory;

    public function __construct() {
        $this->dice = [1, 1, 1, 1, 1];
        $this->rollsRemaining = 3;
        $this->score = 0;
        $this->selectedCategory = null;
    }

    public function rollDice() {
        if ($this->rollsRemaining > 0) {
            for ($i = 0; $i < count($this->dice); $i++) {
                $this->dice[$i] = rand(1, 6);
            }
            $this->rollsRemaining--;
        }
    }

    public function calculateScore($category) {
        // Scoring logic based on the selected category yurr
        $score = 0;
        $counts = array_count_values($this->dice);

        switch ($category) {
            case 'threeOfAKind':
                foreach ($counts as $count) {
                    if ($count >= 3) {
                        $score = array_sum($this->dice);
                        break;
                    }
                }
                break;
            case 'fourOfAKind':
                foreach ($counts as $count) {
                    if ($count >= 4) {
                        $score = array_sum($this->dice);
                        break;
                    }
                }
                break;
            case 'fullHouse':
                if (in_array(3, $counts) && in_array(2, $counts)) {
                    $score = 25;
                }
                break;
            case 'smallStraight':
                $straights = [
                    [1, 2, 3, 4],
                    [2, 3, 4, 5],
                    [3, 4, 5, 6]
                ];
                foreach ($straights as $straight) {
                    if (count(array_intersect($this->dice, $straight)) === 4) {
                        $score = 30;
                        break;
                    }
                }
                break;
            case 'largeStraight':
                $straights = [
                    [1, 2, 3, 4, 5],
                    [2, 3, 4, 5, 6]
                ];
                foreach ($straights as $straight) {
                    if (count(array_intersect($this->dice, $straight)) === 5) {
                        $score = 40;
                        break;
                    }
                }
                break;
            case 'yahtzee':
                if (count($counts) === 1) {
                    $score = 50;
                }
                break;
            case 'chance':
                $score = array_sum($this->dice);
                break;
        }

        $this->score += $score;
        $this->resetTurn();
        return $score;
    }

    private function resetTurn() {
        $this->dice = [1, 1, 1, 1, 1];
        $this->rollsRemaining = 3;
        $this->selectedCategory = null;
    }
}
?>
