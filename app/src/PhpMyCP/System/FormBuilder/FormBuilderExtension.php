<?php

namespace PhpMyCP\System\FormBuilder;

class FormBuilderExtension extends \Twig_Extension {
	
	
	public function getFunctions() {
		
		$options = [
				'is_safe' 	=> array('html')
		];
		
		return [
				new \Twig_SimpleFunction('prop', [$this, 'drawProperty'], $options),
				new \Twig_SimpleFunction('attr', [$this, 'drawAttribute'], $options)
		];
	}
	
	
	public function drawProperty($var, array $data = null, $propName = null) {
		if($data == null) {
			$data = $this->app()->view()->getData();
		}
		
		//If not set or set value is null/false
		if(!isset($data[$var]) || !$data[$var]) {
			return '';
		}

		//Overrides rendered property name if data key not also property name (as requested)
		if($propName != null) {
			//render propname
			return $propName;
		}
		
		//render propname
		return $var;
	}
	
	public function drawAttribute($var, array $data = null, $default = null, $attrName = null, $preventEscaping = false) {
		//If data is not defined then obtain template data from Slim
		if($data == null) {
			$data = $this->app()->getData();
		}
		
		//Assign value or default value
		$args = null;
		if(!isset($data[$var])) {
			if($default == null) {
				return '';
			}
			
			$args = $default;
		
		} else {
			$args = $data[$var];
			
		}
		
		//If args is an array, join by space
		if(is_array($args)) {
			$args = join(" ", $args);
		}
		
		//Prevent ENT_QUOTES HTML escaping if requested
		if(!$preventEscaping) {
			$args = htmlspecialchars($args, ENT_QUOTES);
		}
		
		//Overrides rendered attribute id if data key not also attribute id (as requested)
		if($attrName != null) {
			//render attribute
			return "$attrName='$args'";
		}
		
		//render attribute
		return "$var='$args'";
	}
	
}