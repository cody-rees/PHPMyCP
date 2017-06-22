<?php
namespace PhpMyCP\Controller\Administration;

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\System\Controller;
use PhpMyCP\Model\User;
use Violin\Violin;
use PhpMyCP\Controller\User\UserGroup;

class EditUser extends Controller {

	private static $instance;
	
	public function initRoutes() {
		self::$instance = $this;
		//$authMiddleware =  self::requiresAuth('user.edit');

		$this->app()->get('/administration/user-list/edit/:userID', [$this, 'get'])->name('edit-user');
		$this->app()->post('/administration/user-list/edit/:userID', [$this, 'get']);
		$this->app()->get('/administration/user-list/edit/:userID/change-password', function ($userID) {
			$this->app()->redirectTo('edit-user', ['userID' => $userID]);
			
		})->name('edit-user/change-password');
		
		$this->app()->post('/administration/user-list/edit/:userID/change-password', [$this, 'changePassword']);
		$this->app()->get('/administration/user-list/delete/:userID', [$this, 'delete'])->name('delete-user');
		
// 		self::registerPermission('user.manage', 'Allows user access to user list, Required for all user based permissions.');
// 		self::registerPermission('user.edit', 	'Allows user to edit other users accounts.');
// 		self::registerPermission('user.delete', 'Allows user to delete other users accounts.');
	}
	
	public function get($userID, $renderParams = array(), $handlePostData = true) {
		
		$user = Sentinel::findUserById($userID);
		if(!$user) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not locate user in database, They may have been deleted or the wrong user id was given. Contact developer if issues persist.';
				
			//Sets window.location.href - Prevents this route been called on Refresh
			$renderParams['httpLocation'] 	= $this->app()->urlFor('user-list');
				
			UserList::getInstance()->get($this->app(), $renderParams);
			return;
		}
		
		if($handlePostData && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$data = $this->saveUser($user, $renderParams);
			$renderParams = array_merge($renderParams, $data);
			
			//Reselect User to update Role Changes
			$user = Sentinel::findUserById($userID);
		}

