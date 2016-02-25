<?php

namespace wcatron\MongoDBFramework;

use MongoDB\Collection;
use wcatron\CommonDBFramework\DB;
use wcatron\CommonDBFramework\DBPagination;
use wcatron\MongoDBFramework\Document;
use \MongoDB\BSON\ObjectID as ObjectID;

/**
 * A MongoDB class connects to a Mongo DB database.
 */
class MDB extends DB {
    /**
     * MongoClient used for database queries.
     * @var \MongoDB\Database
     */
    var $db;

    function connect() {
        if (!isset($this->db)) {
            if (isset($this->config['user'])) {
                $m = new \MongoDB\Client("mongodb://" . $this->config['host'] . "/" . $this->config['db'], [ "username" => $this->config['user'],
                                                                                                 "password" => $this->config['pass'] ]);
            } else {
                $m = new \MongoDB\Client("mongodb://" . $this->config['host'] . "/" . $this->config['db']);
            }
            $this->db = $m->{$this->config['db']};
        }
    }

    function __construct() {
    }

    function saveDocument(Document $object) {
        if ($object->needsInsert()) {
            $newDocument = $object->toDocument();
            /** @var \MongoDB\InsertOneResult $results */
            $results = $this->db->{$object::COLLECTION}->insertOne($newDocument);
            $newDocument['_id'] = $results->getInsertedId();
            return $newDocument;
        } else {
            $updateQuery = $object->updateQuery();
            if ($updateQuery) {
                $this->connect();
                $collection = $this->db->selectCollection($object::COLLECTION,
                                                          [
                                                              "typeMap" => [
                                                                  'array'    => 'array',
                                                                  'root'     => 'array',
                                                                  'document' => 'array',
                                                              ]
                                                          ]);
                $resultDocument = $collection->findOneAndUpdate([ "_id" => new ObjectID($object->getID()) ], $updateQuery);
                return $object->toDocument();
            }
        }
    }

    function getObjectByKey($objectType, $field, $value) {
        /** @var Document $object */
        $object = new $objectType();
        $collection = $this->db->selectCollection($object::COLLECTION,
                                                  [
                                                      "typeMap" => [
                                                          'array'    => 'array',
                                                          'root'     => 'array',
                                                          'document' => 'array',
                                                      ]
                                                  ]);
        $document = $collection->findOne([ $field => $value ]);
        if ($document == null) {
            return false;
        }
        $object->fromDocument($document);
        return $object;
    }

    function getObjectsByKey($objectType, $field, $value, DBPagination $pagination = null) {
        return $this->getObjectsWithQuery($objectType, [ $field => $value ], $pagination);
    }

    function getObjectByID($objectType, $id) {
        return $this->getObjectByKey($objectType, '_id', new ObjectID($id));
    }

    function getCountForCollection($collection) {
        return $this->db->{$collection}->count();
    }

    function getObjectWithQuery($objectType, $query) {
        /** @var Document $object */
        $object = new $objectType();
        $collection = $object::COLLECTION;
        $document = $this->db->{$collection}->findOne($query);

        if (is_null($document)) {
            return false;
        }
        $object->fromDocument($document);
        return $object;
    }

    function getObjectsWithQuery($objectType, $query, DBPagination $pagination = null) {
        $object = new $objectType();
        $collection = $object::COLLECTION;
        if (is_null($pagination)) {
            $cursor = $this->db->{$collection}->find($query);
        } else {
            $cursor = $this->db->{$collection}->find($query)->sort([ $pagination->orderKey => $pagination->order ])->skip($pagination->skip)->limit($pagination->limit);
        }

        return self::getObjectsWithCursor($objectType, $cursor);
    }

    /**
     * @param $objectType
     * @param $cursor \MongoCursor
     */
    static function getObjectsWithCursor($objectType, $cursor) {
        $objects = [ ];
        foreach ($cursor as $document) {
            /** @var Document $object */
            $object = new $objectType();
            $object->fromDocument($document);
            $objects[] = $object;
        }
        return $objects;
    }

    function getAllObjects($objectType, DBPagination $pagination = null) {
        return $this->getObjectsWithQuery($objectType, [ ], $pagination);
    }

    function deleteObject($object) {
        return $this->db->{$object::COLLECTION}->findOneAndDelete([ "_id" => new ObjectID($object->getID()) ]);
    }


}

?>
