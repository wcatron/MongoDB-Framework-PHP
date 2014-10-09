<?php

class Book extends Document {
  public $title;
  private $author;
  private $owner; //

  const COLLECTION = "examples";

  function toDocument() {
    $document = parent::toDocument();
    $document['title'] = $this->title;
    $this->denormalizeKeyToObject('author', $document);
    return $document;
  }
  function fromDocument($document) {
    parent::fromDocument($document);
    $document['title'] = $this->title;
  }

  function getAuthor() {
    return $this->normalizedObjectFromKey('Author','author');
  }

  function setAuthor($author) {
    $this->author = $author;
  }
  
}

?>
