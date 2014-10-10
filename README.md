MongoDB-Framework-PHP
=====================

A MongoDB interface and ODM (assistant) all in one. Extremely flexible for any project.

# Overview

This is a set of helper methods and functions that I've used to make MongoDB development faster to develop.

**Setup**

Create models for documents and have them extend *document*. Implement the toDocument and fromDocument methods and set the $collection constant. Replace the $config variable in mongo.php. You can now perform queries and get php objects back.

`Class::getByID("ID");`

# Models & Mongo

### Models

Your classes. Add two functions and a variable and allow any model to create objects.

```
class YourClass extend document {

	const COLLECTION = 'YourCollection';

	function toDocument() {
		$document = parent::toDocument();
		// Your code setting the documents key/value pairs.
		return $document;
	}

	function fromDocument($document) {
		parent::fromDocument($document);
		// Your code setting the objects properties.
	}
}
```

Saving is extremely simple when you have an object whose class extends document.

`$object->save();`

### Mongo

Your connection to mongo. To get your documents as objects use this singleton `mongo_db::getInstance()`

**getObjectByID('ClassName',$id)**

This will return your exact object.

**getObjectsFromQuery('ClassName',$query)**

An array of objects based on a custom query. Not all queries need to be written out though.

**getObjectsByKey('ClassName', 'KeyName', $value)**

If you're only searching by one key-value pair. This simple function will work.

When writing content specific queries for your application it's recommended that you add them to the mongo_db class and reuse them as often as possible. This framework can certainly expand to include more useful queries. The idea though is that all the mongo queries are generic and return php objects not documents.

## Documents & Objects

By using the toDocument and fromDocument methods classes can customize how their data is stored verses how it is presented to the PHP application. This allows for far greater flexibility than many other ODMs. It does however limit the ability to link object properties directly with document keys. This is the reasoning behind the KeysChanged model. It is understood this may be slower on the application side because of the extra calculations however we believe it is easier to develop if you don't have to worry about how property changes effect the underlying document structure.

## Normalizing Arrays and Objects

There are two normalizing methods that can take an object ID or an array of IDs and turn them into the objects they represent only when they are asked for.

Examples:
```
JSON
User Collection
{
	"_id":{"$oid":"1"}
	"username":"JohnDoe2000",
	"friends":["2","3"]
}
```

```
PHP
class User extends document {
	var $friends; // must match property name for now.
	...

	function User() {
		$this->setArrayForKey('User','friends');
	}
}

$user = User::getByID('1');
$friends = $user->friends->get();

```

## IDs as Strings

All IDs are treated as strings and converted to MongoIDs by the mongo_db class. No converting between MongoIDs and strings. If someone sees a major issue here please speak up.
