<?php 
namespace PhpMyCP\Controller\Account;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\System\Controller;
use PhpMyCP\System\Navigation\Dropdown;
use Violin\Violin;

class Account extends Controller {
	
	private $title = "Account | Change Password";
	
	public function initRoutes() {
		$authMiddleware = $this->requiresAuth();
		
		$this->app()->get('/account/change-password', [$this, 'get'])->name('change-pass');
		$this->app()->post('/account/change-password', [$this, 'changePassword']);
	}
	
	public function onNavigation() {
		$dropdown = new Dropdown('zz_account', ['user', 'Account Settings']);
		$dropdown->add("change-pass", 'Change Password', 'change-pass');
		$dropdown->add('logout', 'Logout', 'logout');
	
		$this->navigation()->defaultCategory->addDropdown($dropdown);
	}
	
	
	public function get($renderParams = array()) {
		
		//Reset Password Form
		$formResetPassword = array();
		
		$formResetPassword['section-password'] = [
				'password'		=> [
						'name'		=> 'input-password',
						'type'		=> 'password',
						'label'		=> "Password",
				],
				'password-confirm'	=> [
						'name'		=> 'input-password-confirm',
						'type'		=> 'password',
						'label'		=> 'Password Confirm',
						'htmlAfter'	=> "</br></br>"
				]
		];
		
		
		
		$renderParams['formResetPassword'] = [
				'sections'	=> $formResetPassword,
				'action'	=> $this->app()->urlFor('change-pass'),
				'method'	=> 'post',
				'submit'	=> 'Change Password',
				'submitInFooter'	=> true
		];
		
		
		$breadcrumbs = array();

		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('change-pass'),
				'name'	=> 'Account'
		];

		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('change-pass'),
				'name'	=> 'Change Password'
		];
	
	
		$renderParams = array_merge($renderParams, [
				'header'		=> 'Change Password',
				'subheader'		=> 'View Account information and Change Password',
				'title' 		=> $this->title,
				'active'		=> 'zz_account',
				'breadcrumbs' 	=> $breadcrumbs
	
		]);
	
		$this->app()->render('account/change_password.twig', $renderParams);
	}
	
	public function changePassword($renderParams = array()) {
		
		//Validation
		$v = new Violin();
		$v->addFieldMessages([
				'input-password'			=> [
						'required'	=> 'Password Field is required.',
						'min'		=> 'Password must be at least 8 characters.',
						'max'		=> 'Password cannot be larger than 255 characters.'
				],
				'input-password-confirm'	=> [
						'required'	=> 'Password Confirm Field is required.',
						'matches'	=> 'Password Confirm and Password fields do not match.'
				]
		]);
			
		$v->validate([
				'input-password'			=> [$_POST['input-password'], 			'required|min(5)|max(255)'],
				'input-password-confirm'	=> [$_POST['input-password-confirm'],	'required|matches(input-password)']
		]);
			
		//If Validator fails
		if($v->fails()) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not update user for the following reasons:<br><ol><li>' . join("</li><li>", $v->errors()->all()) . '</li></ol>';
				
		}
		else {
			//Sentinel Update User Password
			Sentinel::update($this->user(), ['password' => $_POST['input-password']]);
	
			$renderParams['calloutType'] = 'callout-success';
			$renderParams['calloutMessage']	= 'User password has been successfully changed.';
				
		}
		
		$this->get($renderParams);
	}
	
}