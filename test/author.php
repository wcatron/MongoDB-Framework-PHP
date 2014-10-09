<?php

class Author extends Document {
  public $name;

  protected $books;

  const COLLECTION = "authors";

  function Author($name) {
    $this->name = $name;
  }

  function toDocument() {
    $document = parent::toDocument();
    $document['name'] = $this->name;
    return $document;
  }

  function fromDocument($document) {
    parent::fromDocument($document);
    $document['name'] = $this->name;
  }

  function getBooks() {
    if ($this->books == null) {
      $this->books = mongo_db::getInstance()->getObjectsByKey('Book','author',$this->getID());
    }
    return $this->books;
  }
}

?>
