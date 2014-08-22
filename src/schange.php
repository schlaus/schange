<?php
/**
 * Schange - An extendable type juggler
 * 
 * @author      Klaus 'Schlaus' Karkia (http://schlaus.karkia.me)
 * @copyright   Copyright (c) 2014 Klaus Karkia
 * @license 	http://schlaus.mit-license.org MIT
 * @link        http://github.com/schlaus/schange
 * @version     1.0
 * 
 * *USAGE*
 * What Schange does is to try to cast a variable to a given type by doing some conversions. The conversion logic is
 * based on my usecase, and could be somewhat off or completely ludicrous in different situations. Parental discretion
 * is adviced.
 * 
 * Syntax is: $result = schange::castTo("int", "42"); OR $result = schange::castToInt("42");
 * Once a custom casting function has been loaded, it can be used in the exact same fashion:
 * $result = schange::castTo("banana", 1); OR $result = schange::castToBanana(1);
 * 
 * The code and tests are pretty straightforward, and should give a pretty good idea of what kind of conversions are done.
 * Supported types are *boolean*, *integer*, *string*, *array*, *float*, and *object*. Here's a few examples:
 * 
 * string("true") => int(1)
 * string("true") => array("t", "r", "u", "e")
 * float(4.5) => int(5)
 * float(4.4) => int(4)
 * array("tes", "ting", "!", 123) => string("testing!123")
 * NULL => empty variable of target type
 * 
 * Furthermore, associative arrays can be converted to objects (instances of stdClass) with each key => value converted into
 * property => value. When converting from objects to arrays, only public properties are preserved. When converting an object
 * to string, the conversion is first attempted via the magic __toString() method, and if one doesn't exist, the object is
 * first converted to an array, and then to string. Null is only ever returned if given variable can't be cast to requested type,
 * for example when trying to convert a boolean value to an array. If the original variable was null, it's converted to an empty
 * instance of the requested type.
 * 
 * For examining whether a variable can be converted to a type, use schange::canCastTo($type, $var); For a list (as an array)
 * of possible target types, use schange::castable($var);
 * 
 * DISCLAIMER: I made this class for my own specific purposes, and don't expect it to be immensely useful to other people.
 * However, I'm happy to help if you do find some use for it and run into problems.
 * 
 */

namespace schlaus\schange;

/**
 * The Schange class
 * 
 * @package schlaus\schange
 */
class schange {

	/**
	 * Used to convert from long type names to short ones
	 * @var array
	 * @internal
	 */
	private static $_types = array(
		"boolean" 	=> "bool",
		"integer" 	=> "int",
		"string" 	=> "str",
		"array" 	=> "arr",
		"double" 	=> "float",
		"object"	=> "obj"
	);

	/**
	 * Holds custom caster closures
	 * @var array
	 * @internal
	 */
	private static $_casters = array();

	/**
	 * Holds default caster closures
	 * @var array
	 * @internal
	 */
	private static $_defaultCasters = array();

	/**
	 * Handles the loading of custom casting function
	 * 
	 * Can be used to load functions one at a time or as an array. Can also be used to overwrite default casters. Casters
	 * cannot currently be removed once loaded, but can be overwritten.
	 * 
	 * @param mixed $name Either the name of the caster if loading a single function OR an array of name => function pairs
	 * @param callable $fn The caster function when loading a single caster
	 * @return void
	 */
	public static function loadCaster($name, $fn = null)
	{
		if (is_array($name)) {
			foreach ($name as $key => $tmp) {
				self::loadCaster($key, $tmp);
			}
		} else {
			if (array_key_exists($name, self::$_types)) $name = self::$_types[$name];
			self::$_casters[$name] = $fn;
		}
	}

	/**
	 * Magic method to allow different usage styles
	 * 
	 * Instead of using the castTo($type, $var) style, you can also use castTo(Type), ie. castToString($var)
	 * 
	 * @param string $fn Name of the target type
	 * @param array $args Passed arguments
	 * @return mixed Either the type casted variable, or null if casting is not possible
	 */
	public static function __callStatic($fn, $args)
	{
		if (preg_match('/castTo(.+)/', $fn, $match)) {
			$fn = strtolower($match[1]);
		}
		return self::castTo($fn, $args[0]);
	}
	
	/**
	 * A helper function to check to which types a given variable can be cast to
	 * 
	 * Returns a list of all possible types as an array.
	 * 
	 * @param mixes $var The variable to be tested
	 * @return array An array of types the given variable can be cast to
	 */
	public static function castable($var)
	{
		$possibles = array();
		foreach(self::$_types as $type) {
			if (self::canCastTo($type, $var)) $possibles[] = $type;
		}
		return $possibles;
	}

	/**
	 * A helper function to check if a variable can be cast to given type
	 * 
	 * @param string $type The type to be tested
	 * @param mixed $var The variable to be tested
	 * @return bool
	 */
	public static function canCastTo($type, $var)
	{
		if (null === self::castTo($type, $var)) return false;
		return true;
	}

