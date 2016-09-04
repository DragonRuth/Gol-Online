<?php
namespace App\Classes\Socket;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Classes\Socket\Base\BaseSocket;
use Illuminate\Support\Facades\DB;

class GameSocket extends BaseSocket {
 protected $clients;
 protected $clients_rooms;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // $numRecv = count($this->clients) - 1;
        // echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
        //     , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        echo "({$msg})\n";

        $data = json_decode($msg,true);


        if ($data["type"] == 'Room') 
        {
            echo "({$data["type"]})\n";
            $this->clients_rooms[$from->resourceId] = $data["number"];
            echo "RoomNumberRecived ({$data["number"]})\n";
        }
        elseif ($data["type"] == 'Cell' || $data["type"] == 'Msg' ) 
        {
            $sender_room = $this->clients_rooms[$from->resourceId];
           foreach ($this->clients as $client) 
           {
            if ($from !== $client && $this->clients_rooms[$client->resourceId]==$sender_room) 
            {
                // The sender is not the receiver, send to each client connected
                $client->send($data["number"]);
            }
           } 

        }

        
    }

    public function onClose(ConnectionInterface $conn) {

        echo "Connection {$conn->resourceId} has disconnected\n";
         $sender_room = $this->clients_rooms[$conn->resourceId];
           foreach ($this->clients as $client) 
           {
            if ($conn !== $client && $this->clients_rooms[$client->resourceId]==$sender_room) 
            {
                // The sender is not the receiver, send to each client connected
                $client->send("Host_Change");
            }
           } 
        
        $this->clients->detach($conn);
        
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}