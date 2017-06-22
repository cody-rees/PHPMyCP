<?php
namespace PhpMyCP\Controller\Administration;

use PhpMyCP\System\Controller;
use PhpMyCP\System\Navigation\Dropdown;
use PhpMyCP\Controller\User\UserGroup;

class GroupsList extends Controller {

	private $title = "Listing User Groups";
	
	public function initRoutes() {
		$this->app()->get('/administration/group-list', [$this, "get"])->name('group-list');
		//self::registerPermission('group.manage', 'Allows user access to groups list, Required for all group based permissions.');
	}
	
	public function onNavigation() {
		$dropdown = new Dropdown('zz_groups',  ['users', 'User Groups']);
		$dropdown->add('groups-list', 'Groups List', 'group-list');
		$dropdown->add('create-group', 'Create Group', '#', false);
		
		$this->navigation()->adminCategory->addDropdown($dropdown);
	}
	
	public function get($renderParams = array()) {
		$breadcrumbs = array();
	
		$breadcrumbs[] = [
				'url'	=> $this->app()->urlFor('group-list'),
				'name'	=> 'User Groups'
		];
	
	
		$renderParams = array_merge($renderParams, [
				'header'		=> 'User Groups List',
				'subheader'		=> 'View a list of currently registered user groups.',
				'title' 		=> $this->title,
				'active'		=> 'zz_groups',
				'breadcrumbs' 	=> $breadcrumbs,
				'groups' 		=> UserGroup::all()
	
		]);
	
		$this->app()->render('administration/groups/groups_list.twig', $renderParams);
	}
	
}