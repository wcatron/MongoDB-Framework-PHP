<?php

include('../phpunit.phar');

include('../src/mongo.php');
include('../src/models.php');

include('person.php');

$person = new Person();

$person->name = "Unit Test";
$person->title = "Mrs.";

$person->save();

echo "Saved Person";

$person_id = $person->getID();

$person = Person::getByID($person_id);

if ($person->name != "Unit Test") {
  die('Unit Test Failed: Name');
}
if ($person->title != "Mrs.") {
  die('Unit Test Failed: Title');
}

$person->delete();

?>
