<?php

class Example extends Document {
  public $key;
  private $array; // stores array of objects from 'documents' array of IDs stored in mongo;
  private $object;

  const COLLECTION = "examples";

  function toDocument() {
    $document = parent::toDocument();
    $document['key'] = $this->key;
    $this->denormalizeKeyToArray('documents',$document,'array');
    $this->denormalizeKeyToObject('object',$document);
    return $document;
  }
  function fromDocument($document) {
    parent::fromDocument($document);
    $document['key'] = $this->key;
  }

  function getObject() {
    return $this->normalizedObjectFromKey('OtherDocumentClass','object');
  }

  function setObject($object) {
    $this->object = $object;
  }

  function getArray() {
    return $this->normalizedArrayFromKey('OtherDocumentClass','documents','array');
  }

  function setArray($array) {
    $this->array = $array;
  }
}

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
