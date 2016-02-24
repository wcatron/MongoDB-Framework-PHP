<?php

namespace wcatron\MongoDBTesting;

use wcatron\MongoDBFramework\Document;
use wcatron\MongoDBFramework\DocumentArray;

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
