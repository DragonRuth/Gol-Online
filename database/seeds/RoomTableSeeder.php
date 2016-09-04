<?php

use Illuminate\Database\Seeder;

class RoomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
   			  DB::table('rooms')->insert([
            'roomState' => '',
            'roomTurn' => 0,
            'numberOfPlayers' => 0,
            'player1State' => 0, 
            'player2State' => 0, 
            'player3State' => 0, 
        ]);
		}
    }
}
