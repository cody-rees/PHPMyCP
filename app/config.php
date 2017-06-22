<?php 

$config = [
		
		//NOTE! Set this value to "true" after installation of the webserver to prevent mass database loss!
		'preventInstallation' => false,
		'defaultEmail'		=> 'admin@localhost.com',
		'defaultPassword'	=> 'admin',
		
		
		//URL and Protocol configuration
		'protocol'	=> stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://',
		'host' 		=> $_SERVER['HTTP_HOST'],
		'path' 		=> '/PhpMyCP/public/',
	
		//Database Credentials (See Laravel Capsule Documentation for additional configuration options)
		'dbConnection'	=> [
				'driver'    => 'mysql',
				'host'      => 'localhost',
				'database'  => 'phpmycp',
				'username'  => 'root',
				'password'  => '',
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
		],
		
];