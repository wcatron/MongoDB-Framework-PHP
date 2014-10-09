<?php

include('../phpunit.phar');

include('../src/mongo.php');
include('../src/models.php');

include('person.php');

class PersonTest extends PHPUnit_Framework_TestCase {
  public function testCreateAndDelete() {
    $person = new Person();

    $person->name = "Unit Test";
    $person->title = "Mrs.";

    $person->save();

    $person_id = $person->getID();

    $person = Person::getByID($person_id);

    $this->assertEquals('Unit Test', $person->name);
    $this->assertEquals('Mrs.', $person->title);

    $person->delete();
  }
  public function testBooks() {
    $person = new Person();
    $person->boo
  }
}

$test = new PersonTest();

$test->testCreateAndDelete();

?>
