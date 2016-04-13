<?php

namespace wcatron\MongoDBFramework;

use wcatron\CommonDBFramework\DBObject;
use wcatron\CommonDBFramework\LinkedObject;
use wcatron\MongoDBFramework\MDB;
use \MongoDB\BSON\ObjectID as ObjectID;

abstract class Document extends DBObject {
	public $old_document = null;
	private $document_arrays = array();
	/**
	 * Array of linked objects.
	 * @var LinkedObject[]
	 */
	private $linked_objects = array();
	const COLLECTION = "undefined";
	/** @var MDB */
	static $dbClass = MDB::class;

	public function save() {
		$new_document = static::getDBInstance()->saveDocument($this);
		if ($new_document) {
			$this->fromDocument($new_document);
			return true;
		}
		return false;
	}

	public function delete() {
		return static::getDBInstance()->deleteObject($this);
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
			$document['_id'] = new ObjectID($id);
		}
		foreach ($this->document_arrays as $array_property) {
			$this->$array_property->toDocument($document);
		}
		foreach ($this->linked_objects as $object_property) {
			$this->$object_property->toArray($document);
		}
		return $document;
	}

	public function fromDocument($document) {
		$this->old_document = $document;
		foreach ($this->document_arrays as $array_property) {
			$this->$array_property->fromDocument($document);
		}
		foreach ($this->linked_objects as $object_property) {
			$this->$object_property->fromArray($document);
		}
	}

	public function updateQuery() {
		if ($this->needsInsert()) {
			return false;
		}

		$new_document = $this->toDocument();

		$fieldsAndValues = static::changedFieldsAndValues($this->old_document, $new_document);

		if (count($fieldsAndValues) == 0) {
			return false;
		}

		return [ '$set' => $fieldsAndValues ];
	}

	public static function changedFieldsAndValues($oldDocument, $newDocument) {
		$fieldsAndValues = [];

		foreach (array_keys($newDocument) as $key) {
			if (!isset($oldDocument[ $key])) {
				$fieldsAndValues[$key] = $newDocument[$key];
			} else if ($newDocument[ $key ] != $oldDocument[ $key ]) {
				$fieldsAndValues[$key] = $newDocument[$key];
			} else if (is_object($newDocument[$key])) {
				if (get_class($newDocument[$key]) == \MongoDB\BSON\UTCDateTime::class) {
					if ($newDocument[ $key ]->__toString() != $oldDocument[ $key ]->__toString()) {
						$fieldsAndValues[$key] = $newDocument[$key];
					}
				}
			}
		}

		return $fieldsAndValues;

	}

	public function isEqual($object) {
		return ($this->getID() == $object->getID());
	}


	/*

	Use these to go from referencing ids to objects. Put in object constructor.

	*/

	public function setArrayForKey($object, $key, $property = null, $documentArrayClass = DocumentArray::class) {
		if ($property == null) {
			$property = $key;
		}
		$this->$property = new $documentArrayClass($object, $key);
		$this->document_arrays[] = $property;
	}

	/** Links child object from a document key, to a class property.
	 * @param string Fully qualified class string for object.
	 * @param string Column name containing the id for the object.
	 * @param string Property name in object that will be used to access the object.
	 * @param string Class name for link. Default option is LinkedObject which works for any DBObject.
	 */
	public function setObjectForKey($object, $key, $property = null, $documentObjectClass = LinkedObject::class) {
		if ($property == null) {
			$property = $key;
		}
		$this->$property = new $documentObjectClass($object, $key);
		$this->linked_objects[] = $property;
	}

	public function validate() {
		return true;
	}

	public static function getObjectName() {
		return get_called_class();
	}

	static function mongoIDFromString($string) {
		return new ObjectID($string);
	}

	/**
	 * @param $id
	 * @return static
	 */
	public static function getByID($id) {
		return static::getDBInstance()->getObjectByID(static::class,$id);
	}

	/**
	 * @return static[]
	 */
	public static function getAll() {
		return static::getDBInstance()->getAllObjects(static::class);
	}

	public static function getManyWithQuery($query) {
		return static::getDBInstance()->getObjectsWithQuery(static::class, $query);
	}

	/**
	 * Gets objects by a particular column value.
	 * @param  string $column Name of column.
	 * @param  string $value  Value to query for.
	 * @return static[]
	 */
	public static function getManyByKey($key, $value) {
		return static::getDBInstance()->getObjectsByKey(static::class, $key, $value);
	}

	/**
	 * Gets one object by a particular column value.
	 * @param  string $column Name of column.
	 * @param  string $value  Value to query for.
	 * @return static
	 */
	public static function getOneByKey($key, $value) {
		return static::getDBInstance()->getObjectByKey(static::class, $key, $value);
	}

	public function getCreatedTimestamp() {
		return $this->old_document["_id"]->getTimestamp();
	}

	/**
	 * @return \MongoCollection
	 */
	static public function getCollection() {
		return MDB::getInstance()->db->{static::COLLECTION};
	}

	public function needsInsert() {
		return ($this->old_document == null);
	}

	/**
	 * @return MDB
	 */
	public static function getDBInstance() {
		return parent::getDBInstance(); // TODO: Change the autogenerated stub
	}
}





?>
