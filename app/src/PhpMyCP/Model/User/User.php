<?php 

namespace PhpMyCP\Model;

use Cartalyst\Sentinel\Users\EloquentUser;

/**
 *
 * @property integer id;
 * @property string email;
 * @property string password,
 * @property string last_name,
 * @property string first_name,
 * @property integer root_user
 *
 */
class User extends EloquentUser {
	
	// Override user fillable fields
	protected $fillable = [
			'email',
			'password',
			'last_name',
			'first_name',
			'permissions',
			'root_user'
	];
	
	public function getFullOrUsername() {
		if(!$this->first_name) {
			return $this->email;
		}
		
		if(!$this->last_name) {
			return $this->first_name;
		}
		
		return $this->first_name . ' ' . $this->last_name;
	}
	
}