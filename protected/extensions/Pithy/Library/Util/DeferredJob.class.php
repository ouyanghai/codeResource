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
    * 队列任务类
    * 本类依赖于 Storage类
    +------------------------------------------------------------------------------
    * @category   Pithy
    * @package  Util
    * @subpackage  Task
    * @author    jenvan <jenvan@pithy.cn>
    * @version   $Id$
    +------------------------------------------------------------------------------
    */ 
    class Task {    

        private static $_instance=null; 
        
        protected $cache=null;
        protected $cacheConfigName="system_task_cache";
        
        protected $queue=null;
        protected $queueConfigName="system_task_queue";
        
        protected $taskName=null;
        
        
        /**
        * 获取 任务 的单例（实例化的对象）
        * 
        * @return 任务单例
        */
        public static function singleton(){
            if(self::$_instance==null){
                $class=__CLASS__;
                self::$_instance=new $class();    
            }                
            return self::$_instance;    
        }

        
        /**
        * 实例化类
        * 
        */
        public function __construct(){ 

            $options=Pithy::config("Storage");
            if(!is_array($options) || empty($options) || !isset($options[$this->cacheConfigName],$options[$this->queueConfigName])){
                return trigger_error("Storage [Task] config not defined!",E_USER_ERROR);
            }
            
            $this->cache=new Storage($this->cacheConfigName);                                                           
            $this->queue=new Storage($this->queueConfigName);                                                           
        }
        
        
        /**
        * 设置全局参数
        * 
        * @param mixed $options
        */
        public function setup($options){
            
        } 
                 
        /**
        * 创建任务
        * 
        * @param string $name 任务队列名称
        * @param mixed $options 任务队列配置参数
        * @return boolean 任务是否创建成功
        */
        public function create($name,$options){
                    
        } 
        
        /**
        * 修改任务
        * 
        * @param string $name 任务队列名称
        * @param mixed $options 任务队列配置参数
        * @return boolean 任务是否修改成功
        */
        public function modify($name,$options){
                    
        } 

        /**
        * 删除任务
        * 
        * @param string $name 任务队列名称
        * @return boolean 任务是否删除成功
        */
        public function remove($name){
         
        }
        
        /**
        * 使用（更改）并返回任务队列
        * 
        * @param string $name 任务队列名称
        * @return 返回当前对象，以便执行连贯操作。如： this->change("test")->get();
        */
        public function change($name){        
            $this->taskName="system_task_".$name;
            return $this;    
        } 
        
 
        
        // 获取
        public function get(){            
            return $this->queue->get($this->taskName);   
        } 

        // 设置
        public function set($value){            
            return $this->queue->set($this->taskName,$value);        
        }
        
        public function add($value){
            
        }
        
        //
        public function replace($value){
            
        }

        public function delete(){
            
        }

        // 执行
        public function execute($cmd,$asyn=true){             

            //$cmd=preg_replace("/(#[^#]+)#/",$this->storage->get("\\1"),$cmd);

            if(substr($cmd,0,7)=="http://")
                return @file_get_contents($cmd);

            if(substr($cmd,0,4)=="php:")
                $cmd=$this->_options["bin_php"]." ".substr($cmd,4);

            if(substr($cmd,0,6)=="mysql:")
                $cmd=$this->_options["bin_mysql"]." ".substr($cmd,6);

            if(!$asyn){
                passthru($cmd,$rtn);
                return $rtn;
            }
            return pclose(popen($cmd,"r"));    
        }

    } 

?> 