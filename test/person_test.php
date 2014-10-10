<?php

header('Content-Type:text/plain');

include('../phpunit.phar');

include('../src/mongo.php');
include('../src/models.php');

include('person.php');
include('author.php');
include('book.php');

class PersonTest extends PHPUnit_Framework_TestCase {

  public function testCreateAndDelete() {
    $person = self::createPersonForTest();

    $person->save();

    $person_id = $person->getID();

    $person = Person::getByID($person_id);

    $this->assertEquals('Unit Test', $person->name);
    $this->assertEquals('Mrs.', $person->title);

    $person->delete();
  }
  public function testBooks() {
    $person = self::createPersonForTest();
    $person->save();
    $person_id = $person->getID();

    $author = new Author("Art Buchwald");
    $author->save();

    $book = new Book();
    $book->title = "I Think I Don't Remember";
    $book->author->set($author);
    $book->save();

    $person->books->add($book);
    $person->save();

    $person = Person::getByID($person_id);
    $books = $person->books->get();

    $this->assertEquals($books[0]->title, $book->title);

    foreach ($books as $book) {
      $this->assertEquals($book->owner->name, $person->name);
      $owner = $book->author->get()->owners[$person->getID()];
      $this->assertEquals($owner->name, $person->name);
    }

    $person->delete();
    $book->delete();
    $author->delete();
  }

  public function createPersonForTest() {
    $person = new Person();

    $person->name = "Unit Test";
    $person->title = "Mrs.";

    return $person;
  }
}

$test = new PersonTest();

$test->testCreateAndDelete();
$test->testBooks();

?>
