<?php
use Slim\Slim;
use Slim\Views\Twig;
use PhpMyCP\System\Controller;
use PhpMyCP\System\Model;
use PhpMyCP\System\System;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use PhpMyCP\Model\User;
use PhpMyCP\System\FormBuilder\FormBuilderExtension;
use PhpMyCP\Controller\User\UserGroup;

//Initialize Application Framework
$app = new Slim([
		'view' => new Twig(),
		'templates.path' => __DIR__ . '/src/views/'
]);

//Load Configuration
require '/config.php';

$controllers = array();
$models = array();

$ignoreClasses = [
		Controller::class,
		Model::class,
		System::class
];

//Autoload Libaries
foreach($autoloader->getClassMap() as $class => $path) {
	if(substr($class, 0, 7) !== "PhpMyCP") {
		continue;
	}
	
	//Ignore our own abstract classes (Easier and faster to hardcode abstract ignore)
	if(in_array($class, $ignoreClasses)) {
		continue;
	}
	
	//Class extends Controller class
	if(strcmp(get_parent_class($class), Controller::class) === 0) {
		$controllers[] = new $class;
		continue;
	}
	
	//Class extends Model class
	if(strcmp(get_parent_class($class), Model::class) === 0) {
		$models[] = $class;
		continue;
	}
	
	//Class extends System class and Autoload Property
	if(strcmp(get_parent_class($class), System::class) === 0) {
		if(property_exists($class, 'autoload')) {
			$instance = new $class;
			System::registerResource($instance->autoload, $instance);
		}
	}
	
}


//Register Default System Resources
System::registerResource('models', $models);
System::registerResource('controllers', $controllers);
System::registerResource('sentinel', Sentinel::instance());

System::registerResource("app", $app);
System::registerResource("appPath", realpath(__DIR__));
System::registerResource("config", $config);


//Initialize Database Connection and Build Schema
require 'database.php';


//Add global template data
$app->hook('slim.before', function() use ($app, $config) {
	$app->view()->appendData([
			'navigation'	=> System::navigation(),
			'user'			=> System::user(),
			'base' 			=> $app->request->getRootUri() . "/",
			'app' 			=> $app
	]);
});
	
// Build Routes
/* @var $controller Controller */
foreach($controllers as $controller) {
	$controller->initRoutes($app);
}

// Build Navigation
/* @var $controller Controller */
foreach($controllers as $controller) {
	$controller->onNavigation($app);
}

//Set PhpMyCP models instead of Sentinel
Sentinel::setModel(User::class);
Sentinel::getRoleRepository()->setModel(UserGroup::class);

//Sentinel setModel bug workaround
$eloquentUser = Sentinel::check();
if($eloquentUser) {
	System::registerResource('user', User::find($eloquentUser->id));

} else {
	System::registerResource('user', null);
	
}


/* @var $twig Twig_Enviroment */
$twig = $app->view()->parserExtensions = [
		new FormBuilderExtension()
		
];

//Runs Application to Handle Request
$app->run(); 

