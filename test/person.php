<?php

class Person extends Document {
  public $name;
  public $title;

  const COLLECTION = "people";

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
