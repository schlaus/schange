[![Build Status](https://secure.travis-ci.org/schlaus/schange.png)](http://travis-ci.org/schlaus/schange)
[![Latest Stable Version](https://poser.pugx.org/schlaus/schange/version.svg)](https://packagist.org/packages/schlaus/schange)
[![Total Downloads](https://poser.pugx.org/schlaus/schange/downloads.svg)](https://packagist.org/packages/schlaus/schange)

Schange
=======

An extendable typejuggler.

Installation
------------
Either via [Composer](https://packagist.org/packages/schlaus/schange) by requiring "schlaus/schange": "dev-master"

Or just download and include src/schange.php

Usage
-----
What Schange does is to try to cast a variable to a given type by doing some conversions. The conversion logic is
based on my usecase, and could be somewhat off or completely ludicrous in different situations. Parental discretion
is adviced.

Syntax is: $result = schange::castTo("int", "42"); OR $result = schange::castToInt("42");
Once a custom casting function has been loaded, it can be used in the exact same fashion:
$result = schange::castTo("banana", 1); OR $result = schange::castToBanana(1);

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

### DISCLAIMER:
I made this class for my own specific purposes, and don't expect it to be immensely useful to other people.
However, I'm happy to help if you do find some use for it and run into problems.