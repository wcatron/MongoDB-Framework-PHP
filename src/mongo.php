<?

$mongo = new mongo_db();

class mongo_db {
	var $db;

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
			$this->db = $m->$dbname;
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

?>
