<?php

namespace wcatron\MongoDBFramework;

use wcatron\MongoDBFramework\MDB;
use wcatron\MongoDBFramework\Document;

class DocumentArray {
	/** @var Document */
	private $object;
	private $key;
	private $ids = array();
	private $objects = array();
	private $loaded = false;
	private $modifier;

	public function __construct($object,$key, $modifier = null) {
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
			$this->objects[$id] = MDB::getInstance()->getObjectByID($this->object, $id);
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

	/**
	 * @return {$this->object}[]
	 */
	public function get() {
		if (!$this->loaded) {
			$id_objs = array_map("wcatron\\MongoDBFramework\\Document::mongoIDFromString",$this->getIDs());
			$query = array('_id'=>array('$in'=>$id_objs));
			$objects = MDB::getInstance()->getObjectsWithQuery($this->object, $query);
			foreach ($objects as $object) {
				if (isset($this->modifier)) {
					$this->callModifier($object);
				}
				$this->objects[$object->getID()] = $object;
			}
			$this->loaded = true;
		}
		return array_values($this->objects);
	}

	public function callModifier(&$object) {
		if(is_callable($this->modifier)) {
			return call_user_func_array($this->modifier, [&$object]);
		}
	}

	public function toDocument(&$document) {
		$document[$this->key] = $this->ids;
	}

	public function fromDocument($document) {
		$this->ids = (array)$document[$this->key];
	}

	public function setModifier(&$modifier) {
		$this->modifier = $modifier;
	}
}

?>
