<?php

use wcatron\MongoDBFramework\MDB;

require_once __DIR__ . '/vendor/autoload.php';

if ($_ENV['travis']) {
    $config = $_ENV;
    MDB::configure($config);
} else {
    $config = parse_ini_file('config.ini');
    MDB::configure($config['mdb_config']);
}



?>
