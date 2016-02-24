<?php

/*

class Example extends Document {
  public $key;
  public $array; // stores array of objects from 'documents' array of IDs stored in mongo;
  public $object;

  const COLLECTION = "examples";

  function Example() {
    $this->setObjectForKey('ObjectA','object');
    $this->setObjectForKey('ObjectB','array');
  }

  function toDocument() {
    $document = parent::toDocument();
    $document['key'] = $this->key;
    return $document;
  }
  function fromDocument($document) {
    parent::fromDocument($document);
    $document['key'] = $this->key;
  }
}*/

// Example array document

/*

{
  "_id" : { "$oid" : "IDHERE2182ASDF129"},
  "key" : "value",
  "documents" : [ "ANOTHERDOCUMENTSID1","ANOTHERDOCUMENTSID2","ANOTHERDOCUMENTSID3" ],
  "object" : "ANOTHERDOCUMENTSID4"
}


*/

?>
