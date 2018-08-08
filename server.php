<?php

class SmtpServer{

    private $addr;
    private $port;
    private $socket;

    public function __construct($address = '127.0.0.1', $port = 25)
    {
        $this->addr = $address;
        $this->port = $port;
    }

    public function start()
    {
        if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            echo "socket_create() error: " . socket_strerror(socket_last_error()) . PHP_EOL;
            return false;
        }

        if (socket_bind($sock, $this->addr, $this->port) === false) {
            echo "socket_bind() error: " . socket_strerror(socket_last_error($sock)) . PHP_EOL;
            return false;
        }

        if (socket_listen($sock, 3) === false) {
            echo "socket_listen() error: " . socket_strerror(socket_last_error($sock)) . PHP_EOL;
            return false;
        }

        $this->socket = $sock;

        return true;
    }

    public function startListen()
    {
        while (true){

            if (($msgsock = socket_accept($this->socket)) === false) {
                echo "socket_accept(): error: " . socket_strerror(socket_last_error($this->socket)) . PHP_EOL;
                break;
            }

            $this->startDialog($msgsock);

            echo "Client disconnected at: ".date('Y-m-d h:i:s') . PHP_EOL;
            socket_close($msgsock);
        }
    }

    private function startDialog($socket)
    {
        socket_getpeername($socket, $raddr, $rport);
        echo "Received Connection from $raddr:$rport\n";

        $this->sendMsg("220 Hello $raddr!", $socket);

        while(true){
            if (false === ($buff = socket_read($socket, 1024, PHP_NORMAL_READ))) {
                echo "socket_read(): error: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
                break;
            }

            if (strpos($buff, 'HELO') !== false){
                $this->sendMsg('250', $socket);
            }

            if (strpos($buff, 'MAIL FROM') !== false){
                $this->sendMsg('250', $socket);
            }

            if (strpos($buff, 'RCPT TO') !== false){
                $this->sendMsg('250', $socket);
            }

            if (strpos($buff, 'DATA') !== false){
                $this->sendMsg('354', $socket);
            }

            if (trim($buff) == '.'){
                $this->sendMsg('250', $socket);
            }

            echo $buff;

            if (trim($buff) == 'QUIT'){
                $this->sendMsg('221', $socket);
                break;
            }
        }
    }

    private function sendMsg($msg, $socket)
    {
        $msg .= "\r\n";
        socket_write($socket, $msg, strlen($msg));
    }

}