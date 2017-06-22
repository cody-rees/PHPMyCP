<?php
namespace PhpMyCP\System;

abstract class Controller extends System {

	abstract function initRoutes();
	function onNavigation() {}

	//Returns a Middleware Function used for Authentication
	public function requiresAuth($permission = null) {
		//Add Permission to Permissions Array
		if($permission != null && !array_key_exists($permission, self::$permissions)) {
			self::$permissions[$permission] = 'Undefined';
		}
	
		return function (Route $route) use ($permission) {
			$app = Slim::getInstance();
				
			if(!self::$user) {
				//Add redirect back to the current page
				if($route->getName()) {
					$redirectURL = $app->urlFor('login') . "?redirect=" . $route->getName();
					$app->redirect($redirectURL);
				}
	
				$app->redirectTo('login');
			}
				
			if(!$permission) {
				return;
			}
	
			if(!self::$user->hasAccess($permission)) {
				$app->render('dashboard/insufficient-permissions.tpl', [
						'header'	=> 'Insufficient Permissions',
						'subheader'	=> 'You do not have the required permissions to access this page.'
				]);
			}
		};
	}
	
}