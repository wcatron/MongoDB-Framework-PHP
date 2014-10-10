<?php

class Book extends Document {
  public $title;
  protected $author;
  protected $owner; //

  const COLLECTION = "books";

  function toDocument() {
    $document = parent::toDocument();
    $document['title'] = $this->title;
    $this->denormalizeKeyToObject($document, 'author');
    return $document;
  }
  function fromDocument($document) {
    parent::fromDocument($document);
    $document['title'] = $this->title;
  }

  function &getAuthor() {
    return $this->normalizedObjectFromKey('Author','author');
  }

  function setAuthor($author) {
    $this->author = $author;
  }

}

?>
