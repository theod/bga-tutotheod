<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * TutoTheod implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\TutoTheod;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

class Game extends \Table
{
    private static array $CARD_TYPES;

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels([
            "my_first_global_variable" => 10,
            "my_second_global_variable" => 11,
            "my_first_game_variant" => 100,
            "my_second_game_variant" => 101,
        ]);        

        self::$CARD_TYPES = [
            1 => [
                "card_name" => clienttranslate('Troll'), // ...
            ],
            2 => [
                "card_name" => clienttranslate('Goblin'), // ...
            ],
            // ...
        ];
    }

    /**
     * Player action, example content.
     *
     * In this scenario, each time a player plays a card, this method will be called. This method is called directly
     * by the action trigger on the front side with `bgaPerformAction`.
     *
     * @throws BgaUserException
     */
    /*
    public function actPlayCard(int $card_id): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // check input values
        $args = $this->argPlayerTurn();
        $playableCardsIds = $args['playableCardsIds'];
        if (!in_array($card_id, $playableCardsIds)) {
            throw new \BgaUserException('Invalid card choice');
        }

        // Add your game logic to play a card here.
        $card_name = self::$CARD_TYPES[$card_id]['card_name'];

        // Notify all players about the card played.
        $this->notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays ${card_name}'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(),
            "card_name" => $card_name,
            "card_id" => $card_id,
            "i18n" => ['card_name'],
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState("playCard");
    }
    */

    public function actThrowDie(): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();
        $player_color = $this->getActivePlayerColor();

        // Get safe random value
        $die_value = $this->getRandomValue([1, 2, 3, 4, 5, 6]);

        // Move player token
        $this->moveToken($player_color, $die_value);

        // Adapt notification message
        if ($die_value > 1) {
            $message = '${player_name} moves his token ${die_value} square';
        }
        else {
            $message = '${player_name} moves his token ${die_value} squares';
        }

        // Notify all players about the move
        $this->notifyAllPlayers("moveToken", clienttranslate($message), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(),
            "die_value" => $die_value
        ]);

        // Go to the next state
        $this->gamestate->nextState("newSquare");
    }

    public function actEndTurn(): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Notify all players about the choice to end turn.
        $this->notifyAllPlayers("endTurn", clienttranslate('${player_name} ends turn'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(),
        ]);

        // At the end of the action, move to the next state
        $this->gamestate->nextState("nextPlayer");
    }

    /**
     * Game state arguments.
     *
     * Those methods return some additional information that is very specific to specific game state.
     *
     * @return array
     * @see ./states.inc.php
     */

    public function argRoundSetup(): array
    {
        // Share tokens positions
        return [
            "tokens" => $this->getCollectionFromDb(
                            "SELECT `token_color` `color`, `square_id` `square`, `slot_id` `slot` FROM `tokens`"
                        )
        ];
    }

    public function argNewSquare(): array
    {
        // Share tokens positions
        return [
            "tokens" => $this->getCollectionFromDb(
                            "SELECT `token_color` `color`, `square_id` `square`, `slot_id` `slot` FROM `tokens`"
                        )
        ];
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    /**
     * Game state actions.
     *
     * The action methods are called everytime the related game state is set.
     * 
     * @see ./states.inc.php
     */

    public function stRoundSetup(): void 
    {
        // BUG? This first state is called before JS interface 'setup' function 
        // and so the 'onEnteringState' function is never called.

        // TODO: if a player wins the last round => he starts on square 5
        // $this->DbQuery( 
        //     "UPDATE tokens SET square_id = 5, slot_id = 5 WHERE token_color = '$winner_id'"
        // );

        $this->gamestate->nextState("returnDie");
    }

    public function stNewSquare(): void 
    {
        $this->gamestate->nextState("nextPlayer");
    }

    public function stNextPlayer(): void 
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Does the player wins?
        $square_id = (int)$this->getActivePlayerSquareId();

        if ($square_id == 32) {

            $this->gamestate->nextState("endGame");
        }
        else {

            // Give some extra time to the active player when he completed an action
            $this->giveExtraTime($player_id);
            
            $this->activeNextPlayer();

            // Go to another gamestate
            // Here, we would detect if the game is over, and in this case use "endGame" transition instead 
            $this->gamestate->nextState("nextPlayer");
        }
    }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
