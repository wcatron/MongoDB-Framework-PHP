<?php

abstract class Document {
	public $old_document = null;
	public $collection;
	private $document_arrays = array();
	private $document_objects = array();
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

	Use these to go from referencing id to object.
	Use denormailize in the toDocument function

	*/

	public function &normalizedObjectFromKey($object, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		if ($this->$property == null) {
			$mongo = mongo_db::getInstance();
			$id = $this->old_document[$key];
			$this->$property = $mongo->getObjectByID($object, $id);
		}
		return $this->$property;
	}

	public function denormalizeKeyToObject(&$document, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		if ($this->$property == null) {
			echo "No one ever re";
			return;// If no one ever retrieved the document data then it didn't change.
		}
		$document[$key] = $this->$property->getID();
	}

	/*

	Use these to go from referencing array of ids to objects.
	Use denormailize in the toDocument function

	*/

	public function setArrayForKey($object, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		$this->$property = new DocumentArray($object, $key);
		$this->document_arrays[] = $property;
	}

	public function setObjectForKey($object, $key, $property = null) {
		if ($property == null) {
			$property = $key;
		}
		$this->$property = new DocumentObject($object, $key);
		$this->document_objects[] = $property;
	}

	public function validate() {
		// Override to restrict saving if doesn't pass test.
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

	public function DocumentArray($object,$key) {
		$this->object = $object;
		$this->key = $key;
	}

	public function getKey() {
		return $this->key;
	}
	public function getIDs() {
		return $this->ids;
	}

	public function add($object) {
		$id = $object->getID();
		if ($loaded) {
			$this->objects[$id] = $object;
		}
		$this->ids[] = $id;
	}
	public function remove($object) {
		$id = $object->getID();
		if ($loaded) {
			unset($this->objects[$id]);
		}
		$index = array_search($id,$this->ids);
		if($index !== FALSE){
			unset($this->ids[$index]);
		}
	}

	public function addID($id) {
		if ($loaded) {
			$this->objects[$id] = mongo_db::getInstance()->getObjectByID($this->object, $id);
		}
		$this->ids[] = $id;
	}

	public function toArray() {
		if (!$loaded) {
			$id_objs = array_map("Document::mongoIDFromString",$this->ids);
			$query = array('_id'=>array('$in'=>$id_objs));
			$objects = mongo_db::getInstance()->getObjectsWithQuery($this->object,$query);
			foreach ($objects as $object) {
				$this->objects[$object->getID()] = $object;
			}
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
}
class DocumentObject {
	private $object_class;
	private $key;
	private $id;
	private $object;
	private $loaded = false;

	public function DocumentObject($object_class,$key) {
		$this->object_class = $object_class;
		$this->key = $key;
	}

	public function get() {
		if (!$loaded) {
			$this->object = mongo_db::getInstance()->getObjectByID($this->object_class, $this->id);
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
}
?>
