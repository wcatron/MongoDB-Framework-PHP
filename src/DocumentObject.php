<?php

namespace wcatron\MongoDBFramework;

class DocumentObject {
	private $object_class;
	private $key;
	private $id;
	private $object;
	private $loaded = false;
	private $modifier;

	public function __construct($object_class,$key) {
		$this->object_class = $object_class;
		$this->key = $key;
	}

	public function get() {
		if (!$this->loaded) {
			$this->object = MDB::getInstance()->getObjectByID($this->object_class, $this->id);
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
		if (isset($document[$this->key])) {
			$this->id = $document[$this->key];
		}
	}

	public function setModifier(&$modifier) {
		$this->modifier = $modifier;
	}
}

?>
