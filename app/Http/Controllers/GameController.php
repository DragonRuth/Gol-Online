<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;
use Illuminate\Support\Facades\DB;
use App\Classes\Game\GOL;

class GameController extends Controller
{
    public function joinGame()
    {
    	$room_found = DB::select('select * from rooms where numberOfPlayers < ?', [3]);
        if ($room_found != NULL) 
        { 
            $room_id = $room_found[0]->id;
            $room_numberOfPlayers = $room_found[0]->numberOfPlayers;
            $playersState[1] = $room_found[0]->player1State;
            $playersState[2] = $room_found[0]->player2State;
            $playersState[3] = $room_found[0]->player3State;
            $hostAlreadyExists = false;
            $hostStatus = '';
            $playerNumber = 0;

            foreach ($playersState as $key => $value) 
            {
                if ($value == 0 && $playerNumber == 0) 
                    {
                        $playerNumber = $key;
                    }
                if ($value == 2)
                    {
                        $hostAlreadyExists = true;
                    }
            }

            $playersState[$playerNumber] = 1;

            if (! $hostAlreadyExists) 
            {
                $hostStatus = 'Host';
                $playersState[$playerNumber] = 2;
            }



        	$affected = DB::update('update rooms set numberOfPlayers = ?, player1State = ?,  player2State = ?,  player3State = ? where id = ?', [$room_numberOfPlayers+1, $playersState[1], $playersState[2], $playersState[3], $room_id]);
        	return view('game.index', ['room' => $room_id, 'player' => $playerNumber, 'hostStatus' => $hostStatus]);
        } 
        else
        {
			return view('errors.norooms');
        }
        	
    }


    public function addCell(Request $request)
    {
    	$player_id = $request->input('player');
    	$cell = $request->input('cell');
    	$room = $request->input('room');


    	$room_found =  DB::select('select * from rooms where id = ?', [$room]);
    	$state = $room_found[0]->roomState;
    	$state_decoded = json_decode($state,true);
    	$state_decoded[(int)$cell - 1] = (int)$player_id;
    	$state_new = json_encode($state_decoded);

    	$affected = DB::update('update rooms set roomState = ? where id = ?', [$state_new, $room]);
    }

    public function updateTurn(Request $request)
    {
    	$turn = $request->input('turnUp');
    	$room = $request->input('room');

    	$room_found =  DB::select('select * from rooms where id = ?', [$room]);
    	$room_turn = $room_found[0]->roomTurn;
        if ($room_turn == 100) $room_turn = 0;
    	$affected = DB::update('update rooms set roomTurn = ? where id = ?', [$room_turn + 10, $room]);

    }

        public function startGame(Request $request)
    {
    	$room = $request->input('room');

    	$room_found =  DB::select('select * from rooms where id = ?', [$room]);

        $state = [];

        for ($i = 0; $i < 3800; $i++) {
                $state[$i] = 0;
        }  		

        $state[3800] = 0;
        $state[3801] = 0;
        $state[3802] = 0;
        

        $state_encoded = json_encode($state);
        $affected = DB::update('update rooms set roomState = ? where id = ?', [$state_encoded, $room]);

    }

        public function loadGame(Request $request)
    {
        $room = $request->input('room');

        $room_found =  DB::select('select * from rooms where id = ?', [$room]);

        return response()->json(['roomturn' => $room_found[0]->roomTurn]);
    }

    public function loadGrid(Request $request)
    {
        $room = $request->input('room');
        $player = $request->input('player');
        $host = $request->input('host');
        $room_found =  DB::select('select * from rooms where id = ?', [$room]);

        $state = $room_found[0]->roomState;

        $state_decoded = json_decode($state, true); //Массив содержащий в себе информацию
        if ($host == 'Host') 
        {
            $gol = new GOL(76,50,3);
            $gol->setPoints($state_decoded);
            $gol->step();
            $state_new = $gol->output();
        } else 
        {
            $state_new = $state_decoded;
        }


        $firstPlayer = $state_new[3800];
        $secondPlayer = $state_new[3801];
        $thirdPlayer = $state_new[3802];

    


        if ($host == 'Host') 
        {
           $affected = DB::update('update rooms set roomState = ? where id = ?', [json_encode($state_new), $room]); 
        }

        unset($state_new[3800]);
        unset($state_new[3801]);
        unset($state_new[3802]);


        return response()->json(['state' => $state_new,'fpscore' => $firstPlayer, 'spscore' => $secondPlayer, 'tpscore' => $thirdPlayer]);

    }

    public function leaveGame(Request $request)
    {
         $room = $request->input('room');
         $player = $request->input('player');
         $room_found = DB::select('select * from rooms where id = ?', [$room]);
         $room_numberOfPlayers = $room_found[0]->numberOfPlayers;

         $playersState[1] = $room_found[0]->player1State;
         $playersState[2] = $room_found[0]->player2State;
         $playersState[3] = $room_found[0]->player3State;

        $deleteState = $room_found[0]->roomState; 

        $state_dec = json_decode($deleteState, true);

        $state_dec[3799 + $player] = 0;


        for ($i = 0; $i < 3800; $i++) 
        {
            if ($state_dec[$i] == $player) 
            {
                $state_dec[$i] = 0;
            }
        }


        $state_enc = json_encode($state_dec);
        $affected = DB::update('update rooms set roomState = ? where id = ?', [$state_enc, $room]);


        if (($room_numberOfPlayers - 1) == 0) 
         {
            $state = [];

            for ($i = 0; $i < 3800; $i++) 
            {
                $state[$i] = 0;
            }       

            $state[3800] = 0;
            $state[3801] = 0;
            $state[3802] = 0;
        

            $state_encoded = json_encode($state);
            $affected = DB::update('update rooms set roomState = ? where id = ?', [$state_encoded, $room]);
        }

        $newHostNumber = 0;

        if ($playersState[$player] == 2) 
        {
            foreach ($playersState as $key => $value) 
            {
                if ($value == 1) 
                { 
                    $newHostNumber = $key;
                    break;
                }
            }

        }

        if ($newHostNumber != 0 ) 
        {
        $playersState[$newHostNumber] = 2;
        }

        $playersState[$player] = 0;
        $affected = DB::update('update rooms set numberOfPlayers = ?, player1State = ?, player2State = ?,  player3State = ? where id = ?', [$room_numberOfPlayers - 1, $playersState[1], $playersState[2], $playersState[3], $room]);
    
    } 


    public function ChangeHost(Request $request) 
    {
        $room = $request->input('room');
        $player = $request->input('player');
        $room_found = DB::select('select * from rooms where id = ?', [$room]);

        $playersState[1] = $room_found[0]->player1State;
        $playersState[2] = $room_found[0]->player2State;
        $playersState[3] = $room_found[0]->player3State;

        $currentHost = 0;

        foreach ($playersState as $key => $value) 
        {
            if ($value == 2) 
            { 
                $currentHost = $key;
            }
        }

        return response()->json(['host' => $currentHost]);
    }


}
