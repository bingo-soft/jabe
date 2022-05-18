<?php

namespace Tests\Util;

require_once("vendor/autoload.php");

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Timer;

/*$serverurl = "127.0.0.1";
$port = "9510";
$server = new Server($serverurl, $port, true);

$server->on("start", function (Server $server) {
    echo "Swoole http server is started\n";
});

$server->on("request", function (Request $request, Response $response) {
    // Every 1s, execute the run function
    Timer::tick(1000, function ($timerid, $param) {
        var_dump($timerid);
        var_dump($param);
    }, ["param1", "param2"]);
});

$server->start();*/

$word = "yes";
$temerId = Timer::after(1000, function () use ($word) {
    Timer::tick(1000, function () use ($word) {
        echo "$word ";
    });
});

Timer::after(5000, function () {
    Timer::clearAll();
    echo "Booo!";
});
