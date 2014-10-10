<?php

class Author extends Document {
  public $name;
  public $owners = array();

  const COLLECTION = "authors";

  function Author($name = null) {
    $this->name = $name;
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

  protected $books;
  function getBooks() {
    if ($this->books == null) {
      $this->books = mongo_db::getInstance()->getObjectsByKey('Book','author',$this->getID());
    }
    return $this->books;
  }
}

?>
