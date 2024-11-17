/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * TutoTheod implementation : © <Your name here> <Your email address here>
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

            // Create board
            document.getElementById('game_play_area').insertAdjacentHTML('beforeend', `
                <div id="board">
                </div>
            `);

            // Create squares
            const board = document.getElementById('board');
            const size = 64;
            for (let x=0; x<6; x++) {
                for (let y=0; y<6; y++) {
                    const left = x * size;
                    const top = y * size;
                    // we use afterbegin to make sure squares are placed before discs
                    board.insertAdjacentHTML(`afterbegin`, `
                        <div id="square_${x}_${y}" class="square">
                        </div>
                    `);
                }
            }
            
            // Setting up player boards
            Object.values(gamedatas.players).forEach(player => {
                // example of setting up players boards
                this.getPlayerPanelElement(player.id).insertAdjacentHTML('beforeend', `
                    <div id="player-counter-${player.id}">A player counter</div>
                `);

                // example of adding a div for each player
                document.getElementById('board').insertAdjacentHTML('beforeend', `
                    <div id="board-${player.id}">
                        <strong>${player.name}</strong>
                        <div>Player zone content goes here</div>
                    </div>
                `);
            });
            
            // Set up your game interface here, according to "gamedatas"

            //colors = Object.values(players).map(function(player) { return player.color; });
 
            one_color = [
                "D1E2AD" // vert
            ];

            two_colors = [
                "D1E2AD", // vert
                "F7BFD9" // rose
            ];

            three_colors = [
                "D1E2AD", // vert
                "F7BFD9", // rose
                "FFF271" // jaune
            ];

            four_colors = [
                "D1E2AD", // vert
                "F7BFD9", // rose
                "FFF271", // jaune
                "86D1F5" // bleu
            ];

            five_colors = [
                "D1E2AD", // vert
                "F7BFD9", // rose
                "FFF271", // jaune
                "86D1F5", // bleu
                "A8ADD7"  // violet
            ];

            this.putTokensOnSquare( 0, 5, five_colors );
 
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
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
           
            case 'dummy':
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
                    const playableCardsIds = args.playableCardsIds; // returned by the argPlayerTurn

                    // Add test action buttons in the action status bar, simulating a card click:
                    playableCardsIds.forEach(
                        cardId => this.addActionButton(`actPlayCard${cardId}-btn`, _('Play card with id ${card_id}').replace('${card_id}', cardId), () => this.onCardClick(cardId))
                    ); 

                    this.addActionButton('actPass-btn', _('Pass'), () => this.bgaPerformAction("actPass"), null, null, 'gray'); 
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */

        putTokensOnSquare: function( x, y, colors )
        {
            console.log( 'putPlayersOnSquare: colors', colors );

            // Tokens position depend on the number of players
            const configurations = [
                [
                    0,0,0,
                    0,1,0,
                    0,0,0
                ],
                [
                    2,0,0,
                    0,1,0,
                    0,0,0
                ],
                [
                    2,0,3,
                    0,1,0,
                    0,0,0
                ],
                [
                    2,0,3,
                    0,1,0,
                    4,0,0
                ],
                [
                    2,0,3,
                    0,1,0,
                    4,0,5
                ]
            ];

            configurations[colors.length-1].forEach(index => {

                if (index == 0) {

                    document.getElementById('square_'+x+'_'+y).insertAdjacentHTML('afterbegin', `
                        <div class="token_wrapper">
                            <div class="token_separator"
                            </div>
                        </div>
                    `);
                }
                else {

                    document.getElementById('square_'+x+'_'+y).insertAdjacentHTML('afterbegin', `
                        <div class="token_wrapper" id="token_${colors[index-1]}">
                            <div class="token" data-color="${colors[index-1]}">
                            </div>
                        </div>
                    `);
                }
                
            });
            
            //this.placeOnObject( `token_${color}`, 'square_'+x+'_'+y );
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
        
        // Example:
        
        onCardClick: function( card_id )
        {
            console.log( 'onCardClick', card_id );

            this.bgaPerformAction("actPlayCard", { 
                card_id,
            }).then(() =>  {                
                // What to do after the server call if it succeeded
                // (most of the time, nothing, as the game will react to notifs / change of state instead)
            });        
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
