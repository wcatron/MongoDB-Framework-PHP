MongoDB-Framework-PHP
=====================

A MongoDB interface and ODM (assistant) all in one. Extremely flexible for any project.

# Overview

This is a set of helper methods and functions that I've used to make MongoDB development faster to develop.

**Setup**

Add your existing classes to the models.php file and have them extend *document*. Implement the toDocument and fromDocument methods and set the $collection variable. Replace the $config variable in mongo.php. You can now perform queries and get php objects back. 

`$mongo->getObjectFromID("ClassName","ID");`

## Documents & Objects

By using the toDocument and fromDocument methods classes can customize how their data is stored verses how it is presented to the PHP application. This allows for far greater flexibility than many other ODMs. It does however limit the ability to link object properties directly with document keys. This is the reasoning behind the KeysChanged model. It is understood this may be slower on the application side because of the extra calculations however we believe it is easier to develop if you don't have to worry about how property changes effect the underlying document structure.

## Normalizing

There are two normalizing methods that can take an object ID or an array of IDs and turn them into the objects they represent.

Examples:
` JSON
User Collection
{
	"_id":{"$oid":"1"}
	"username":"JohnDoe2000",
	"friends":["2","3"]
}
`

` PHP
class User extends document {
	var $friends; // must match property name for now.
	...
	function getFriends() {
		if (!isset($this->friends)) {
			$this->normalizePropertyArray('friends','User');
		}
		return $this->friends;
	}
}

$user = $mongo->getObjectFromId('User','1');
$friends = $user->getFriends();

`

## IDs as Strings

All IDs are strings. No converting between MongoIDs and strings. If someone sees a major issue here please speak up.