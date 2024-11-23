/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * TutoTheod implementation : © <Théo de la Hogue> <dev@theod.fr>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * tutotheod.js
 *
 * TutoTheod user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.tutotheod", ebg.core.gamegui, {
        constructor: function(){
            console.log('tutotheod constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

            game_play_area = document.getElementById('game_play_area')

            // Create board
            game_play_area.insertAdjacentHTML('beforeend', `
                <div id="board">
                </div>
            `);

            // Create squares
            const board = document.getElementById('board');
            const size = 64;
            const ids = [
                    [ 5,  6,  7,  8,  9, 10], // y = 0
                    [ 4, 23, 24, 25, 26, 11], // y = 1
                    [ 3, 22, 33, 34, 27, 12], // y = 2
                    [ 2, 21, 32, 35, 28, 13], // y = 3
                    [ 1, 20, 31, 30, 29, 14], // y = 4
                    [ 0, 19, 18, 17, 16, 15], // y = 5
            ]; // x = 0,  1,  2,  3,  4,  5

            for (let x=0; x<6; x++) {

                for (let y=0; y<6; y++) {

                    const left = x * size;
                    const top = y * size;

                    board.insertAdjacentHTML(`beforeend`, `
                        <div id="square_${ids[x][y]}" class="square">
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                            <div class="slot"></div>
                        </div>
                    `);
                }
            }

            // Create dice
            //var dice_x = Math.floor(Math.random() * 6) + 1;
            var dice_value = Math.floor(Math.random() * 6) + 1;

            // DEBUG
            console.log( "random dice value", dice_value );

            board.insertAdjacentHTML('beforeend', `
                <div class="dice" id="dice" data-value="${dice_value}" style="top: -50px; left: -50px;">
                </div>
            `);

            document.getElementById('dice').addEventListener('click', event => this.onClickDice(event));
            
            // Setting up player boards
            /*
            Object.values(gamedatas.players).forEach(player => {

                // Example of setting up players boards
                this.getPlayerPanelElement(player.id).insertAdjacentHTML('beforeend', `
                    <div id="player-counter-${player.id}">A player counter</div>
                `);

                // Example of adding a div for each player
                board.insertAdjacentHTML('beforeend', `
                    <div id="board-${player.id}">
                        <strong>${player.name}</strong>
                        <div>Player zone content goes here</div>
                    </div>
                `);
            });
            */
            
            // Set up your game interface here, according to "gamedatas"


            // DEBUG
            console.log( "gamedatas.tokens", Object.values(gamedatas.tokens) );

            Object.values(gamedatas.tokens).forEach(token => {

                // Create token
                board.insertAdjacentHTML('beforeend', `
                    <div class="token_wrapper" id="token_${token.color}">
                        <div class="token" data-color="${token.color}"">
                        </div>
                    </div>
                `);

                // Animate token from player board to a square slot
                this.placeOnObject( `token_${token.color}`, 'overall_player_board_'+player );
                this.placeTokenOnSquareSlot( token.color, token.square, token.slot );
            });

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName, args );
            
            switch( stateName )
            {
                case 'playerTurn':

                    //this.highligthActivePlayerToken();
                    break;

                case 'newSquare':

                    // Update tokens position
                    for (const [key, token] of Object.entries(args.args.tokens)) {

                        this.placeTokenOnSquareSlot( token.color, token.square, token.slot );
                    }

                    break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );
                
                break;
           */
           
           
            case 'dummy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName, args );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
                 case 'playerTurn':    

                    this.addActionButton('actThrowDice-btn', _('Throw dice'), () => this.bgaPerformAction("actThrowDice"), null, null, 'gray'); 
                    this.addActionButton('actEndTurn-btn', _('End turn'), () => this.bgaPerformAction("actEndTurn"), null, null, 'gray'); 
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        getActivePlayerColor: function()
        {
            return this.gamedatas.players[this.getActivePlayerId()].color;
        },

        placeTokenOnSquareSlot: function( color, square, slot )
        {
            /* Tokens are placed over square's slots like this:
                
                            1 2 3      2 0 3
                            4 5 6  ->  0 1 0
                            7 8 9      4 0 5
                            slots      tokens

                The first arriving token is always stored on central slot (5) 
                while others are stored around starting from the upper left slot (1).
            */
            console.log( 'putTokenOnSquare:', color, square );

            var token = document.getElementById('token_'+color);
            var square_slot = document.getElementById('square_'+square).children[slot];

            this.slideToObject( token, square_slot ).play();
        },

        highligthActivePlayerToken: function()
        {
            var activePlayerColor = this.getActivePlayerColor();
            console.log( 'highligthActivePlayerToken:', activePlayerColor );

            // Clear former active token
            document.querySelectorAll('.active').forEach(token => token.classList.remove('active'));

            // Add new active token
            document.getElementById('token_'+activePlayerColor).firstElementChild.classList.add('active');        
                        
            this.addTooltipToClass( 'activePlayerToken', '', _('This is the active player token.') );
        },


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */

        onClickDice: function( event )
        {
            console.log( 'onClickDice', event );

            // Stop this event propagation
            event.preventDefault();
            event.stopPropagation();

            this.bgaPerformAction("actThrowDice");
        },    

        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your tutotheod.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
