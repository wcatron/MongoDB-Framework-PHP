<?php

class Person extends Document {
  public $name;
  public $title;

  protected $books;

  const COLLECTION = "people";

  function toDocument() {
    $document = parent::toDocument();
    $document['name'] = $this->name;
    $document['title'] = $this->title;

    $this->denormalizeKeyToArray('books',$document);

    return $document;
  }
  function fromDocument($document) {
    parent::fromDocument($document);
    $this->name = $document['name'];
    $this->title = $document['title'];
  }

  function getBooks() {
    return $this->normalizedArrayFromKey('Book','books');
  }

  function addBook($book) {
    array_push($this->getBooks(), $book);
  }

}

?>
