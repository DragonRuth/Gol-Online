var canvas = document.getElementById('myCanvas'),
	canvLeft = canvas.offsetLeft,
    canvTop = canvas.offsetTop,
    cellSize = 15,
    ctx = canvas.getContext('2d'),
    cells = [],
    myColor = "#FF6363",
    playersColors = [getRandomColor(),getRandomColor(),getRandomColor()],
    newCellColor = "#DDFF00",
    Z1cellColor = "#E00B4B",
    Z_1cellColor = "#004CFF",
    myRoomNumber = document.getElementById('Room_id').innerHTML,
    myPlayerNumber = document.getElementById('Player_id').innerHTML, 
    turn = 0,    
    timeForNewDot = 0,
    myDotsAvalable = 7,
    Highscores = [],
    startOfthegame = false,
    timerIdNotaHost = 0,
    hostStatus = document.getElementById('Host_Status').innerHTML, 
    conn = new WebSocket('ws://localhost:8080');


canvas.addEventListener("click", function(event){
	var x = event.pageX - canvLeft,
        y = event.pageY - canvTop,
		cellNumber = 0;  
     cells.forEach(function(cell) {
     	cellNumber++
        if (y > cell.top && y < cell.top + cell.height 
            && x > cell.left && x < cell.left + cell.width && myDotsAvalable > 0 && cell.color == "None" && startOfthegame) {
        FillCell(cellNumber, myColor);
        changeDots("-");
        console.log('Cell Number is:' + cellNumber);
        var datatosend = '{ "type": "Cell", "number":'+ cellNumber + '}';
        conn.send(datatosend);
        console.log('Send'+datatosend);
        $.get('addCell',{cell: cellNumber, player: myPlayerNumber, room: myRoomNumber} ,function(data) {console.log("done")})
        }
    });


},false)

function FillCell(cell, color)
 {

        var x = 0,
            y = 0;
        if (cell%50 === 0) {
            y = 49;
            x = Math.floor(cell/50) - 1;
        } else {
 	        x = Math.floor(cell/50),
 	        y = cell%50 - 1;
        } 
 		ctx.fillStyle = color;
        ctx.clearRect(x*cellSize,y*cellSize,cellSize,cellSize);
 		ctx.fillRect(x*cellSize,y*cellSize,cellSize,cellSize);
 }
 function GenerateCells(){
 	for (var i = 0; i < 76; i++) {
 		for (var j = 0; j < 50; j++) {
 			cells.push({
                 color: "None",
   				 width: cellSize,
    			 height: cellSize,
   				 top: j*cellSize,
    			 left: i*cellSize
			});
 		}
 	}

 }

function FillGrid(){
	cells.forEach(function(element){
        if (element.color == "None") {
		  ctx.lineWidth="1";
		  ctx.strokeStyle="#FCC4C0";
          ctx.clearRect(element.left,element.top,element.width,element.height);
		  ctx.strokeRect(element.left,element.top,element.width,element.height);
        } else {
            ctx.fillStyle = element.color;
            ctx.clearRect(element.left,element.top,element.width,element.height);
            ctx.fillRect(element.left,element.top,element.width,element.height);
        }
	})
}

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}


GenerateCells();
FillGrid();

document.getElementById('first_player').style.color = playersColors[0];
document.getElementById('second_player').style.color = playersColors[1];
document.getElementById('third_player').style.color = playersColors[2];

conn.onopen = function(e) {
    console.log("Connection Established!");
    console.log ( myRoomNumber);
    myColor = playersColors[myPlayerNumber - 1];
    var  datatosend = '{ "type": "Room", "number":'+ myRoomNumber + '}';
    console.log(datatosend);
    conn.send(datatosend);
    if (hostStatus == 'Host') {
    initiateGame();
    }

};

conn.onmessage = function(e){
    console.log('Data received: ' + e.data);
    if (e.data == "Grid_Update") {
        updateGrid();
    } else if (e.data == "Host_Change" && !hostStatus) {
        console.log('that worked');
         $.post('ChangeHost',{room: myRoomNumber, player: myPlayerNumber}, function(data) {
            if (data.host == myPlayerNumber) NewHost();
         })
    } else {
        FillCell(e.data, newCellColor) 
    }
}

