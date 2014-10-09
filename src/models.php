<?

abstract class Document {
	public $old_document = null;
	public $collection;
	const COLLECTION = "undefined";

	public function save() {
		global $mongo;
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this);
			$this->fromDocument($new_document);
		} else {
			log_error(true,"Failed validation object in collection: ".$this->collection);
		}
	}

	public function secure_save() {
		global $mongo;
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this, true);
			$this->fromDocument($new_document);
		} else {
			log_error(true,"Failed validation object in collection: ".$this->collection);
		}
	}

	public function delete() {
		global $mongo;
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

	public function normalizePropertyObject($property, $object) {
		global $mongo;
		$id = $this->old_document[$property];
		$this->$property = $mongo->getObjectByID($object, $id);
	}

	public function normalizeObjectFromKeyToProperty($object, $key, $property) {
		global $mongo;
		$id = $this->old_document[$key];
		$this->$property = $mongo->getObjectByID($object, $id);
	}

	public function normalizePropertyArray($property, $object) {
		global $mongo;
		$ids = $this->old_document[$property];
		$this->$property = array();
		$id_objs = array_map("Document::mongoIDFromString",$ids);
		$query = array('_id'=>array('$in'=>$id_objs));
		$this->$property = $mongo->getObjectsWithQuery($object,$query);
	}

	public function validate() {
		// Override to restrict saving if doesn't pass test.
		return true;
	}

	public static function getObjectName() {
        return get_called_class();
    }

	public static function getByID($id) {
		global $mongo;
		return $mongo->getObjectByID(self::getObjectName(),$id);
	}
}

?>
