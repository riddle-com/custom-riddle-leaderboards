<?php

require dirname(__FILE__) . '/src/init.php';

$handler = new Riddle\Core\RiddleLeaderboardHandler();
$handler->start();