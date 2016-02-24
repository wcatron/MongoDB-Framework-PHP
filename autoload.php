<?php

use wcatron\MongoDBFramework\MDB;

require_once __DIR__ . '/vendor/autoload.php';

var_dump($_ENV);

if (file_exists('config.ini')) {
    $config = parse_ini_file('config.ini');
    MDB::configure($config['mdb_config']);
} else {
    $config = [
        "host" => "127.0.0.1",
        "db" => "mongodb_framework_php_testing"
    ];
    MDB::configure($config);
}



?>
