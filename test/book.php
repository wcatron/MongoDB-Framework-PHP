<?php

class Book extends Document {
  public $title;
  public $author;
  public $owner;

  const COLLECTION = "books";

  function Book() {
    $this->setObjectForKey('Author','author');
    $modify = function (&$author)
    {
      if ($this->owner) {
        $author->owners[$this->owner->getID()] = $this->owner;
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
