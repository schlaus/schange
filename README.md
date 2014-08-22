[![Build Status](https://secure.travis-ci.org/schlaus/schange.png)](http://travis-ci.org/schlaus/schange)
[![Latest Stable Version](https://poser.pugx.org/schlaus/schange/version.svg)](https://packagist.org/packages/schlaus/schange)
[![Total Downloads](https://poser.pugx.org/schlaus/schange/downloads.svg)](https://packagist.org/packages/schlaus/schange)
[![License](https://poser.pugx.org/schlaus/schange/license.svg)](https://packagist.org/packages/schlaus/schange)

Schange
=======

An extendable type juggler.

Installation
------------
Either via [Composer](https://packagist.org/packages/schlaus/schange) by requiring "schlaus/schange": "dev-master"

Or just download and include src/schange.php

Usage
-----
What Schange does is to try to cast a variable to a given type by doing some conversions. The conversion logic is
based on my usecase, and could be somewhat off or completely ludicrous in different situations. Parental discretion
is adviced.

Syntax is:

```php
$result = schange::castTo("int", "42");
// OR
$result = schange::castToInt("42");
```
Once a custom casting function has been loaded, it can be used in the exact same fashion:

```php
$result = schange::castTo("banana", 1);
// OR
$result = schange::castToBanana(1);
```

The code and tests are pretty straightforward, and should give a pretty good idea of what kind of conversions are done.
Supported types are *boolean*, *integer*, *string*, *array*, *float*, and *object*. Here's a few examples:

```php
string("true") => int(1)
string("true") => array("t", "r", "u", "e")
float(4.5) => int(5)
float(4.4) => int(4)
array("tes", "ting", "!", 123) => string("testing!123")
NULL => empty variable of target type
```

Furthermore, associative arrays can be converted to objects (instances of stdClass) with each key => value converted into
property => value. When converting from objects to arrays, only public properties are preserved. When converting an object
to string, the conversion is first attempted via the magic __toString() method, and if one doesn't exist, the object is
first converted to an array, and then to string. Null is only ever returned if given variable can't be cast to requested type,
for example when trying to convert a boolean value to an array. If the original variable was null, it's converted to an empty
instance of the requested type.

For examining whether a variable can be converted to a type, use schange::canCastTo($type, $var); For a list (as an array)
of possible target types, use schange::castable($var);

### Using custom casters

Casting functions receive two parameters: the variable to be cast, and the current type of the variable. Thus each function can
only handle casting to one target type. For an example of how a casting function should look like, let's look at the castToArr
function:

```php
function($var, $currentType) {
	switch($currentType) {
		case "boolean":
			return null;
			break;
		case "integer":
		case "double":
			$arr = str_split(\schlaus\schange\schange::castToStr($var));
			foreach ($arr as &$val) {
				if ($val !== ".") $val = intval($val);
			}
			return $arr;
			break;
		case "string":
			return str_split($var);
			break;
		case "array":
			return $var;
			break;
		case "object":
			return json_decode(json_encode($var), true);
			break;
		case "NULL":
			return array();
			break;
	}

	return null;
}
```

Casting functions should return null only when the requested conversion can't be done, and in case the input was null to begin with,
an empty instance of the target type. There are no separate functions for evaluating whether a variable can be cast to another type,
but instead the testing is done using the actual casting function, and a result returned based on whether the result was null or not.

Custom casters can be loaded either one function at a time, or as an array of casters:

```php
schange::loadCaster("targetType", function($var, $currentType) {
	/*
	.
	.	Conversion logic goes here
	.
	*/
});

// OR

schange::loadCaster(array(
	"type1" => function($var, $currentType) { ... },
	"type2" => function($var, $currentType) { ... },
));
```

Once loaded, custom functions can be used just in the same way as the default ones. Custom casters take precedence over the default ones,
so for example if you register a function for casting to string, that function will be used instead of the default one.

One thing to note when writing custom functions is that if you want to support PHP 5.3 you can't use *self::...* in your function,
since in PHP 5.3 the closure is not run in the correct scope. Instead, use the full namespaced class name.

DISCLAIMER:
-----------
I made this class for my own specific purposes, and don't expect it to be immensely useful to other people.
However, I'm happy to help if you do find some use for it and run into problems.