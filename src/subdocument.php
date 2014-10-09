<?php

class SubDocument extends Document {
  public $key;
  public $key2;

  const COLLECTION = "subdocuments";

  function toRow() {
    $row = parent::toRow();
    return $row;
  }
  function fromRow($row) {
    parent::fromRow($row);
  }
}

?>
