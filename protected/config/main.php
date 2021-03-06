<?php

$_root_=dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return CMap::mergeArray(
	array(
	"basePath"=>$_root_."protected",
	"language"=>"zh_cn",
	'name'=>'zhaoqiye',
	"timezone"=>"Asia/Shanghai",
	"runtimePath"=>$_root_."runtime",
	// preloading 'log' component
	'preload'=>array('log', 'PithyLoader'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),
	'defaultController'=>'web',
	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'PithyLoader'=>array(
			'class'=>'application.extensions.PithyLoader',
		),
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
			'loginUrl' => '/admin/loginPage',
		),
		// uncomment the following to enable URLs in path-format
	
		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName'=>false,
			'urlSuffix'=>'.html',
			'rules'=>array(
				//'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		
		/*
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		*/
		// uncomment the following to use a MySQL database
		
		'db'=>array(
			'connectionString' => 'mysql:host=mysql;dbname=b2b',
			'emulatePrepare' => true,
			'username' => 'admin',
			'password' => 'admin',
			'charset' => 'utf8',
			'tablePrefix' => 'phone_',
		),
		
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
					"logPath"=>$_root_."runtime/log",
                    "maxFileSize"=>5*1024,
                    "maxLogFiles"=>100,
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		"pithy" => require(dirname(__FILE__).DIRECTORY_SEPARATOR."pithy.php"),
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
	),
	require($_root_."private/config/main.php")
);