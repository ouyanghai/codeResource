<?php
return array(
'components'=>array(
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
			'connectionString' => 'mysql:host=mysql;dbname=zhaoqiye',
			'emulatePrepare' => true,
			'username' => 'debug',
			'password' => 'hdp@5C01',
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
);
?>