//       if ($from_version <= 1404301345)
//       {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            $this->applyDbUpgradeToAllDB( $sql );
//       }
//
//       if ($from_version <= 1405061421)
//       {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            $this->applyDbUpgradeToAllDB( $sql );
//       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas()
    {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table if you need it.
        $result["players"] = $this->getCollectionFromDb(
            "SELECT `player_id` `id`, `player_score` `score`, `player_color` `color` FROM `player`"
        );

        // Gather all information about current game situation (visible by player $current_player_id).

        // Get players position
        $result['tokens'] = $this->getCollectionFromDb(
            "SELECT `token_color` `color`, `square_id` `square`, `slot_id` `slot` FROM `tokens`"
        );

        return $result;
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "tutotheod";
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the games.
        $gameinfos = $this->getGameinfos();
        $default_colors = array( "86D1F5", "FFF271", "A8ADD7", "F7BFD9", "D1E2AD");

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                array_shift($default_colors),
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);
        }

        // Create players based on generic information.
        //
        // NOTE: You can add extra field on player table in the database (see dbmodel.sql) and initialize
        // additional fields directly here.
        static::DbQuery(
            sprintf(
                "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES %s",
                implode(",", $query_values)
            )
        );

        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        // Dummy content.
        $this->setGameStateInitialValue("my_first_global_variable", 0);

        // Init game statistics.
        //
        // NOTE: statistics used in this file must be defined in your `stats.inc.php` file.

        // Dummy content.
        // $this->initStat("table", "table_teststat1", 0);
        // $this->initStat("player", "player_teststat1", 0);

        // Setup the initial game situation here.
        $this->initMyTables();

        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();
    }

    function initMyTables() {
        // Load tables outside setupNewGame as nothing can be log from it.
        // NOTE: use the game chat to call it with 'initMyTables()' without quotes.
        // Then go to the log page at (change the table number) : 
        // https://studio.boardgamearena.com/1/tutotheod/tutotheod/logaccess.html?table=649157

        try {

            $players = $this->loadPlayersBasicInfos();
            //$this->dump('PLAYERS', $players);

            /*** PASTE CODE TO DEBUG BELOW ***/

            // Init the tokens
            $sql = "INSERT INTO tokens (token_color,square_id,slot_id) VALUES ";
            $sql_values = array();

            $players_id = array_keys($players);
            $square_slots_id = [5, 1, 3, 7, 9]; // Cf comment into "moveToken" function below

            for( $i=0; $i<count($players_id); $i++ )
            {
                $player_color = $players[$players_id[$i]]["player_color"];
                $slot_id = $square_slots_id[$i];

                // TODO: Check if a player is the last President
                $sql_values[] = "('$player_color',0,'$slot_id')";
            }

            $sql .= implode( ',', $sql_values );
            $this->DbQuery( $sql );

        } catch ( Exception $e ) {

            // logging does not actually work in game init :(
            // but if you calling from php chat it will work
            $this->error("Fatal error while creating game");
            $this->dump('err', $e);

        }
    }

    function getActivePlayerColor() {

       $player_id = $this->getActivePlayerId();
       $players = $this->loadPlayersBasicInfos();

       if (isset($players[$player_id]))
           return $players[$player_id]['player_color'];
       else
           return null;
   }
   
    function getActivePlayerSquareId() {

        $token_color = $this->getActivePlayerColor();

        return (int)$this->getUniqueValueFromDB(
            "SELECT square_id FROM tokens WHERE token_color = '$token_color'"
        );
   }
    
    function moveToken( $token_color, $squares_number ) {

        // Get square where the token is
        $square_id = (int)$this->getUniqueValueFromDB(
            "SELECT square_id FROM tokens WHERE token_color = '$token_color'"
        );

        // Add squares number to get new square where to go
        $new_square_id = $square_id + $squares_number;

        // Constrain square id above 0
        if ($new_square_id < 0) {

            $new_square_id = 0;
        }

        // Move token back when it goes too far
        // TODO: Notify about this
        elseif ($new_square_id > 32) {

            $new_square_id = 32 - ($new_square_id - 32);
        }

        /* Tokens are placed over square's slots like this:
                
                            1 2 3      2 0 3
                            4 5 6  ->  0 1 0
                            7 8 9      4 0 5
                            slots      tokens

            The first arriving token is always stored on central slot (5) 
            while others are stored around starting from the upper left slot (1).
        */
        $square_slots_id = [5, 1, 3, 7, 9];

        // How many tokens are already in the new square
        $tokens_count = (int)$this->getUniqueValueFromDB(
            "SELECT COUNT(token_color) FROM tokens WHERE square_id = '$new_square_id'"
        );

        $new_slot_id = $square_slots_id[ $tokens_count ];

        // Update token's square and slot
        $this->DbQuery( 
            "UPDATE tokens SET square_id = '$new_square_id', slot_id = '$new_slot_id' WHERE token_color = '$token_color'"
        );
   }

    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: pass).
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, otherwise it will fail with a
     * "Not logged" error message.
     *
     * @param array{ type: string, name: string } $state
     * @param int $active_player
     * @return void
     * @throws feException if the zombie mode is not supported at this game state.
     */
    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default:
                {
                    $this->gamestate->nextState("zombiePass");
                    break;
                }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }

    private function getRandomValue(array $array)
    {
        $size = count($array);
        if ($size == 0) {
            trigger_error("getRandomValue(): Array is empty", E_USER_WARNING);
            return null;
        }
        $rand = random_int(0, $size - 1);
        $slice = array_slice($array, $rand, 1, true);
        foreach ($slice as $key => $value) {
            return $value;
        }
    }
}
