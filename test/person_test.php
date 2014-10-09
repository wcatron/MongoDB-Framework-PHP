<?php

include('../src/mongo.php');
include('../src/models.php');

include('person.php');

$mongo = new Mongo();

$person = new Person();

$person->name = "Unit Test";
$person->title = "Mrs.";

$person->save($mongo);

$person->delete($mongo);

?>