		$breadcrumbs = array();
		
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('user-list'),
				'name'	=> 'Users'
		];
		
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('edit-user', ['userID' => $userID]),
				'name'	=> $user->getFullOrUsername()
		];
		
		//User Info Form
		$formUserInfo = array();
		
		$formUserInfo['section-email'] = [
				'email'		=> [
						'name'		=> 'input-email',
						'type'		=> 'text',
						'label'		=> 'Email',
						'value'		=> $user->email,
						'htmlAfter'	=> '</br>'
				]
		];
		
		
		$formUserInfo['section-info'] = [
				'first_name'	=> [
						'name'		=> 'input-firstname',
						'type'		=> 'text',
						'label'		=> 'First Name',
						'value'		=> $user->first_name
				],
				'last_name'		=> [
						'name'		=> 'input-lastname',
						'type'		=> 'text',
						'label'		=> 'Last Name',
						'value'		=> $user->last_name,
						'htmlAfter'	=> '</br>'
				]
		];

		$formUserInfo['section-groups'] = array();
		
		$userRoles = array();
		foreach($user->roles as $role) {
			$userRoles[] = $role->slug;
		}
		
		$roles = UserGroup::get();
		foreach($roles as $role) {
			$formUserInfo['section-groups'][$role->slug] = [
					'name'		=> "groups[$role->id]",
					'type'		=> "checkbox",
					'label'		=> $role->name,
					'selected'	=> in_array($role->slug, $userRoles)
					
			];
			
			//Insert HTML Before
			if($roles[0] == $role) {
				$formUserInfo['section-groups'][$role->slug]['htmlBefore'] = "<h4 style='padding-left: 10px'>User Groups<br><small>Select which roles you wish this user to have access too</small></h4><div class='seperator'></div>";
			}
		}
		

		$renderParams['formUserInfo'] = [
				'sections'	=> $formUserInfo,
				'action'	=> $this->app()->urlFor('edit-user', ['userID' => $userID]),
				'method'	=> 'post',
				'submit'	=> 'Save',
				'submitInFooter'	=> true
		];
		
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
				'action'	=> $this->app()->urlFor('edit-user/change-password', ['userID' => $userID]),
				'method'	=> 'post',
				'submit'	=> 'Change Password',
				'submitInFooter'	=> true
		];
		
		//Page Title
		$renderParams['title'] = 'Edit User | ' . $user->getFullOrUsername();
		
		$renderParams = array_merge($renderParams, [
				'header'		=> 'Edit User',
				'active'		=> 'z_users',
				'breadcrumbs' 	=> $breadcrumbs,
				'editUser'		=> $user
		
		]);
		

		$this->app()->render('administration/users/edit_user.twig', $renderParams);
	}
	
	public function saveUser($user, $renderParams = array()) {
		
		//Validation
		$v = new Violin();

		$v->addFieldMessages([
				'input-email'			=> [
						'required'	=> 'Email field cannot be empty.',
						'email'		=> 'Please provide a valid email.',
						'max'		=> 'Email cannot be larger than 255 characters due to database restrictions.'
				],
				'input-firstname'	=> [
						'max'	=> 'First Name cannot be larger than 255 characters due to database restrictions.'
				],
				'input-lastname'	=> [
						'max'	=> 'Last Name cannot be larger than 255 characters due to database restrictions.'
				]
		]);
		
		$v->validate([
				'input-email'			=> [$_POST['input-email'], 		'required|email|max(255)'],
				'input-firstname'		=> [$_POST['input-firstname'],	'max(255)'],
				'input-lastname'		=> [$_POST['input-lastname'],	'max(255)']
		]);
		
		//If Validator fails
		if($v->fails()) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not update user for the following reasons:<br><ol><li>' . join("</li><li>", $v->errors()->all()) . '</li></ol>';

			return $renderParams;
		}
		
		
		//If email has been changed
		if(strcasecmp($user->email, $_POST['input-email']) != 0) {
			$credentials = [
					'email' => $_POST['input-email'],
			];
			
			//If Email belongs to another user
			if( Sentinel::findByCredentials($credentials) ) {
				$renderParams['calloutType'] = 'callout-danger';
				$renderParams['calloutMessage']	= 'Could not update user email because a user by the email address already exists.';
				
				return $renderParams;
			} 
		}
		
		//Save Input to User
		$user->email = $_POST['input-email'];
		if (!empty($_POST['input-firstname'])) $user->first_name = $_POST['input-firstname']; 
			else $user->first_name = null;
		
		if (!empty($_POST['input-lastname'])) $user->last_name = $_POST['input-lastname']; 
			else $user->last_name = null;
		
		$user->save();
		
		//Detach Roles
		foreach($user->roles as $role) {
			$role->users()->detach($user);
		}
		
		//Attach Roles
		if(isset($_POST['groups'])) {
			foreach($_POST['groups'] as $roleID => $state) {
				if($state !== 'on') {
					continue;
				}
				
				$role = Sentinel::findRoleById($roleID);
				$role->users()->attach($user);
			}
		}
		
		$renderParams['calloutType'] = 'callout-success';
		$renderParams['calloutMessage']	= 'User details have been successfully updated';
		return $renderParams;
	}
	
	public function changePassword($userID, $renderParams = array()) {
		$user = Sentinel::findUserById($userID);
		if(!$user) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not locate user in database, They may have been deleted or the wrong user id was given. Contact developer if issues persist.';
				
			//Sets Displayed URL - Prevents this route been called on Refresh
			$renderParams['httpLocation'] 	= $this->app()->urlFor('user-list');
				
			UserList::getInstance()->get($renderParams);
		}
		
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

			$this->get($userID, $renderParams, false);
			return;
		}
		
		//Sentinel Update User Password
		Sentinel::update($user, ['password' => $_POST['input-password']]);
		
		$renderParams['calloutType'] = 'callout-success';
		$renderParams['calloutMessage']	= 'User password has been successfully changed.';
		
		$this->get($userID, $renderParams, false);
	}


	public function delete($userID, $renderParams = array()) {
	
		//Sets Displayed URL - Prevents this route been called on Refresh
		$renderParams['httpLocation'] 	= $this->app()->urlFor('user-list');
	
		$user = Sentinel::findUserById($userID);
		if(!$user) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not locate user in database, They may have been deleted or the wrong user id was given. Contact developer if issues persist.';
				
			UserList::getInstance()->get($renderParams);
			return;
		}
	
		//Prevent Deletion of Root User
		if($user->root_user) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Access Violation, You cannot delete the root user.';
				
			UserList::getInstance()->get($renderParams);
			return;
		}
	
		//Delete User
		$user->delete();
	
		$renderParams['calloutType'] = 'callout-info';
		$renderParams['calloutMessage']	= 'User \'' . $user->getFullOrUsername() . '\' has been deleted.';
	
		UserList::getInstance()->get($renderParams);
	}
	
	public static function getInstance() {
		return self::$instance;
	}
}