	/**
	 * The main entry function used to cast variables to other types
	 *  
	 * @param string $type The type you wish to cast the given variable to
	 * @param mixed $var The variable to be cast to given type
	 * @throws Exception If a call is made to a casting function that has not been loaded
	 * @return mixed The result of the cast, or null if casting was not possible
	 */
	public static function castTo($type, $var)
	{
		if (array_key_exists($type, self::$_types)) $type = self::$_types[$type];
		

		if (isset(self::$_casters[$type])) {
			$fn = self::$_casters[$type];
		} else {
			if (empty(self::$_defaultCasters)) self::_defaultCasters();
			if (isset(self::$_defaultCasters)) $fn = self::$_defaultCasters[$type];
			else throw new \Exception("Could not find a function to cast to $type");
		}

		$currentType = gettype($var);

		return $fn($var, $currentType);
		
	}

	/**
	 * Sets up the default casting functions
	 * 
	 * @internal
	 * @return void
	 */
	private static function _defaultCasters()
	{
		self::$_defaultCasters = array(
			"int" => function($var, $currentType) {
				switch($currentType) {
					case "boolean":
						if ($var === true) return 1;
						else return 0;
						break;
					case "integer":
						return $var;
						break;
					case "string":
						if (empty($var)) return 0;
						if (is_numeric($var)) return (int) round(\schlaus\schange\schange::castToFloat($var));
						if (\schlaus\schange\schange::canCastTo("bool", $var)) return \schlaus\schange\schange::castToInt(\schlaus\schange\schange::castToBool($var));
						break;
					case "array":
						if (empty($var)) return 0;
						if (count($var) == 1) {
							return \schlaus\schange\schange::castToInt(reset($var));
						}
						break;
					case "double":
						return (int) round($var);
						break;
					case "object":
						if (null !== $tmp = \schlaus\schange\schange::castToStr($var)) return \schlaus\schange\schange::castToInt($tmp);
						if (null !== $tmp = \schlaus\schange\schange::castToArr($var)) return \schlaus\schange\schange::castToInt($tmp);
						return null;
						break;
					case "NULL":
						return 0;
						break;
				}

				return null;				
			},
			"float" => function($var, $currentType) {
				switch($currentType) {
					case "boolean":
						if ($var === true) return 1.0;
						else return 0.0;
						break;
					case "integer":
						return floatval($var);
						break;
					case "string":
						if (empty($var)) return 0.0;
						if (is_numeric($var)) return floatval($var);
						if (\schlaus\schange\schange::canCastTo("bool", $var)) return \schlaus\schange\schange::castToFloat(\schlaus\schange\schange::castToBool($var));
						return null;
						break;
					case "array":
						if (empty($var)) return 0.0;
						if (count($var) == 1) {
							return \schlaus\schange\schange::castToFloat(reset($var));
						}
						return null;
						break;
					case "double":
						return $var;
						break;
					case "object":
						if (null !== $tmp = \schlaus\schange\schange::castToStr($var)) return \schlaus\schange\schange::castToFloat($tmp);
						if (null !== $tmp = \schlaus\schange\schange::castToArr($var)) return \schlaus\schange\schange::castToFloat($tmp);
						return null;
						break;
					case "NULL":
						return 0.0;
						break;
				}

				return null;
			},
			"str" => function($var, $currentType) {
				switch($currentType) {
					case "boolean":
						if ($var === true) return "true";
						else return "false";
						break;
					case "integer":
						return (string) $var;
						break;
					case "string":
						return $var;
						break;
					case "array":
						if (empty($var)) return "";
						if (count($var) == 1) {
							return \schlaus\schange\schange::castToStr(reset($var));
						}
						$parsedStr = "";
						foreach ($var as $val) {
							if (null === $parsedStr .= \schlaus\schange\schange::castToStr($val)) return null;
						}
						return $parsedStr;
						break;
					case "double":
						return (string) $var;
						break;
					case "object":
						if (method_exists($var, "__toString")) return (string) $var;
						if (null !== $tmp = \schlaus\schange\schange::castToArr($var)) return \schlaus\schange\schange::castToStr($tmp);
						return null;
						break;
					case "NULL":
						return "";
						break;
				}

				return null;
			},
			"bool" => function($var, $currentType) {
				switch($currentType) {
					case "boolean":
						return $var;
						break;
					case "integer":
						return (bool) $var;
						break;
					case "string":
						if (strtolower($var) === "true") return true;
						if (strtolower($var) === "false") return false;
						if (is_numeric($var)) return (bool) \schlaus\schange\schange::castToInt($var);
						return null;
						break;
					case "array":
						if (empty($var)) return false;
						if (count($var) == 1) {
							return \schlaus\schange\schange::castToBool(reset($var));
						}
						return null;
						break;
					case "double":
						return (bool) $var;
						break;
					case "object":
						if (null !== $tmp = \schlaus\schange\schange::castToStr($var)) return \schlaus\schange\schange::castToBool($tmp);
						if (null !== $tmp = \schlaus\schange\schange::castToArr($var)) return \schlaus\schange\schange::castToBool($tmp);
						return null;
						break;
					case "NULL":
						return false;
						break;
				}

				return null;
			},
			"arr" => function($var, $currentType) {
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
			},
			"obj" => function($var, $currentType) {
				switch($currentType) {
					case "boolean":
					case "integer":
					case "string":
					case "double":
						if (null !== $arr = \schlaus\schange\schange::castToArr($var)) return \schlaus\schange\schange::castToObj($arr);
						return null;
						break;
					case "array":
						$obj = json_decode(json_encode($var), false);
						if (is_object($obj)) return $obj;
						return null;
						break;
					case "object":
						return $var;
						break;
					case "NULL":
						return new stdClass();
						break;
				}

				return null;
			}

		);
	}

}