conn.onclose = function(e) {}; 

window.onunload = function(event) { 
      $.ajax({
        type: 'POST',
        async: false,  //Так делать не хорошо, но приходится
        url: 'leaveGame',
        data: {
           room: myRoomNumber,
           player: myPlayerNumber 
        }
    });
      //КОСТЫЛЬ так как onuload и onclose не успевают отправлять ajax запрос
     // $.post('leaveGame',{room: myRoomNumber, player: myPlayerNumber} ,function(data) {});
     // event.returnValue = "";
};

function initiateGame(){
    console.log("start");
    startOfthegame = true;
    $.get('startGame',{room: myRoomNumber} ,function(data) {console.log("done")})
    var timerId = setTimeout(function tick() {
        document.getElementById("myProgressBar").style="width: "+turn+"%;";
        turn = turn + 10;
        console.log("tick");
        if (turn == 100) {
            turn = 0; 
            loadGrid();
            var datatosend = '{ "type": "Msg", "number": "Grid_Update" }';
            conn.send(datatosend);
            console.log('Send'+datatosend);
            conn.send(datatosend);
            timeForNewDot +=1;
            if (timeForNewDot == 5) changeDots("+");
        } ;
        $.get('updateTurn',{turnUp: turn, room: myRoomNumber} ,function(data) {})
        timerId = setTimeout(tick, 800);
    }, 800)
};

function updateGrid(){
    loadGrid();
    clearInterval(timerIdNotaHost);
    timeForNewDot +=1;
    if (timeForNewDot == 5) changeDots("+");
    startOfthegame = true;
    startTimer();
    turn = 0;
} 

function startTimer(){
        timerIdNotaHost = setInterval(function(){
        document.getElementById("myProgressBar").style="width: "+turn+"%;";
        turn = turn + 10;
        if (turn == 100) {
            startOfthegame = false;
        }
        console.log(turn);
    },1000)
 
}
function loadGrid(){
    $.get('loadGrid',{room: myRoomNumber, player: myPlayerNumber, host: hostStatus }, function(data){

        console.log(data.state);
        var arr = data.state;
        Highscores[0] = data.fpscore;
        Highscores[1] = data.spscore;
        Highscores[2] = data.tpscore;

        document.getElementById('first_player').innerHTML = Highscores[0];
        document.getElementById('second_player').innerHTML = Highscores[1];
        document.getElementById('third_player').innerHTML = Highscores[2];

        for (var i = 0; i < 3800; i++) {
            switch(arr[i]) {
                case 0: 
                cells[i].color = "None";
                break;
                case 1:
                cells[i].color = playersColors[0];
                break;
                case 2:
                cells[i].color = playersColors[1];
                break;
                case 3:
                cells[i].color = playersColors[2];
                break;
                case 'z1':
                cells[i].color = Z1cellColor;
                break;
                case 'z-1':
                cells[i].color = Z_1cellColor;
                break;
                default:
                console.log("Error!");
                break;
                } 
            }
            FillGrid();
        }

    )
}

function changeDots(action){
    if (action == "-") {
        myDotsAvalable -= 1;
    } else {
        myDotsAvalable += 1;
    }
    timeForNewDot = 0;
    document.getElementById("Cells_id").innerHTML = myDotsAvalable;
}

function NewHost() {
    console.log("You are a new host!")
    startOfthegame = true;
    hostStatus = 'Host';
    clearInterval(timerIdNotaHost);
    var timerId = setTimeout(function tick() {
        document.getElementById("myProgressBar").style="width: "+turn+"%;";
        turn = turn + 10;
        console.log("tick");
        if (turn == 100) {
            turn = 0; 
            loadGrid();
            var datatosend = '{ "type": "Msg", "number": "Grid_Update" }';
            conn.send(datatosend);
            console.log('Send'+datatosend);
            conn.send(datatosend);
            timeForNewDot +=1;
            if (timeForNewDot == 5) changeDots("+");
        } ;
        $.get('updateTurn',{turnUp: turn, room: myRoomNumber} ,function(data) {})
        timerId = setTimeout(tick, 800);
    }, 800)

}

