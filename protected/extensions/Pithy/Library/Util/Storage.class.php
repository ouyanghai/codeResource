<?php
    // +----------------------------------------------------------------------
    // | PithyPHP [ 精练PHP ]
    // +----------------------------------------------------------------------
    // | Copyright (c) 2010 http://pithy.cn All rights reserved.
    // +----------------------------------------------------------------------
    // | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
    // +----------------------------------------------------------------------
    // | Author: jenvan <jenvan@pithy.cn>
    // +----------------------------------------------------------------------


    /**
    +------------------------------------------------------------------------------
    * 数据存储类
    * 支持 File 和 Memcache 方式的存储，支持绝大部分数据类型的存储、亦可存储成队列形式
    +------------------------------------------------------------------------------
    * @category   Pithy
    * @package  Util
    * @subpackage  Storage
    * @author    jenvan <jenvan@pithy.cn>
    * @version   $Id$
    +------------------------------------------------------------------------------
    */ 
    class Storage{    

        static protected $_instance=array();       

        private $className;
        private $options=array();        
        private $driver=null;
        private $basePath="./data",$codeType="serialize",$delimiter="\r\n",$startCode="<?php exit;?>\r\n";

        public function __construct($options="Memcache"){  
            
            if(is_string($options) && !empty($options))
                $type=$options;
            elseif(is_array($options) && isset($options["type"]) && !empty($options["type"]))
                $type=$options["type"];
            else
                trigger_error("Parameters error!",E_USER_ERROR);             
            
            // 获取配置，共有三种配置(优先级从前到后) 1.参数配置 2.配置文件中别名配置 3.配置文件中默认配置 
            
            // 获取配置文件中的所有配置
            $_options=Pithy::config("Storage");
            
            if(isset($_options[$type]) && is_array($_options[$type]) && !empty($_options[$type])){
                // 获取配置文件中的指定配置
                $this->options=$_options[$type];
                
                // 别名配置覆盖默认配置
                if(isset($this->options["type"]) && is_string($this->options["type"])){
                    $type=strtolower($this->options["type"]); 
                    if(isset($_options[$type]) && is_array($_options[$type]) && !empty($_options[$type]))
                        $this->options=array_merge($_options[$type],$this->options);
                    else
                        return trigger_error("Storage type ($type) not exists!",E_USER_ERROR); 
                }
                // 参数配置覆盖别名配置
                if(is_array($options) && !empty($options))
                    $this->options=array_merge($this->options,$options);
            }
            else{
                return trigger_error("Storage default config not defined!",E_USER_ERROR);    
            }               
            
            // 判断类型是否支持
            $this->className=ucfirst(strtolower($type));
            if(!in_array(strtolower($this->className),array("filecache","filequeue","memcache","memcachedb","memcacheq"))){
                return trigger_error("Storage type ($type) not support!",E_USER_ERROR);  
            }
            
            // 实例化 1
            /*
            ksort($this->options);
            $identify=$this->className."-".md5(implode("",$this->options));
            if(!isset(self::$_instance[$identify])) { 
                if(in_array(strtolower($this->className),array("filecache","filequeue")))
                    self::$_instance[$identify] = $this; 
                if(in_array(strtolower($this->className),array("memcache","memcachedb","memcacheq")))
                    self::$_instance[$identify] = new Memcache;                  
            }
            $this->driver = self::$_instance[$identify];
            */
            
            // 实例化 2
            if(in_array(strtolower($this->className),array("filecache","filequeue")))
                $this->driver = $this; 
            if(in_array(strtolower($this->className),array("memcache","memcachedb","memcacheq")))
                $this->driver = new Memcache;             
            
            $this->connect(); 

            return $this->driver;
        }

        public function __call($method,$args){
            $object=$this->driver;
            
            if(strstr($this->className,"File")!="" && in_array($method,array("get","set","delete","add","replace","increment","inc","decrement","dec"))){
                $method="_".$method; 
            }
            
            if(!method_exists($object,$method)){
                return trigger_error("Method (".$this->className."::".$method.") not exists!",E_USER_ERROR);                
            }
            
            if(!empty($args) && is_array($args)){
                return call_user_func_array(array(&$object,$method),$args);                                    
            }
            else{
                return $object->$method();
            }
        }

        public function connect(){            
            if(strstr($this->className,"File")!=""){
                $this->basePath=(isset($this->options["path"]) && !empty($this->options["path"]))?$this->options["path"]:$this->basePath;                                
                $this->codeType=(isset($this->options["code"]) && !empty($this->options["code"]))?$this->options["code"]:$this->codeType;
                $this->delimiter=(isset($this->options["delimiter"]) && !empty($this->options["delimiter"]))?$this->options["delimiter"]:$this->delimiter;
            }
            if(strstr($this->className,"Memcache")!=""){
                $servers=explode(",",$this->options["server"]);          
                foreach($servers as $server){
                    list($host,$port)=explode(":",$server);
                    if(!$this->driver->addServer($host,$port)){
                        trigger_error("Can not connect to $server !",E_USER_ERROR);
                    } 
                }
            }
        } 


        
        
        
        /* 文件存储的方法*/
        
        // 获取
        private function _get($name){
            return $this->getData($name);
        }

        // 设置
        private function _set($name,$value){            
            return $this->setData($name,$value);
        }

        // 删除
        private function _delete($name){
            return $this->setData($name,null);            
        }

        // 新增
        private function _add($name,$value){
            return $this->setData($name,$value,true);
        }

        // 替换        
        private function _replace($name,$value){  
            return $this->setData($name,$value,false);          
        }

        // 递增
        private function _increment($name){
            $value=intval($object->get($name));
            $object->set($name,$value+1); 
            return $value+1; 
        }
        private function _inc($name){
            return $this->_increment($name);
        }

        // 递减        
        private function _decrement($name){
            $value=intval($object->get($name));
            $object->set($name,$value-1); 
            return $value-1;
        }
        private function _dec($name){
            return $this->_decrement($name);
        }         
        

        /* 文件存储的实现*/
        
        // 获取或设置类目 
        final public function category($name=""){
            if(empty($name))
                return (isset($this->options["category"]) && !empty($this->options["category"])) ? $this->options["category"] : "common";  
            $this->options["category"]=$name; 
        }        

        // 获取文件路径
        private function getPath($name){
            $category=$this->category();
            if(strstr($name,".")!="")
                list($category,$name)=explode(".",$name);            
            $filename=(in_array($category,array("task","cron"))?"":substr(strtolower($this->className),4).DIRECTORY_SEPARATOR).$category.DIRECTORY_SEPARATOR.$name.".php";
            $filepath=$this->basePath.DIRECTORY_SEPARATOR.$filename;
            return $filepath;
        }

        // 获取编码
        private function getCode($value,$flag=false){
            if($flag){
                if($this->codeType=="json")
                    $value=json_encode($value);
                if($this->codeType=="serialize")
                    $value=serialize($value);    
            }
            else{
                if($this->codeType=="json")
                    $value=json_decode($value);
                if($this->codeType=="serialize")
                    $value=unserialize($value);    
            }
            return $value;
        }  

        // 获取文件内容
        private function getData($name){
            if(substr($this->className,0,4)!="File")
                return null;

            $filepath=$this->getPath($name);
            if(!is_file($filepath))
                return null;

            $value=substr(@file_get_contents($filepath),strlen($this->startCode));

            if(strtolower($this->className)=="filecache"){
                ;   
            }

            if(strtolower($this->className)=="filequeue"){
                $arr=explode($this->delimiter,$value);
                $value=array_shift($arr);
                $this->setData($name,implode($this->delimiter,$arr),-1);
            }

            $value=$this->getCode($value);

            return $value;
        } 

        // 设置文件内容
        private function setData($name,$value,$state=""){        
            if(substr($this->className,0,4)!="File")
                return null;

            $filepath=$this->getPath($name);

            if(is_file($filepath)===$state)
                return null;    

            $folder=pathinfo($filepath,PATHINFO_DIRNAME);    
            if(!is_dir($folder)){
                if(!@mkdir($folder,0777,true)){
                    trigger_error("Can not create $folder ",E_USER_ERROR);
                    return null;
                }
            }

            if(is_null($value))
                return is_file($filepath) & @unlink($filepath);
             
            if(empty($value))
                return false;

            if($state==-1){
                $_value=$this->startCode.$value;
            }
            else{

                $_value=$this->getCode($value,true);

                if(strtolower($this->className)=="filecache"){ 
                    $flag=LOCK_EX;
                    $_value=$this->startCode.$_value;
                }

                if(strtolower($this->className)=="filequeue"){
                    if(is_file($filepath)){
                        $flag=FILE_APPEND;
                        $_value=$this->delimiter.$_value;    
                    }
                    else{
                        $flag=LOCK_EX;
                        $_value=$this->startCode.$_value;    
                    }
                } 

            } 

            return @file_put_contents($filepath,$_value,$flag)>0 ? $value : null;
        }
    }
?>
