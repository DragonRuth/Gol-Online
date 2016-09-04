<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['roomState','roomTurn','numberOfPlayers', 'player1State', 'player2State', 'player3State'];
}
 