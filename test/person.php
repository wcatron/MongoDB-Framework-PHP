<?php

class Person extends Document {
  public $name;
  public $title;

  const COLLECTION = "people";

  function toRow() {
    $row = parent::toRow();
    return $row;
  }
  function fromRow($row) {
    parent::fromRow($row);

  }
}

?>
