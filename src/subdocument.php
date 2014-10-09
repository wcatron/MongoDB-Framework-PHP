<?php

class SubDocument extends Document {
  public $key;
  public $key2;

  const COLLECTION = "subdocuments";

  function toDocument() {
    $document = parent::toDocument();

    return $document;
  }
  function fromDocument($document) {
    parent::fromDocument($document);

  }
}

?>
