<?php

class Person extends Document {
  public $name;
  public $title;

  public $books;

  const COLLECTION = "people";

  function Person () {
    $this->setArrayForKey('Book','books');
    $modify = function (&$book)
    {
      $book->owner = $this;
    };
    $this->books->setModifier($modify);
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
