MongoDB-Framework-PHP
=====================

A MongoDB interface and ODM (assistant) all in one. Extremely flexible for any project.

# Overview

This is a set of helper methods and functions that I've used to make MongoDB development faster to develop.

**Setup**

Add your existing classes to the models.php file and have them extend *document*. Implement the toDocument and fromDocument methods and set the $collection variable. Replace the $config variable in mongo.php. You can now perform queries and get php objects back. 

`$mongo->getObjectFromID("ClassName","ID");`

## Normalizing

There are two normalizing methods that can take an object ID or an array of IDs and turn them into the objects they represent.

## IDs as Strings

All IDs are strings. No converting between MongoIDs and strings.