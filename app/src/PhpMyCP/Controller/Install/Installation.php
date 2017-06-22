<?php 

namespace PhpMyCP\Controller\Install;

use PhpMyCP\System\Controller;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\Model\User;
use Illuminate\Database\Capsule\Manager;

class Installation extends Controller {
	
	private $debugMode = true;
	
	public function initRoutes() {
		if($this->config()['preventInstallation']) {
			die("Reinstallation of this site, not allowed. Check your configuration.");
		}
		
		$this->app()->get('/install', [$this, 'install']);
	}
	
	public function install() {
		//Gets Database name from Capsule
		$dbName = Manager::connection()->getDatabaseName();
		
		//Clear Database if running in Production Mode
		if($this->debugMode) {
			Manager::connection()->getPdo()->exec("DROP DATABASE IF EXISTS $dbName");
			Manager::connection()->getPdo()->exec("CREATE DATABASE $dbName");
			Manager::connection()->getPdo()->exec("USE $dbName");
		}
		
		//Sentinel Installation
		$this->installFromSQLFile("sentinel.sql");
		
		//Call installation routes on Models
		foreach($this->models() as $model) {
			if(is_callable([$model, 'install'])) {
				$model->install();
			}
		}
		
		//Install Default User
		$this->installDefaultUser([
				'email' 		=> $this->config()['defaultEmail'],
				'password' 		=> $this->config()['defaultPassword'],
				'root_user'		=> 1
		]);
		
		echo "Installation Complete!";
	}
	
	private function installDefaultUser($credentials) {
		
		//Check Credentials
		if(!Sentinel::validForCreation( $credentials )) {
			die ( "Could not install Default User, Invalid Default Credentials." );
		}
		
		//Check If Root User already exists
		if (User::where('root_user', 1)->first()) {
			die ( "Could not install Root User, Root user already exists." );
		}
		
		//Create Administrator Role
		$administrator = Sentinel::getRoleRepository()->createModel()->create([
				'name'			=> 'Administrator',
				'slug'			=> 'administrator',
				'permissions'	=> [
						'*'	=> true
				]
		]);
		
		//Create & Activate User
		$user = Sentinel::create ( $credentials );
		
		$activation = Sentinel::getActivationRepository()->create($user);
		$activation->completed = true;
		$activation->completed_at = 'CURRENT_TIMESTAMP';
		$activation->save();
		
		//Add Administrator Role to User
		$administrator->users()->attach($user);
		
	}
	
}