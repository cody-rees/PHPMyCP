<?php
namespace PhpMyCP\Controller\Administration;

use Illuminate\Database\Capsule\Manager;
use PhpMyCP\System\Controller;
use PhpMyCP\System\Navigation\Dropdown;
use PhpMyCP\Model\User;

class UserList extends Controller {

	private $title = "Listing Users";
	private static $instance;
	
	public function initRoutes() {
		self::$instance = $this;
		$this->app()->get('/administration/user-list', [$this, "get"])->name('user-list');
		
		//self::registerPermission('user.manage', 'Allows user access to user list, Required for all user based permissions.');
	}
	
	public function onNavigation() {
		$dropdown = new Dropdown('z_users',  ['user', 'Users']);
		$dropdown->add("user-list", 'Users List', 'user-list');
		$dropdown->add('create-user', 'Register New User', 'create-user');
		
		$this->navigation()->adminCategory->addDropdown($dropdown);
	}
	
	public function get($renderParams = array()) {
		
		//Search Defaults
		$page = 1;
		$search = null;
		$orderBy = 'id';
		
		$data = array();
		if(isset($_GET['page'])) {
			$page = $_GET['page'];
		}
		
		if(isset($_GET['search']) && !empty($_GET['search'])) {
			$search = $_GET['search'];
			$data['search'] = $search; 
		}
		
		if(isset($_GET['order'])) {
			$orderBy = $_GET['order'];
			$data['order'] = $orderBy;
		}
		
		$userList = $this->getUserList($page, $search, $orderBy); //$page, $search, $orderBy
		
		//Previous Page URL
		if($userList['page'] > 1) {
			$data['page'] = $userList['page']-1; 	
			$renderParams['previousPageURL'] = $this->app()->urlFor('user-list') 
				. '?' . http_build_query($data); 
		}

		//Next Page URL
		if($userList['page'] < $userList['pageTotal']) {
			$data['page'] = $userList['page']+1;
			$renderParams['nextPageURL'] = $this->app()->urlFor('user-list')
				. '?' . http_build_query($data);
		}
		
		$breadcrumbs = array();
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('user-list'),
				'name'	=> 'Users'
		];
	
		$renderParams = array_merge($renderParams, [
				'header'		=> 'Users List',
				'subheader'		=> 'View a list of currently registered users and details',
				'title' 		=> $this->title,
				'active'		=> 'z_users',
				'breadcrumbs' 	=> $breadcrumbs,
				'userList'		=> $userList,
				'search'		=> $search
	
		]);

		$renderParams['action'] = $this->app()->urlFor('user-list');
		$this->app()->render('administration/users/user_list.twig', $renderParams);
	}
	
	public function getUserList($page = 1, $search = null, $orderBy = 'id', $limit = 20) {

		$users;
		
		if($search && !empty($search)) {
			//Escape Search
			$search = str_replace("'", "\\'", $search);
			
			//Full Name Column
			$fullName = Manager::raw("CONCAT(`first_name`, CONCAT(' ', `last_name`))");
			
			//Where
			$users = User::where('email', 'LIKE', "%$search%")
				->orWhere($fullName, 'LIKE', "%$search%");
		
			//Order By
			$orderBy = Manager::raw(
				"GROUP BY `id`
					CASE WHEN `email` LIKE '$search' THEN 0
						WHEN `$fullName` LIKE  '$search' THEN 0
						WHEN `first_name` LIKE '$search' THEN 1
						WHEN `last_name` LIKE '$search' THEN 1
						WHEN `email` LIKE '$search%' THEN 2
						WHEN `$fullName` LIKE '$search%' THEN 2
						WHEN `first_name` LIKE '$search%' THEN 3
						WHEN `last_name` LIKE '$search%' THEN 3
						WHEN `email` LIKE '%$search' THEN 4
						WHEN `$fullName` LIKE '%$search%' THEN 4
						WHEN `first_name` LIKE '%$search%' THEN 5
						WHEN `last_name LIKE '%$search%' THEN 5
						WHEN `email` LIKE '%$search%' THEN 6
						WHEN `$fullName` LIKE '%$search%' THEN 6
						WHEN `first_name` LIKE '%$search%' THEN 7
						WHEN `last_name LIKE '%$search%' THEN 7
						ELSE 8
					END,
				`id`");
		 
			$users->raw($orderBy);
		}
		else {
			$users = User::orderBy($orderBy);
		}
		
		if($page < 1) {
			$page = 1;
		}
		
		$userTotal = $users->count();
		$pageTotal = ceil($userTotal / $limit);
		
		$offset = ($page-1) * $limit;
		if($offset >= $userTotal) {
			$offset = ($pageTotal-1) * $limit;
			$page = $pageTotal;
		}

		$users->offset($offset)->limit($limit);
		
		
		$results = [
				'userTotal'		=> $userTotal,
				'page'			=> $page,
				'pageTotal'		=> $pageTotal,
				'users'			=> $users->get(),
				'offset'		=> $offset,
				'limit'			=> $limit
		];
		
		return $results;
	}
	
	public static function getInstance() {
		return self::$instance;
	}
	
}