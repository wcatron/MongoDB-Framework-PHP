<?php

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
    $book->setAuthor($author);
    $book->save();

    $person->addBook($person);
    $person->save();
    var_dump($person);

    $person = Person::getByID($person_id);
    $books = $person->getBooks();
    $this->assertEquals($books[0], $book);

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
