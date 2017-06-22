<?php 

namespace PhpMyCP\System;

use Slim\App;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Database\Capsule\Manager;

/**
 * PhpMyCP\System
 * @author Cody Rees-Whitley 
 *
 * @method \Slim\Slim app
 * @method array controllers
 * @method array models
 * @method \PhpMyCP\System\Navigation navigation
 * @method \Cartalyst\Sentinel\Native\Facades\Sentinel sentinel
 *
 */

abstract class System {
	
	public static $systemResources = [];
	
	//Creates Access methods for all System Resources
	public function __call($name, $args) {
		return self::__callStatic($name, $args);
	}
	
	public static function __callStatic($name, $args) {
		if(isset(self::$systemResources[$name])) {
			return self::$systemResources[$name];
		}
	
		throw new \BadMethodCallException("Method name \"$name\" does not exist in the current context.");
	}
	
	public static function isDefined($name) {
		return isset(self::$systemResources);
	}
	
	//Register Access Method for Resource
	public static function registerResource($name, $instance) {
		self::$systemResources[$name] = $instance;
	}
	
	public function installFromSQLFile($filename) {
		$filename = $this->appPath() . "/src/schema/$filename";

		//Get File Contents
		$schema = file_get_contents($filename);
		$schemaArr = explode(";", $schema); //Split at delimiter
		$schemaArr = array_splice($schemaArr, 0, sizeof($schemaArr) - 1); //Remove last Element (Blank)

		//Execute Schema Statements
		foreach($schemaArr as $tableSchema) {
			Manager::statement($tableSchema . ";");
		}
	} 
	
}