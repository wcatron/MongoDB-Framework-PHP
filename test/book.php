<?php

class Book extends Document {
  public $title;
  public $author;
  protected $owner; //

  const COLLECTION = "books";

  function Book() {
    $this->setObjectForKey('Author','author');
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
