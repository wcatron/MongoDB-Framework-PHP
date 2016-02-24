<?php

namespace wcatron\MongoDBTesting;

use wcatron\CommonDBFramework\LinkedObject;
use wcatron\MongoDBFramework\Document;

class Book extends Document {
  var $title;
  /** @var LinkedObject */
  var $author;
  var $owner;

  const COLLECTION = "books";

  function __construct() {
    $this->setObjectForKey(Author::class,'author');
    $modify = function (&$author)
    {
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

?>
