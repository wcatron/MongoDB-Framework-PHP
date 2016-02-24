<?php

use wcatron\MongoDBTesting\Book;
use wcatron\MongoDBTesting\Person;
use wcatron\MongoDBTesting\Author;
use wcatron\MongoDBFramework\MDB;

class DBTest extends PHPUnit_Framework_TestCase {

  public function testSaveAndDelete()
  {
    $config = parse_ini_file(__DIR__.'/../config.ini');
    MDB::configure($config['mdb_config']);
    $person = new Person();

    $person->name = "Unit Test";
    $person->title = "Mrs.";

    $result = $person->save();
    $this->assertTrue(($result != false));

    $author = new Author("Art Buchwald");
    $author->save();

    $book = new Book();
    $book->title = "I Think I Don't Remember";
    $book->author->set($author);
    $book->save();

    $person->books->add($book);
    $person->save();

    $person_id = $person->getID();

    $person = Person::getByID($person_id);
    $books = $person->books->get();

    var_dump($books);

    $this->assertEquals($books[0]->title, $book->title);

    foreach ($books as $book) {
      $this->assertEquals($book->owner->name, $person->name);
      $owners = $book->author->get()->owners->get();
      $this->assertEquals($owners[0]->name, $person->name);
    }

    $person->delete();
    $book->delete();
    $author->delete();
  }

}
?>