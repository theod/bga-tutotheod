<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * TutoTheod implementation : © <Théo de la Hogue> <dev@theod.fr>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * TutoTheod game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   States types:

       _ activeplayer: in this type of state, we expect some action from the active player.

       _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)

       _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.

       _ manager: special type for initial and final state

   Arguments of game states:

       _ name: the name of the GameState, in order you can recognize it on your own code.

       _ description: the description of the current game state is always displayed in the action status bar on the top of the game. Most of the time this is useless for game state with "game" type.

       _ descriptionmyturn: the description of the current game state when it's your turn.

       _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)

       _ action: name of the method to call when this game state become the current game state. Usually, the action method is prefixed by "st" (ex: "stMyGameStateName").

       _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction" method on both client side (Javacript: this.checkAction) and server side (PHP: $this->checkAction). Actions are called from the front with bgaPerformAction, and matched to the function on the game.php file.

       _ transitions: the transitions are the possible paths to go from a game state to another. You must name transitions in order to use transition names in "nextState" PHP method, and use IDs to specify the next game state for each transition.

       _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the client side to be used on "onEnteringState" or to set arguments in the gamestate description.

       _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

require_once("modules/php/constants.inc.php");

$machinestates = [

    // The initial state. Please do not modify.

    ST_BGA_GAME_SETUP => array(
        "name" => "gameSetup",
        "description" => clienttranslate("Game setup"),
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => ["" => ST_ROUND_SETUP]
    ),

    // Note: ID=2 => your first state

    ST_ROUND_SETUP => [
        "name" => "roundSetup",
        "description" => clienttranslate('All players put their token on Candidature square.'),
        "type" => "game",
        "action" => "stRoundSetup",
        "transitions" => ["returnDie" => ST_PLAYER_TURN]
    ],

    ST_PLAYER_TURN => [
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must throw the die'),
        "descriptionmyturn" => clienttranslate('${you} must throw the die'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => [
            "actThrowDie",
            "actReturnDie"
        ],
        "transitions" => ["moveToken" => ST_MOVE_TOKEN, "returnDie" => ST_NEXT_PLAYER]
    ],

    ST_MOVE_TOKEN => [
        "name" => "moveToken",
        "description" => clienttranslate('${actplayer} token is moving'),
        "type" => "game",
        "args" => "argMoveToken",
        "action" => "stMoveToken",
        "transitions" => ["moveTokenBack" => ST_MOVE_TOKEN, "endOfMove" => ST_END_OF_MOVE]
    ],

    ST_END_OF_MOVE => [
        "name" => "endOfMove",
        "description" => clienttranslate('${actplayer} token movement is done'),
        "type" => "game",
        "action" => "stEndOfMove",
        "transitions" => ["playAgain" => ST_PLAYER_TURN, "playerWin" => ST_END_ROUND]
    ],

    ST_NEXT_PLAYER => [
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,
        "transitions" => ["nextPlayer" => ST_PLAYER_TURN]
    ],

    ST_END_ROUND => [
        "name" => "endRound",
        "description" => clienttranslate('${actplayer} must decide to continue or end the game'),
        "descriptionmyturn" => clienttranslate('${you} must decide to continue or end the game'),
        "type" => "activeplayer",
        "args" => "argEndRound",
        "possibleactions" => [
            "actContinue",
            "actEndGame"
        ],
        "transitions" => ["newRound" => ST_ROUND_SETUP, "endGame" => ST_END_GAME]
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_END_GAME => [
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],

];



