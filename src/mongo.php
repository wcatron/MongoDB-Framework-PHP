<?php

class mongo_db {
	var $db;

	public static function getInstance() {
      static $mongo = null;
      if (null === $mongo) {
          $mongo = new static();
      }
      return $mongo;
  }

	function mongo_db($config = null) {
		$this->connect($config);
	}
	function connect($config = null) {
		if (!isset($this->db)) {
			if ($config == null) {
				$configs = parse_ini_file("../config.ini");
				$config = $configs['mdb_config'];
			}
			$m = new MongoClient("mongodb://".$config['user'].":".$config['pass']."@".$config['host']."/".$config['db']);
			$this->db = $m->$config['db'];
		}
	}
	function saveDocument ($object) {
		$instancePropertyName = $object::COLLECTION;
		$new_document = $object->toDocument();
		if ($object->old_document) {
			$updatequery = $object->customUpdate();
			if ($updatequery == false) {
				// Calculate what's changed.
				$changed_keys = self::keys_changed($object->old_document, $new_document);

				$fieldsAndValues = array();

				foreach ($changed_keys as $key) {
					$fieldsAndValues[$key] = $new_document[$key];
				}

				if (count($fieldsAndValues) == 0) {
					return $object->old_document;
				}

				$updatequery = array('$set' => $fieldsAndValues);
			}

			$resulting_document = $this->db->{$instancePropertyName}->findAndModify(array("_id" => new MongoId($object->getID())), $updatequery);
			return $resulting_document;
		} else {
			// New Document
			$document_results = $this->db->{$instancePropertyName}->insert($new_document);
			return $new_document;
		}
	}

	function getObjectByKey($objectType, $field, $value) {
		$object = new $objectType();
		$collection = $object::COLLECTION;
		$document = $this->db->{$collection}->findOne(array($field=>$value));
		if ($document == NULL) {
			return false;
		}
		$object->fromDocument($document);
		return $object;
	}
	function getObjectsByKey($objectType, $field, $value) {
		return $this->getObjectsWithQuery($objectType, array($field=>$value));
	}

	function getObjectByID($objectType, $id) {
		return $this->getObjectByKey($objectType,'_id',new MongoId($id));
	}

	function getCountForCollection($collection) {
		return $this->db->{$collection}->count();
	}

	function getObjectWithQuery($objectType, $query) {
		$object = new $objectType();
		$collection = $object::COLLECTION;
		$document = $this->db->{$collection}->findOne($query);

		if ($document == NULL) {
			return false;
		}
		$object->fromDocument($document);
		return $object;
	}

	function getObjectsWithQuery($objectType, $query) {
		$object = new $objectType();
		$collection = $object::COLLECTION;
		$cursor = $this->db->{$collection}->find($query);

		$objects = array();
		foreach ($cursor as $document) {
			$object = new $objectType();
			$object->fromDocument($document);
			$objects[] = $object;
		}
		return $objects;
	}

	function getAllObjects($objectType) {
		return $this->getObjectsWithQuery($objectType, array());
	}

	function deleteObject($object) {
		return $this->db->{$object::COLLECTION}->remove(array("_id"=>new MongoID($object->getID())));
	}

	function keys_changed($old, $new) {
		$all_keys = array_keys($new);
		$return_keys = array();

		foreach($all_keys as $key) {
			if (!isset($old[$key]) || $new[$key] != $old[$key]) {
				array_push($return_keys, $key);
			}
		}

		return $return_keys;
	}
}


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
