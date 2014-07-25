<?
	
abstract class Document {	
	public $old_document = null;
	public $collection;
	
	public function save() {
		global $mongo;
		if ($this->validate()) {
			$new_document = $mongo->saveDocument($this);
			$this->fromDocument($new_document);
		}
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
	
	public function validate() {
		return true;
	}
	
	public function isEqual($object) {
		return ($this->getID() == $object->getID());
	}
	
	public function normalizePropertyObject($property, $object) {
		global $mongo;
		$id = $this->old_document[$property];
		$this->$property = $mongo->getObjectFromID($object, $id);
	}
	
	public function normalizePropertyArray($property, $object) {
		global $mongo;
		$ids = $this->old_document[$property];
		$this->$property = array();		
		$id_objs = array_map("Document::mongoIDFromString",$ids);
		$query = array('_id'=>array('$in'=>$id_objs));
		$this->$property = $mongo->getObjectsWithQuery($object,$query);
	}
	
	function mongoIDFromString($string) {
		return new MongoId($string);
	}
}

?>