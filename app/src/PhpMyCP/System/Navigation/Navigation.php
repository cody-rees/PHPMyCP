<?php 
namespace PhpMyCP\System\Navigation;

use Slim\Slim;
use PhpMyCP\System\System;

class Navigation extends System {
	
	//Autoload as System Resource
	public $autoload = "navigation";
	
	public $defaultCategory;
	public $adminCategory;
	
	protected $categories = array();

	public function __construct() {
		$this->defaultCategory 		= new Category('Main Navigation', false);
		$this->adminCategory 		= new Category( ['server', 'Administration']);

		$this->categories[] = $this->defaultCategory;
		$this->categories[] = $this->adminCategory;
	}
	public function categories() {
		return $this->categories;
	}
	
}

class Category {
	
	protected $items = array();
	protected $tagline;
	protected $hidden;
	
	public function __construct($tagline, $hidden = false) {
		$this->hidden = $hidden;
		$this->tagline = $tagline;
	}

	public function tagline() {
		return $this->tagline;		
	}
	
	public function isHidden() {
		return $this->hidden;
	}
	
	
	public function add($name, $tagline, $route, $namedRoute = true) {
		if($namedRoute) {
			$route = Slim::getInstance()->urlFor($route);
		}
		
		$this->items[] = new Item($name, $tagline, $route);
		$this->sort();
	}
	
	public function addDropdown(Dropdown $dropdown) {
		$this->items[] = $dropdown;
		$this->sort();
	}
	
	public function items() {
		return $this->items;
	}
	
	protected function sort() {
		usort($this->items, array($this, "compare"));
	}
	
	public function compare($a, $b) {
		return strcmp($a->name(), $b->name());
	}
	
}

class Dropdown {

	protected $items = array();
	protected $name;
	protected $tagline;
	
	public function __construct($name, $tagline) {
		$this->name = $name;
		$this->tagline = $tagline;
	}
	
	public function name() {
		return $this->name;
	}
	
	public function tagline() {
		return $this->tagline;
	} 
	
	public function items() {
		return $this->items;
	}
	

	public function add($name, $tagline, $route, $namedRoute = true) {
		if($namedRoute) {
			$route = Slim::getInstance()->urlFor($route);
		}
	
		$this->items[] = new Item($name, $tagline, $route);
	}

	//Used by Twig Templating Engine
	public function isDropdown() {
		return true;
	}
}
	
class Item {
	
	protected $name;
	protected $tagline;
	protected $route;

	public function __construct($name, $tagline, $route) {
		$this->name = $name;
		$this->tagline = $tagline;
		$this->route = $route;
	}
	
	public function name() {
		return $this->name;
	}
	
	public function tagline() {
		return $this->tagline;
	} 
	
	public function route() {
		return $this->route;
	}
	
	//Used by Twig Templating Engine
	public function isDropdown() {
		return false;
	}
}
