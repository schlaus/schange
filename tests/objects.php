<?php

class testObjectA {
	
	public $var1 = 1;
	public $var2 = 2;

	public function __toString()
	{
		return "test";
	}

}

class testObjectB {
	
	public $var1 = 1;
	public $var2 = 2;

}

class testObjectC {
	public function __toString()
	{
		return "123.56";
	}	
}