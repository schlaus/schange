<?php

require ('objects.php');

use \schlaus\schange\schange;

class schangeTest extends PHPUnit_Framework_TestCase
{

    private static $source = array(
        "str" => array(
            "str"   => "test",
            "int"   => "123",
            "float" => "123.45",
            "bool"  => "false",
            "other" => "!%&"
        ),
        "int" => array(
            "int"   => 123,
            "bool"  => 1
        ),
        "bool" => true,
        "float" => array(
            "float" => 123.45,
            "int"   => 123.0,
            "bool"  => 0.0
        ),
        "arr" => array(
            "str"   => array("a", "b", "c", 1),
            "int"   => array(5),
            "bool"  => array(true)
        )
    );

    public static function setUpBeforeClass()
    {
        self::$source['obj'] = array(
            "str"   => new testObjectA(),
            "arr"   => new testObjectB()
        );
    }

    public function testStrToInt() {

        $this->assertTrue(schange::canCastTo("int", "123"));
        $this->assertTrue(schange::canCastTo("int", "123.45"));
        $this->assertTrue(schange::canCastTo("int", "true"));
        $this->assertFalse(schange::canCastTo("int", "!#%"));

        $this->assertSame(schange::castTo("int", "123"), 123);
        $this->assertSame(schange::castTo("int", "123.45"), 123);
        $this->assertSame(schange::castTo("int", "false"), 0);

    }

    public function testStrToArr() {

        $this->assertTrue(schange::canCastTo("arr", "123"));
        $this->assertTrue(schange::canCastTo("arr", "123.45"));
        $this->assertTrue(schange::canCastTo("arr", "true"));
        $this->assertTrue(schange::canCastTo("arr", "!#%"));

        $this->assertSame(schange::castTo("arr", "!%&"), array("!", "%", "&"));

    }

    public function testStrToBool() {

        $this->assertTrue(schange::canCastTo("bool", "123"));
        $this->assertTrue(schange::canCastTo("bool", "123.45"));
        $this->assertTrue(schange::canCastTo("bool", "true"));
        $this->assertFalse(schange::canCastTo("bool", "!%&"));

        $this->assertSame(schange::castTo("bool", "123"), true);
        $this->assertSame(schange::castTo("bool", "123.45"), true);
        $this->assertSame(schange::castTo("bool", "false"), false);

    }

    public function testStrToStr() {
        $this->assertSame(schange::castTo("str", "test"), "test");
        $this->assertSame(schange::castTo("str", "123"), "123");

    }

    public function testStrToObj() {
        $this->assertFalse(schange::canCastTo("obj", "test"));
    }

    public function testStrToFloat() {
        $this->assertTrue(schange::canCastTo("float", "123.45"));
        $this->assertTrue(schange::canCastTo("float", "123"));
        $this->assertFalse(schange::canCastTo("float", "!%&"));

        $this->assertSame(schange::castTo("float", "123.45"), 123.45);
        $this->assertSame(schange::castTo("float", "123"), 123.0);
        $this->assertSame(schange::castTo("float", "true"), 1.0);

    }



    public function testIntToStr() {
        $this->assertSame(schange::castTo("str", 123), "123");

    }

    public function testIntToBool() {
        $this->assertSame(schange::castTo("bool", 1), true);
        $this->assertSame(schange::castTo("bool", 0), false);

    }

    public function testIntToArr() {
        $this->assertSame(schange::castTo("arr", 123), array(1,2,3));
        $this->assertSame(schange::castTo("arr", 0), array(0));
    }

    public function testIntToObj() {
        $this->assertFalse(schange::canCastTo("obj", 123));

    }

    public function testIntToInt() {
        $this->assertSame(schange::castTo("int", 123), 123);
    }

    public function testIntToFloat() {
        $this->assertSame(schange::castTo("float", 123), 123.0);
    }



    public function testBoolToStr() {
        $this->assertSame(schange::castTo("str", true), "true");
        $this->assertSame(schange::castTo("str", false), "false");
    }

    public function testBoolToInt() {
        $this->assertSame(schange::castTo("int", true), 1);
        $this->assertSame(schange::castTo("int", false), 0);
    }

    public function testBoolToArr() {
        $this->assertSame(schange::castTo("arr", true), null);
    }

    public function testBoolToObj() {
        $this->assertSame(schange::castTo("obj", true), null);
    }

    public function testBoolToBool() {
        $this->assertSame(schange::castTo("bool", true), true);
        $this->assertSame(schange::castTo("bool", false), false);
    }

    public function testBoolToFloat() {
        $this->assertSame(schange::castTo("float", true), 1.0);
        $this->assertSame(schange::castTo("float", false), 0.0);

    }



    public function testFloatToStr() {
        $this->assertSame(schange::castTo("str", 1.0), "1");
    }

    public function testFloatToInt() {
        $this->assertSame(schange::castTo("int", 1.0), 1);
        $this->assertSame(schange::castTo("int", 1.5), 2);

    }

