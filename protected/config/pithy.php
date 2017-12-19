<?php
$_root = dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR;

return CMap::mergeArray(
    array(
        // App设置
        'APP'=>array(
            'DEBUG'             => false,   // 是否开启调试模式    

            'ERROR_LOG'         => true,    // 是否开启错误记录
            'ERROR_TRACE'       => true,    // 是否开启错误跟踪
            'ERROR_DISPLAY'     => true,    // 是否开启错误显示
            'ERROR_MESSAGE'     => "系统出错，请稍后再试！",           // 错误显示信息

            'LOG_LEVEL'         => array("ALERT","ERROR","WARNING"),    // 开启记录等级  true,false,array()
            'LOG_FILE_SIZE'     => 100*1024*1024,                       // 记录文件最大字节数

            'AUTOLOAD'          => true,    // 是否开启SPL_AUTOLOAD_REGISTER
            'AUTOLOAD_PATH'     => '',      // __autoLoad 机制额外检测路径设置,注意搜索顺序 
            'TIMEZONE'          =>'PRC',    //设置时区
        ),
        
        
        // Cookie设置
        'COOKIE'=>array(
            'EXPIRE'         => 3600,    // Coodie有效期
            'DOMAIN'         => '',      // Cookie有效域名
            'PATH'           => '/',     // Cookie路径
            'PREFIX'         => '',      // Cookie前缀 避免冲突
        ),
        
        // 存储设置
        "Storage" => array(
            // 五种存储类型的默认配置
            "FileCache"=>array(
                "path"=>$_root."runtime/data",
                "code"=>"serialize",
            ),
            "FileQueue"=>array(
                "path"=>$_root."runtime/data",
                "code"=>"json",
                "delimiter"=>"\r\n",
            ),
            "Memcache"=>array(
                "server"=>"chche:11211",
            ),
            "MemcacheDB"=>array(
                "server"=>"cache:11212",
            ),
            "MemcacheQ"=>array(
                "server"=>"cache:11213",
            ),
            // 自定义配置和别名配置
            "test"=>array(
                "type"=>"FileCache",
                "code"=>"json",
            ),
            "cache"=>array(
                "type"=>"MemcacheDB",
            ),
            "queue"=>array(
                "type"=>"MemcacheQ",
            ),
        ),
    ),
    
    require($_root."private/config/pithy.php")
);
