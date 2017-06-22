<?php 
namespace PhpMyCP\Controller\Authentication;


use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\System\Controller;

class Login extends Controller {
	
	public $title = "Login | Authentication";
	
	public function initRoutes() {
		$this->app()->get("/login", [$this, 'get'])->name('login');
		$this->app()->post("/login", [$this, 'login']);
	}
	
	
	public function get($renderParams = array()) {
		if($this->user()) {
			$this->app()->redirectTo("dashboard");
			return;
		}
		
		$renderParams = array_merge($renderParams, [
				'title' 		=> $this->title,
				'stylesheets'	=> [
						'views/authentication/css/login.css'
				]
		]);
		
		if(isset($_GET['redirect'])) {
			$renderParams['redirect'] = $_GET['redirect'];
		}
		 
		//Renders view 'authentication/login.tpl'
		$this->app()->render('authentication/login.twig', $renderParams);
	}
	
	public function login() {
		if($this->user()) {
			$this->app()->redirectTo("dashboard");
			return;
		}
		
		//Get POST data from slim request
		$post = $this->app()->request->post();
		
		//Validation: Checks if fields for missing data
		if(empty($post['input-login']) || empty($post['input-password'])) {
				
			//Render Page with custom error params
			$this->get([
					'errorMessage' => 'Missing username or password fields.',
					'formData' => [
							'input-login' => $post['input-login']
					]
			]);
				
			return;
		}
		
		$credentials = [
				'email'		=> $post['input-login'],
				'password'	=> $post['input-password']
		];
		
		//Attempts to get User via Login Credentials
		$user = Sentinel::findByCredentials($credentials);
		
		//If user is not found or Credentials are invalid
		if(!$user || !Sentinel::validateCredentials($user, $credentials)) {
				
			//Render Page with custom error params
			$this->get([
					'errorMessage' => 'Invalid username or password.',
					'formData' => [
							'input-login' => $post['input-login']
					]
			]);
		
			return;
		}
		
		//Authenticate User
		Sentinel::login($user, isset($post['input-remember']));
		
		//If redirect is set
		if(isset($_GET['redirect'])) {
			$this->app()->redirectTo($this->app()->request->get('redirect'));
			return;
		}
		
		//User is Authenticated, Redirect to named route 'home'
		$this->app()->redirectTo('dashboard');
	}
}
