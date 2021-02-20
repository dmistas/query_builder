<?php
session_start();

require_once "classes\Config.php";
require_once "classes\Database.php";


$GLOBALS['config'] = [
    'mysql' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'my_database',
    ],
    
];

