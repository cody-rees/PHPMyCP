<?php 
namespace PhpMyCP\Controller\Authentication;


use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\System\Controller;

class Logout extends Controller {
	
	public $title = "Logout | Authentication";
	
	public function initRoutes() {
		$this->app()->get("/logout", [$this, 'logout'])->name('logout');
	}

	public function logout() {
		Sentinel::logout();
		$this->app()->redirectTo('login');
	}
	
}
