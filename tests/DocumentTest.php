<?php

/**
 * Run using the following command:
 * /vendor/bin/phpunit --bootstrap autoload.php tests/DocumentTest
 */

require 'sample_test_classes.php';

class DocumentTest extends PHPUnit_Framework_TestCase {

    public function testInsert() {
        $person = new \Person("Unit Test", "Mrs.");
        $this->assertTrue($person->save());
        $this->assertTrue($person->getID() !== null);
        $person->delete();
    }

    public function testUpdate() {
        $person = new \Person("Unit Test", "Ms.");
        $this->assertTrue($person->save(), "Person was successfully created.");
        $this->assertEquals("Unit Test", $person->name);

        $personID = $person->getID();
        $retrievedPerson = \Person::getByID($personID);
        $this->assertEquals($person->name, $retrievedPerson->name);
        $retrievedPerson = null;

        $person->title = "Mrs.";
        $this->assertTrue($person->save());

        $retrievedPerson = \Person::getByID($personID);
        $this->assertEquals($person->name, $retrievedPerson->name);
        $this->assertEquals($person->title, $retrievedPerson->title);

        $person->delete();
    }

    public function testLinkedObject() {
        $author = new \Author("Art Buchwald");
        $this->assertTrue($author->save());

        $book = new \Book();
        $book->title = "I Think I Don't Remember";
        $book->author->set($author);
        $this->assertTrue($book->save());

        $bookID = $book->getID();
        $retrievedBook = \Book::getByID($bookID);
        $this->assertTrue($retrievedBook->author->isEqual($author));

        /** @var Author $retrievedAuthor */
        $retrievedAuthor = $retrievedBook->author->get();
        $this->assertEquals($author->name, $retrievedAuthor->name);

        $author->delete();
        $book->delete();
    }

    public function testDocumentArray() {
        $person = new \Person("Unit Test", "Mrs.");

        $this->assertEquals("Mrs. Unit Test", $person->getFullName());

        $result = $person->save();
        $this->assertTrue(($result != false));
        $author = new \Author("Art Buchwald");
        $author->save();

        $book = new \Book();
        $book->title = "I Think I Don't Remember";
        $book->author->set($author);
        $book->save();

        $person->books->add($book);
        $person->save();

        $person_id = $person->getID();

        $person = \Person::getByID($person_id);
        $books = $person->books->get();

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
