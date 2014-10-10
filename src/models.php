<?php

abstract class Document {
	public $old_document = null;
	private $document_arrays = array();
	private $document_objects = array();
	const COLLECTION = "undefined";

	public function save() {
		$mongo = mongo_db::getInstance();
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this);
			$this->fromDocument($new_document);
		} else {
			return false;
		}
	}

	public function secure_save() {
		$mongo = mongo_db::getInstance();
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this, true);
			$this->fromDocument($new_document);
		} else {
			return false;
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
		$id = $this->getID();
		$document = array();
		if ($id != null) {
			$document['_id'] = new MongoId($id);
		}
		foreach ($this->document_arrays as $array_property) {
			$this->$array_property->toDocument($document);
		}
		foreach ($this->document_objects as $object_property) {
			$this->$object_property->toDocument($document);
		}
		return $document;
	}

	public function fromDocument($document) {
		$this->old_document = $document;
		foreach ($this->document_arrays as $array_property) {
			$this->$array_property->fromDocument($document);
		}
		foreach ($this->document_objects as $object_property) {
			$this->$object_property->fromDocument($document);
		}
	}

	public function customUpdate() {
		return false;
	}

	public function isEqual($object) {
		return ($this->getID() == $object->getID());
	}


	/*

	Use these to go from referencing ids to objects. Put in object constructor.

	*/

	public function setArrayForKey($object, $key, $property = null, $documentArrayClass = "DocumentArray") {
		if ($property == null) {
			$property = $key;
		}
		$this->$property = new $documentArrayClass($object, $key);
		$this->document_arrays[] = $property;
	}

	public function setObjectForKey($object, $key, $property = null, $documentObjectClass = "DocumentObject") {
		if ($property == null) {
			$property = $key;
		}
		$this->$property = new $documentObjectClass($object, $key);
		$this->document_objects[] = $property;
	}

	public function validate() {
		return true;
	}

	public static function getObjectName() {
      return get_called_class();
  }

	function mongoIDFromString($string) {
		return new MongoId($string);
	}

	public static function getByID($id) {
		$mongo = mongo_db::getInstance();
		return $mongo->getObjectByID(self::getObjectName(),$id);
	}
}

class DocumentArray {
	private $object;
	private $key;
	private $ids = array();
	private $objects = array();
	private $loaded = false;
	private $modifier;

	public function DocumentArray($object,$key, $modifier = null) {
		$this->object = $object;
		$this->key = $key;
		$this->modifier = $modifier;
	}

	public function getKey() {
		return $this->key;
	}
	public function getIDs() {
		return $this->ids;
	}

	public function add($object) {
		$id = $object->getID();
		if ($this->loaded) {
			$this->objects[$id] = $object;
		}
		$this->ids[] = $id;
	}

	public function addID($id) {
		if ($this->loaded) {
			$this->objects[$id] = mongo_db::getInstance()->getObjectByID($this->object, $id);
		}
		$this->ids[] = $id;
	}

	public function remove($object) {
		$id = $object->getID();
		$this->removeID($id);
	}

	public function removeID($id) {
		if ($this->loaded) {
			unset($this->objects[$id]);
		}
		$index = array_search($id,$this->ids);
		if($index !== FALSE){
			unset($this->ids[$index]);
		}
	}

	public function get() {
		if (!$this->loaded) {
			$id_objs = array_map("Document::mongoIDFromString",$this->ids);
			$query = array('_id'=>array('$in'=>$id_objs));
			$objects_results = mongo_db::getInstance()->getObjectsWithQuery($this->object,$query);
			foreach ($objects_results as $object) {
				if (isset($this->modifier)) {
					$modify = $this->modifier;
					$modify($object);
				}
				$this->objects[$object->getID()] = $object;
			}
			$this->loaded = true;
		}
		return array_values($this->objects);
	}

	public function toDocument(&$document) {
		$document[$this->key] = $this->ids;
	}

	public function fromDocument($document) {
		$this->ids = $document[$this->key];
	}

	public function getObjects() {

	}

	public function setModifier(&$modifier) {
		$this->modifier = $modifier;
	}
}

class DocumentObject {
	private $object_class;
	private $key;
	private $id;
	private $object;
	private $loaded = false;
	private $modifier;

	public function DocumentObject($object_class,$key) {
		$this->object_class = $object_class;
		$this->key = $key;

	}

	public function get() {
		if (!$this->loaded) {
			$this->object = mongo_db::getInstance()->getObjectByID($this->object_class, $this->id);
			if (isset($this->modifier)) {
				$modify = $this->modifier;
				$modify($this->object);
			}
			$this->loaded = true;
		}
		return $this->object;
	}

	public function getID() {
		return $this->id;
	}

	public function set($object) {
		$this->object = $object;
		$this->id = $object->getID();
	}

	public function setID($id) {
		$this->id = $id;
		if ($this->loaded) {
			$this->loaded = false;
		}
	}

	public function toDocument(&$document) {
		$document[$this->key] = $this->id;
	}

	public function fromDocument($document) {
		$this->id = $document[$this->key];
	}

	public function setModifier(&$modifier) {
		$this->modifier = $modifier;
	}
}
?>
