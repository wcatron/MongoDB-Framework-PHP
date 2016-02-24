<?php

namespace wcatron\MongoDBTesting;

use wcatron\MongoDBFramework\DocumentArray;
use wcatron\MongoDBFramework\MDB;
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

?>
