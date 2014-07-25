<?
	
$mongo = new mongo_db();

class mongo_db {
	var $db;

	function mongo_db() {
		$this->connect();
	}
	function connect() {
		if (!isset($this->db)) {
			$m = new MongoClient($config);
			$this->db = $m->$dbname;
		}
	}
	function saveDocument ($object) {
		$instancePropertyName = $object->collection;
		$new_document = $object->toDocument();
		
		if ($object->old_document) {
			
			$updatequery = $object->customUpdate();
			if ($updatequery == false) {
				// Calculate what's changed.
				$changed_keys = keys_changed($object->old_document, $new_document);				
				$fieldsAndValues = array();
				
				foreach ($changed_keys as $key) {
					$fieldsAndValues[$key] = $new_document[$key];
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
	function getObjectByField($objectType, $field, $value) {
		$object = new $objectType();
		$collection = $object->collection;
		$document = $this->db->{$collection}->findOne(array($field=>$value));
		if ($document == NULL) {
			return false;
		}
		$object->fromDocument($document);
		return $object;
	}
	function getObjectsByField($objectType, $field, $value) {
		return $this->getObjectsWithQuery($objectType, array($field=>$value));
	}
	
	function getObjectFromID($objectType, $id) {
		return $this->getObjectByField($objectType,'_id',new MongoId($id
	}
	
	function getCountForCollection($collection) {
		return $this->db->{$collection}->count();
	}
	
	function getObjectsWithQuery($objectType, $query) {
		$object = new $objectType();
		$collection = $object->collection;
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
}

function keys_changed($old, $new) {
	$all_keys = array_keys($new);
	$return_keys = array();
	
	foreach($all_keys as $key) {
		if ($new[$key] != $old[$key]) {
			array_push($return_keys, $key);
		}
	}
	
	return $return_keys;
}

?>