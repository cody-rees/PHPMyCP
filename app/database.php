<?php

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

use Cartalyst\Sentinel\Native\Facades\Sentinel;

//Create new Database Connection
$capsule = new Capsule;
$capsule->addConnection($config['dbConnection']);

// Set the event dispatcher used by Eloquent models...
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

//Set Sentinel User Model
Sentinel::setModel(User::class);

