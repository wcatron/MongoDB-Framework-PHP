<?php

abstract class Document {
	public $old_document = null;
	public $collection;
	const COLLECTION = "undefined";

	public function save() {
		$mongo = mongo_db::getInstance();
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this);
			$this->fromDocument($new_document);
		} else {
			log_error(true,"Failed validation object in collection: ".$this->collection);
		}
	}

	public function secure_save() {
		$mongo = mongo_db::getInstance();
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this, true);
			$this->fromDocument($new_document);
		} else {
			log_error(true,"Failed validation object in collection: ".$this->collection);
		}
	}

	public function delete() {
		$mongo = mongo_db::getInstance();
		return $mongo->deleteObject($this);
	}

	public function getID() {
		if (isset($this->old_document)) {
			return (string)$this->old_document["_id"];
		}
		return null;
	}

	public function toDocument() {
		if ($this->getID() == null) {
			return array();
		}
		return array("_id"=>new MongoId($this->getID()));
	}

	public function fromDocument($document) {
		$this->old_document = $document;
	}

	public function customUpdate() {
		return false;
	}

	public function isEqual($object) {
		return ($this->getID() == $object->getID());
	}

	// Use these to go from referencing id to object.

	public function normalizedObjectFromKey($object, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		$mongo = mongo_db::getInstance();
		$id = $this->old_document[$key];
		$this->$property = $mongo->getObjectByID($object, $id);
	}

	public function denormalizeKeyToObject($object, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		if ($this->$property == null) {
			return;// If no one ever retrieved the document data then it didn't change.
		}
		$document[$key] = $this->$property->getID();
	}

	/*

	Working. Use in getter function for array.
	Will only actuall fetch once the first time.

	*/
	public function normalizedArrayFromKey($object, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		if ($this->$property == null) {
			$mongo = mongo_db::getInstance();
			$ids = $this->old_document[$key];
			$id_objs = array_map("Document::mongoIDFromString",$ids);
			$query = array('_id'=>array('$in'=>$id_objs));
			$this->$property = $mongo->getObjectsWithQuery($object,$query);
		}
		return $this->$property;
	}

	/*

	Use in toDocument function in subclasses. If property and key names are the same leave of last parameter.

	*/

	public function denormalizeKeyToArray($key, $document, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		if ($this->$property == null) {
			return;// If no one ever retrieved the document data then it didn't change.
		}
		$document[$key] = array();
		foreach ($this->$property as $object) {
			array_push($document[$key],$object->getID());
		}
	}

	public function validate() {
		// Override to restrict saving if doesn't pass test.
		return true;
	}

	public static function getObjectName() {
        return get_called_class();
    }

	public static function getByID($id) {
		$mongo = mongo_db::getInstance();
		return $mongo->getObjectByID(self::getObjectName(),$id);
	}
}

?>
