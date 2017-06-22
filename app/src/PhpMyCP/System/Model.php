<?php
namespace PhpMyCP\System;

use Illuminate\Database\Eloquent\Model;
use PhpMyCP\System\System;

/**
 * 
 * @see
 */

abstract class Model extends Model {
	
	public function __call($name, $args) {
		System::__callStatic($name, $args);
	}
	
	public static function __callStatic($name, $args) {
		System::__callStatic($name, $args);
	}
	
	//Allows Schema Installation from SQL file
	public function installFromSQLFile($filename) {
		System::installFromSQLFile($filename);
	}
	
}