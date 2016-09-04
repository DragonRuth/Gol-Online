@extends('layouts.app')

@section('content')
<div class="container" >
	<div class="row">
  		<div class="col-md-2"><h4>You are a player №</h4></div>
  		<div class="col-md-1"><h4 id="Player_id">{{$player}}</h4></div>
      <div class="col-md-1"><h4 id="Host_Status">{{$hostStatus}}</h4></div>
  	</div>
	<div class = "row">
		<div class="col-md-3"><h3>SCORES:</h3></div>
		<div class="col-md-3"><h3 id="first_player" >1:</h3></div>
		<div class="col-md-3"><h3 id="second_player">2:</h3></div>
		<div class="col-md-3"><h3 id="third_player">3:</h3></div>
	</div>
</div>
<div class="container" align="center">
        <canvas id="myCanvas" width="1140" height="750" style="border:2px solid #000000;"></canvas>
</div>
<div class="container" align="center">
<div class="progress">
  <div class="progress-bar" id="myProgressBar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
    <span class="sr-only">60% Complete</span>
  </div>
</div> 
</div>
<div class="container" >
  <div class="row">
    <div class="col-md-2"> <h3>ROOM:</h3></div>
    <div class="col-md-2"> <h3 id = "Room_id">{{$room}}</h3></div>
    <div class="col-md-2"> <h3>Free Cells:</h3></div>
    <div class="col-md-2"> <h3 id = "Cells_id"></h3></div>
  </div>
</div>
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="{{ asset('js/game.js') }}">

</script>
@endsection
