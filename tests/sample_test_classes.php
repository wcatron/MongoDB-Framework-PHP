<?php

/**
 * These classes are not autoloaded on purpose because composer packages can't exclude test files.
 */

use wcatron\MongoDBFramework\DocumentArray;
use wcatron\MongoDBFramework\Document;

class Author extends Document {
    public $name;
    /** @var DocumentArray */
    public $owners;

    const COLLECTION = "authors";

    function __construct($name = null) {
        $this->name = $name;
        $this->setArrayForKey(Person::class, 'owners');
    }

    function toDocument() {
        $document = parent::toDocument();
        $document['name'] = $this->name;
        return $document;
    }

    function fromDocument($document) {
        parent::fromDocument($document);
        $this->name = $document['name'];
    }

}

use wcatron\CommonDBFramework\LinkedObject;

class Book extends Document {
    var $title;
    /** @var LinkedObject */
    var $author;
    var $owner;

    const COLLECTION = "books";

    function __construct() {
        $this->setObjectForKey(Author::class, 'author');
        $modify = function (&$author) {
            if ($this->owner) {
                $author->owners->add($this->owner);
            }
        };
        $this->author->setModifier($modify);
    }

    function toDocument() {
        $document = parent::toDocument();
        $document['title'] = $this->title;
        return $document;
    }

    function fromDocument($document) {
        parent::fromDocument($document);
        $this->title = $document['title'];
    }
}

class Person extends Document {
    var $name;
    var $title;

    /** @var DocumentArray */
    var $books;

    const COLLECTION = "people";

    function __construct($name = null, $title = null) {
        $this->setArrayForKey(Book::class, 'books');
        $modify = function (&$book) {
            $book->owner = $this;
        };
        $this->books->setModifier($modify);
        $this->name = $name;
        $this->title = $title;
    }

    function getFullName() {
        return (($this->title) ? $this->title . " " : "") . $this->name;
    }

    function toDocument() {
        $document = parent::toDocument();
        $document['name'] = $this->name;
        $document['title'] = $this->title;
        return $document;
    }

    function fromDocument($document) {
        parent::fromDocument($document);
        $this->name = $document['name'];
        $this->title = $document['title'];
    }
}

?>
