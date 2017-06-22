<?php
namespace PhpMyCP\Controller\Administration;

use Cartalyst\Sentinel\Roles\EloquentRole;
use Violin\Violin;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\System\Controller;

class CreateUser extends Controller {
	
	private $title = 'Create User';

	public function initRoutes() {
		//$authMiddleware =  self::requiresAuth('user.edit');

		$this->app()->get('/administration/user-list/create', [$this, 'getForm'])->name('create-user');
		$this->app()->post('/administration/user-list/create', [$this, 'createUser']);
		
		//self::registerPermission('user.edit', 'Allows user to edit other user accounts.');

	}

	public function getForm($renderParams = array()) {

		$form = array();
		$form['section-login'] = [
				'email'	=> [
						'name'		=> 'input-email',
						'label'		=> 'Email(*)',
						'htmlAfter'	=> "</br></br>"
				]
				
		];
		
		$form['section-info'] = [
				'first-name' 	=> [
						'name'		=> 'input-firstname',
						'label' 	=> 'First Name',
						'type'		=> 'text'
				],
				'last-name'			=> [
						'name'		=> 'input-lastname',
						'label'		=> 'Last Name',
						'type'		=> 'text',
						'htmlAfter'	=> "</br>"
				]
		];
		
		$form['section-password'] = [
				'password'		=> [
						'name'		=> 'input-password',
						'type'		=> 'password',
						'label'		=> "Password(*)"
				],
				'password-confirm'	=> [
						'name'		=> 'input-password-confirm',
						'type'		=> 'password',
						'label'		=> 'Password Confirm(*)',
						'htmlAfter'	=> "</br>"
				]
		];
		

		$form['section-groups'] = array();
		
		$roles = EloquentRole::get();
		foreach($roles as $role) {
			$form['section-groups'][$role->slug] = [
					'name'		=> "groups[$role->id]",
					'type'		=> "checkbox",
					'label'		=> $role->name,
					'selected'	=> false
			];
		
			//Insert HTML Before
			if($roles[0] == $role) {
				$form['section-groups'][$role->slug]['htmlBefore'] = "<h4 style='padding-left: 10px'>User Groups<br><small>Select which roles you wish this user to have access too</small></h4><div class='seperator'></div>";
			}
			
		}
		
		
		$renderParams['formCreateUser'] = [
				'sections'			=> $form,
				'action'			=> $this->app()->urlFor('create-user'),
				'method'			=> 'post',
				'submit'			=> 'Register User',
				'submitInFooter'	=> true
		];
		
		
		//Breadcrumbs
		$breadcrumbs = array();
		
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('user-list'),
				'name'	=> 'Users'
		];
		
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('create-user'),
				'name'	=> 'Register User'
		];

		
		//Final Render params
		$renderParams = array_merge($renderParams, [
				'header'		=> 'Register User',
				'active'		=> 'z_users',
				'breadcrumbs' 	=> $breadcrumbs,
				'title'			=> $this->title
		
		]);
		
		
		$this->app()->render('administration/users/create_user.twig', $renderParams);
	}
	
	public function createUser($renderParams = array()) {
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
				],
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
				'input-email'				=> [$_POST['input-email'], 		'required|email|max(255)'],
				'input-firstname'			=> [$_POST['input-firstname'],	'max(255)'],
				'input-lastname'			=> [$_POST['input-lastname'],	'max(255)'],
				'input-password'			=> [$_POST['input-password'],	'required|min(8)|max(255)'],
				'input-password-confirm'	=> [$_POST['input-password-confirm'], 'required|matches(input-password)']
		]);
		
		//If Validator fails
		if($v->fails()) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not update user for the following reasons:<br><ol><li>' . join("</li><li>", $v->errors()->all()) . '</li></ol>';
		
			$renderParams['formData'] = [
					'input-email'		=> $_POST['input-email'],
					'input-firstname'	=> $_POST['input-firstname'],
					'input-lastname'	=> $_POST['input-lastname']
			];
			
			$this->getForm($renderParams);
			return;
		}
		
		$credentials = [
				'email' => $_POST['input-email'],
		];
			
		//If Email belongs to another user
		if( Sentinel::findByCredentials($credentials) ) {
			$renderParams['calloutType'] = 'callout-danger';
			$renderParams['calloutMessage']	= 'Could not create user because a user by the email address already exists.';

			$renderParams['formData'] = [
					'input-email'		=> $_POST['input-email'],
					'input-firstname'	=> $_POST['input-firstname'],
					'input-lastname'	=> $_POST['input-lastname']
			];
				
			$this->getForm($renderParams);
			return;
		}
		
		$user = Sentinel::create([
				'email'		=> $_POST['input-email'],
				'password'	=> $_POST['input-password']
		]);
	
		//Set first/last name if not empty
		if (!empty($_POST['input-firstname'])) 
			$user->first_name = $_POST['input-firstname'];
		
		if (!empty($_POST['input-lastname'])) 
			$user->last_name = $_POST['input-lastname'];
	
		$user->save();
		
		//Activate User
		$activation = Sentinel::getActivationRepository()->create($user);
		$activation->completed = true;
		$activation->completed_at = 'CURRENT_TIMESTAMP';
		$activation->save();
		
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
		
		$displayName = $user->getFullOrUsername();
		
		$renderParams['calloutType'] = 'callout-success';
		$renderParams['calloutMessage']	= "New User ($displayName) has been successfully registered." ;

		//Sets Displayed URL - Prevents this route been called on Refresh
		$renderParams['httpLocation'] 	= $this->app()->urlFor('edit-user', ['userID' => $user->id]);
		
		EditUser::getInstance()->get($user->id, $renderParams, false);
	}
}