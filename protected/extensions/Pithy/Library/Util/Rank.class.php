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
    * 排行榜类
    * 本类依赖于 Storage类
    +------------------------------------------------------------------------------
    * @category		Pithy
    * @package		Util
    * @subpackage	Rank
    * @author		jenvan <jenvan@pithy.cn>
    * @version		$Id$
	*
	*
	* 设计原理：所有排行榜的配置存一个缓存；每个排行榜单独一个缓存；每个排行榜的键值单独一个缓存；
	* 每次对键进行加或减操作，先会记录一条当前时间戳的记录，然后统计该键中有效记录的数量（即没有过
	* 期的时间戳数量），将其更新到排行榜缓存中对应键值的排行数。
	*
    +------------------------------------------------------------------------------
    */ 
    class Rank {    

        private static $_instance = null; 
        private $_rank = null;

        
        /**
        * 获取类的单例（实例化的对象）
        * 
        * @return 任务单例
        */
        public static function singleton($name){
			if( !is_array(self::$_instance) ){
				self::$_instance = array();
			}
            if( !isset(self::$_instance[$name]) || empty(self::$_instance[$name]) ){
                $class=__CLASS__;
                self::$_instance[$name] = new $class($name);    
            }                
            return self::$_instance[$name];    
        }
		
		/**
        * 获取所有排行榜信息
        * 
        * @param mixed $name
        */
        public static function list(){
            
        }         
        
        /**
        * 创建一个新排行榜
        * 
        * @param mixed $name
        * @param mixed $options=array($limit,$timeout) 可设置排行榜的榜单的键值上限、每个键的每个单位数据的过期时间
        */
        public static function create($name, $options=array(100,60*24*7)){
            
        } 
		
        /**
        * 删除一个排行榜
        * 
        * @param mixed $name
        */
        public static function remove($name){
            
        }
		
        /**
        * 判断一个排行榜是否存在
        * 
        * @param mixed $name
        */
        public static function exists($name){
            
        } 

        
        /**
        * 实例化类
        * 
		* @param mixed $name 排行榜名称
        */
        public function __construct($name){ 
			if( !self::exists($name) ){
				trigger_error("Rank {$name} not exists!", E_USER_ERROR);
			}                                       
			$this->_rank = $name;


        } 

		// 查询排行榜某一区间数据或者全部数据（以数组的键值对形式返回）
		public function query($range="-", $limit=0){
			list($min, $max) = explode("-", $range);

		}
        
		// 清空排行榜的数据
		public function clear(){

		} 
        
        // 查询排行榜中某元素的值及排名
        public function get($key){            
              
        } 

        // 向排行榜中添加或设置数据（支持数组方式批量添加）
        public function set($key, $value){            
               
        }
        // 向排行榜中添加数据（如果存在则不添加）
        public function add($key, $value){
            
        }        
        // 向排行榜中替换数据（如果不存在则替换）
        public function replace($key, $value){
            
        }        
        // 对排行榜中某个数据执行加法操作
        public function increase($key, $step=1){
            
        }        
        // 对排行榜中某个数据执行减法操作
        public function decrease($key, $step=1){
            
        }

		// 删除排行榜中指定key的数据
        public function delete($key){
            
        }

    } 

?> 