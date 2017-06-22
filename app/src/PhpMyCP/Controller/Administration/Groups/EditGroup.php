<?php 

namespace PhpMyCP\Controller\Administration;

use PhpMyCP\System\Controller;

class EditGroup extends Controller {
	
	public function initRoutes() {
		
	}
	
	public function get() {
		$form['section-name'] = [
				'group-name'	=> [
						'name'		=> 'input-name',
						'label'		=> 'Group Name'
				],
				'slug' => [
						'name'		=> 'input-slug',
						'lable'		=> 'slug'
				]
		
		];
		
		$permissions = [
				'example.node',
				'example.node2',
				'example.node3',
				'example.node4',
				'example.node5',
				'example.node6',
				'example.node7',
				'example.node8',
				'example.node9',
		];
		
		$form['section-permissions'] = array();
		foreach($permissions as $perm) {
			$form['section-permissions'][$perm] = [
				'name'		=> 'permissions[]',
				'label' 	=> 'Access Permissions',
				'type'		=> 'checkbox',
				'value'		=> $perm
			];
			
		}
		
		
	}
	
}