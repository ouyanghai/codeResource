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
    * 定时任务类
    * 本类依赖于 Task类  Storage类
    +------------------------------------------------------------------------------
    * @category   Pithy
    * @package  Util
    * @subpackage  Cron
    * @author    jenvan <jenvan@pithy.cn>
    * @version   $Id$
    +------------------------------------------------------------------------------
    */ 
    class Cron extends Task {    

        private $_name="default";
        private $_options=array();

        protected $storage=null;
        protected $storageType="FileCache";

        private $interval=10;
        private $parsed=false;
        private $data=array();

        // 获取
        public function get($key){
            $map=$this->cache->get($this->getName($key));
            if(empty($map) || !is_array($map))
                return null;               

            if(empty($key))
                return $map;

            if(isset($map[$key]))                   
                return $map[$key];

            return null;    
        }

        // 设置
        public function set($key,$value){
            $map=$this->get();
            if(empty($map))
                $map=array();

            if(empty($key) || !is_string($key))
                return false;

            if(empty($value)){
                if(isset($map[$key]))
                    unset($map[$key]);    
            }                
            else{
                if(strstr($value," ")<>"" && count(explode(" ",$value))>=5)
                    $map[$key]=$value;    
            } 

            return $this->cache->set($this->getName($key),$map);
        }

        // 新增（如果存在则放弃）
        public function add($key,$value){
            if(is_null($this->get($key)))
                return $this->set($key,$value);
            return false;
        }

        // 替换（如果不存在则放弃）        
        public function replace($key,$value){        
            if(!is_null($this->get($key)))
                return $this->set($key,$value);
            return false;  
        }

        // 删除
        public function remove($key){
            return $this->replace($key,null);
        }

        // 解析(只负责解析当天的)
        public function parse(){            
            $map=$this->get();
            if(empty($map))
                return array(); 

            $week=date("w");
            $month=date("n");
            $day=date("j");             

            $data=array();
            foreach($map as $k=>$v){
                $arr=explode(" ",$v);
                list($a,$b,$c,$d,$e,$cmd)=$arr;    
                $params=count($arr)<=6?"":implode(" ",array_slice($arr,6));

                $weeks=$this->getRange($e,0,6);
                $months=$this->getRange($d,1,12);
                $days=$this->getRange($c,1,31); 
                if( in_array($week,$weeks) || (in_array($month,$months) && in_array($day,$days)) ){
                    $hours=$this->getRange($b,0,23);
                    $miutues=$this->getRange($a,0,59); 

                    foreach($hours as $hour){
                        foreach($miutues as $minute){
                            $item=$this->getKey($hour,$minute);
                            if(!isset($data[$item]))                            
                                $data[$item]=array();
                            $data[$item][]=trim($cmd." ".$params);
                        }
                    }   
                } 
            }
            $this->data=$data;

            $this->parsed=true;            
            return $this->data;    
        }

        // 执行
        public function run(){

            // 如果尚未解析
            if($this->parsed==false)
                $this->parse();

            // 每天 00:00:00 重新执行一次 parse 操作            
            if(0)
                $this->parse();    

            // 执行操作
            $data=$this->data;
            $item=$this->getKey($data("H"),$data("i"));
            if(isset($data[$item]) && !empty($data[$item])){
                $arr=$data[$item];
                foreach($arr as $v){
                    $this->execute($v);    
                }
            }
        }
        
        

        // 通过给定的字符串表达式和范围，获取符合条件的值 getRange("1,9-11,*/5,27/3,14-28/2,59",0,59)
        private function getRange($str,$min,$max){       

            $data=array();

            $arr=explode(",",$str);
            foreach($arr as $v){
                if($v=="*"){
                    $data=range($min,$max); 
                    break;
                }
                if(is_numeric($v)){
                    $data[]=$v;
                    continue;
                }
                if(strstr($v,"/")==""){
                    if(strstr($v,"-")<>""){
                        list($x,$y)=explode("-",$v);
                        $data=array_merge($data,range($x,$y));
                        continue;
                    }    
                }
                else{
                    $temp=array();

                    list($a,$b)=explode("/",$v);
                    if($a=="*"){
                        $temp=range($min,$max);                     
                    }
                    if(is_numeric($a)){
                        $temp=range($a,$max);                        
                    }
                    if(strstr($a,"-")<>""){
                        list($x,$y)=explode("-",$a);
                        $temp=range($x,$y);    
                    }                   

                    if(!empty($temp))
                        $temp=array_filter($temp,create_function('$v','return ($v%'.$b.'==0);'));
                    $data=array_merge($data,$temp);

                    continue;
                }                    
            }

            $data=array_unique($data);
            $data=array_filter($data,create_function('$v','return ($v>='.$min.' && $v<='.$max.');'));
            sort($data);

            return array_values($data);    
        }

        // 获取 cron 数组列表的键值
        private function getKey($hour,$minute){
            return $this->cronName."-".str_pad($hour,2,"0",STR_PAD_LEFT).str_pad(floor($minute/$this->interval),2,"0",STR_PAD_LEFT);    
        }
    }
?>