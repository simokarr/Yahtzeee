document.addEventListener('DOMContentLoaded', () => {
    const diceContainer = document.getElementById('dice-container');
    const rollButton = document.getElementById('roll-button');
    const scoreButton = document.getElementById('score-button');
    const scoreboardBody = document.getElementById('scoreboard-body');
    const categoryButtons = document.querySelectorAll('.category-button');

    let selectedCategory = null;

    function rollDice() {
        fetch('api.php?action=roll')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.error) {
                        console.error(data.error);
                    } else {
                        updateGameState(data);
                    }
                } catch (error) {
                    console.error('Failed to parse JSON:', error, text);
                }
            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    }

    function toggleHold(event) {
        if (event.target.closest('.dice')) {
            event.target.closest('.dice').classList.toggle('held');
        }
    }

    function scoreDice() {
        if (selectedCategory) {
            fetch(`api.php?action=score&category=${selectedCategory}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.error) {
                            console.error(data.error);
                        } else {
                            updateGameState(data);
                        }
                    } catch (error) {
                        console.error('Failed to parse JSON:', error, text);
                    }
                })
                .catch(error => console.error('There was a problem with the fetch operation:', error));
        }
    }

    function selectCategory(event) {
        selectedCategory = event.target.dataset.category;
        scoreButton.disabled = false;
        fetch(`api.php?action=selectCategory&category=${selectedCategory}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                } else {
                    console.log(data);
                }
            });
    }

    function updateGameState(gameState) {
        const diceValues = gameState.diceValues;
        for (let i = 0; i < diceContainer.children.length; i++) {
            diceContainer.children[i].querySelector('img').src = `dice_pictures/${diceValues[i]}.png`;
        }
        rollButton.textContent = `Roll Dice (${gameState.rollsRemaining} rolls remaining)`;
        scoreboardBody.innerHTML = '';
        gameState.leaderboard.forEach(entry => {
            const row = document.createElement('tr');
            row.innerHTML = `<td>${entry.category}</td><td>${entry.score}</td>`;
            scoreboardBody.appendChild(row);
        });
        scoreButton.disabled = gameState.rollsRemaining === 3;
        selectedCategory = null;
    }

    function fetchGameState() {
        fetch('api.php?action=getGameState')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                } else {
                    updateGameState(data);
                }
            })
            .catch(error => console.error('There was a problem with the fetch operation:', error));
    }

    rollButton.addEventListener('click', rollDice);
    scoreButton.addEventListener('click', scoreDice);
    diceContainer.addEventListener('click', toggleHold);
    categoryButtons.forEach(button => button.addEventListener('click', selectCategory));

    // Fetch the game state when the page loads
    fetchGameState();
});
