<?php 
namespace PhpMyCP\Controller\Dashboard;


use PhpMyCP\System\Controller;

class Dashboard extends Controller {
	
	public $title = "Dashboard";

	public function initRoutes() {
		$this->app()->get('/dashboard', [$this, 'get'])->name('dashboard');		
	}
	
	public function onNavigation() {
		$this->navigation()->defaultCategory
			->add('_home',  ['dashboard', 'Dashboard'], 'dashboard');
	}
	
	public function get($renderParams = array()) {
		$breadcrumbs = array();
		
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('dashboard'),
				'name'	=> 'Dashboard',
				'icon'	=> 'dashboard'
		];
		
		$renderParams = array_merge($renderParams, [
				'header'		=> 'Dashboard',
				'subheader'		=> 'Useful Utilities and Notifications here',
				'title' 		=> $this->title,
				'active'		=> '_home',
				'breadcrumbs' 	=> $breadcrumbs
		]);
		
		$this->app()->render('dashboard/dashboard.twig', $renderParams); 
	}
	
	
	
}