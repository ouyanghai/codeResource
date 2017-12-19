<?php
    Class ShortUrl {         
        
        static public $prefix = "http://i.haodianpu.com/go/";
        
        static protected $white_lists = array("haodianpu.com", "taobao.com");
        
        static protected $cache_server = "cache";
        static protected $cache_prefix = "short_url_";
        
        /**
         * 设置短网址
         * 
         * @param mixed $long
         * @param mixed $short
         * 
         * @return 
         */
        static public function set($long, $short=""){
        
            if( empty($long) || strlen($long) <= 10 || (substr($long, 0, 7) != "http://" && substr($long, 0, 8) != "https://") )
                return 0;  
                
            if( !empty($short) && !preg_match("/^[a-z0-9]{2,}$/") )
                return 0;
            
            $arr = parse_url($long);
            
            $host = $arr["host"]; 
            if( !preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches) )
                return 0;
                
            $domain = $matches[0];
            if( !in_array($domain, self::$white_lists) )
                return 0;
            
            $pos = empty($short) ? substr(md5($long), 0, 2) : substr($short, 0, 2);
            $name = self::$cache_prefix.$pos;  
            
            $cache = new Storage(self::$cache_server);
            $data = $cache->get($name);            

            $short = empty($short) ? $pos.base_convert(count($data), 10, 36) : $short;
            if( isset($data[$short]) ){
                $obj = json_decode($data[$short]);
                if( is_array($obj) && isset($obj->url) )
                    return $obj->url == $long ? self::$prefix.$short : "";
            }                      
                
            $data[$short] = json_encode(array("url"=>$long));
            $cache->set($name, $data);
            
            return self::$prefix.$short; 
        }
        
        
        /**
         * 获取短网址对应的长网址
         * 
         * @param mixed $short
         * @return mixed
         */
        static public function get($short){
            
            if( !empty($short) && !preg_match("/^[a-z0-9]{2,}$/", $short) )
                return 0;
                
            $pos = substr($short, 0, 2);
            $name = self::$cache_prefix.$pos;      
            
            $cache = new Storage(self::$cache_server);
            $data = $cache->get($name);
            
            if( !isset($data[$short]) )
                return "";
                
            $obj = json_decode($data[$short]);
            if( !is_object($obj) || !isset($obj->url) )
                return "";
    
            return $obj->url;                   
        } 

    } 
?>