    public function testFloatToArr() {
        $this->assertSame(schange::castTo("arr", 1.0), array(1));
        $this->assertSame(schange::castTo("arr", 1.5), array(1, ".", 5));

    }

    public function testFloatToObj() {
        $this->assertFalse(schange::canCastTo("obj", 123.45));
    }

    public function testFloatToBool() {
        $this->assertSame(schange::castTo("bool", 123.45), true);
        $this->assertSame(schange::castTo("bool", 0.0), false);
    }

    public function testFloatToFloat() {
        $this->assertSame(schange::castTo("float", 123.45), 123.45);

    }



    public function testArrToStr() {
        $this->assertSame(schange::castTo("str", array("t", "e", "s", "t")), "test");
        $this->assertSame(schange::castTo("str", array("t", "e", "s", "t", 1, 23.45)), "test123.45");
        $this->assertSame(schange::castTo("str", array("testing")), "testing");

        $this->assertTrue(schange::canCastTo("str", array(new stdClass())));
    }

    public function testArrToInt() {
        $this->assertSame(schange::castTo("int", array(123)), 123);
        $this->assertSame(schange::castTo("int", array(123.45)), 123);
        $this->assertSame(schange::castTo("int", array("123")), 123);

        $this->assertFalse(schange::canCastTo("int", array("a", 2)));
    }

    public function testArrToArr() {

        $obj = new stdClass();

        $this->assertSame(schange::castTo("arr", array($obj)), array($obj));
    }

    public function testArrToObj() {

        $arr = array("var1" => 1, "var2" => 2);
        $obj = schange::castTo("obj", $arr);

        $this->assertObjectHasAttribute("var1", $obj);
        $this->assertSame($obj->var1, 1);

        $this->assertFalse(schange::canCastTo("obj", array("a", 2)));

    }

    public function testArrToBool() {
        $this->assertSame(schange::castTo("bool", array(true)), true);
        $this->assertSame(schange::castTo("bool", array("true")), true);

    }

    public function testArrToFloat() {
        $this->assertSame(schange::castTo("float", array(123.45)), 123.45);

    }



    public function testObjToStr() {
        require_once('objects.php');

        // Has a __toString() function
        $objA = new testObjectA();

        // Doesn't have a __toString() function
        $objB = new testObjectB();

        $this->assertSame(schange::castTo("str", $objA), "test");

        $this->assertTrue(schange::canCastTo("str", $objB));

        $this->assertSame(schange::castTo("str", $objB), "12");
    }

    public function testObjToInt() {
        require_once('objects.php');

        $objB = new testObjectB();
        $objC = new testObjectC();

        $this->assertSame(schange::castTo("int", $objC), 124);

        $this->assertTrue(schange::canCastTo("int", $objB));

        $this->assertSame(schange::castTo("int", $objB), 12);
    }

    public function testObjToArr() {
        require_once('objects.php');

        $objB = new testObjectB();
        $objC = new testObjectC();

        $arr = array("var1" => 1, "var2" => 2);

        $this->assertSame(schange::castTo("arr", $objB), $arr);

        $this->assertTrue(schange::canCastTo("arr", $objC));

    }

    public function testObjToObj() {
        require_once('objects.php');

        $objB = new testObjectB();

        $this->assertSame(schange::castTo("obj", $objB), $objB);

    }

    public function testObjToBool() {
        $obj = new stdClass();
        $obj->a = true;

        $this->assertSame(schange::castTo("bool", $obj), true);

        $this->assertFalse(schange::canCastTo("bool", new stdClass()));
    }

    public function testObjToFloat() {

        $obj = new stdClass();
        $obj->a = 1;
        $obj->b = 2.5;

        $this->assertSame(schange::castTo("float", new stdClass()), 0.0);

        $this->assertSame(schange::castTo("float", $obj), 12.5);
    }

    /**
     * @expectedException     Exception
     */
    public function testNonexistentCaster() {
        schange::castTo("bananas", "test");
    }

    public function testLoadingCaster() {
        schange::loadCaster("bananas", function($var, $type) { return "a banana!"; });

        $this->assertSame(schange::castTo("bananas", "test"), "a banana!");
        $this->assertSame(schange::castToBananas("test"), "a banana!");
    }

    public function testLoadingMultipleCasters() {

        $casters = array(
            "banana1" => function($var, $type) { return "a banana!"; },
            "banana2" => function($var, $type) { return "2 bananas!"; }
        );

        schange::loadCaster($casters);

        $this->assertSame(schange::castToBanana1("test"), "a banana!");
        $this->assertSame(schange::castToBanana2("test"), "2 bananas!");
    }

    public function testOverloadingDefaultCaster() {
        schange::loadCaster("string", function($var, $type) { return "More bananas!"; });

        $this->assertSame(schange::castToStr(123.45), "More bananas!");
    }

    public function testCastableList() {
        $var = "test";

        $list = array("str", "arr");

        $this->assertSame(schange::castable($var), $list);
    }

}