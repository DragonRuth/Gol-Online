@extends('layouts.app')

@section('content')
<div class="jumbotron">
  <div class="container">
    <div class="page-header">
        <h1>Game of Life Online</h1>
        <p> A multiplayer version of famous Convay's Game of Life
        </p>
    </div>
    <div class="container"> 
        <h2>Simple Rules</h2>
        <p>A cell lives, if it has 2 alive neighbors and is born if it has 3 alive neighbors around. 
            If you have more alive cells around then your opponnet - cell is yours! 
            Red cells need less alive neighbors and blue need more.</p>
    </div>
    <div class="container">
        <h2>Notible Figures</h2>
        <p>You may find it helpful to know these figures, as they are as simple, as they are amazing!</p>
    </div>
    <div class="row">
        <div class="col-sm-4 col-md-4">
                <img src="images/Rpento.svg.png" alt="No Image">
                    <div class="caption">
                    <h3>R-Pentomino</h3>
                    <p>A simple, long living structure</p>
                    </div>
        </div>
        <div class="col-sm-4 col-md-4">
                <img src="images/Diehard.svg.png" alt="No Image">
                    <div class="caption">
                    <h3>Diehard</h3>
                    <p>Takes 130 genrations, before it finally disappers</p>
                    </div>
        </div>
        <div class="col-sm-4 col-md-4">
                <img src="images/Acorn.svg.png" alt="No Image">
                    <div class="caption">
                    <h3>Acorn</h3>
                    <p>Lives for 5206 generatoins!</p>
                    </div>
        </div>
    </div>
   </div>
</div>
   <div class="container" align= "center">
     <a href="{{ url('/game') }}" type="button" class="btn btn-success btn-lg">Play</a>
   </div>
@endsection