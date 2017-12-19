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

    class Top { 
        
        public $output = false;
        public $traces = array();
        public $logs = array();
    
        protected $options;
        protected $account;
        
        // 基类初始化
        public function __construct(){  
            
            $this->options=self::config();
            $this->mode($this->options["mode"]);
            
            $args=func_get_args();           
            call_user_func_array(array($this,"init"),$args);                                              
        }
        
        // 初始化
        public function init(){

        } 
        
        // 获取配置信息
        static public function config($name=""){
            $name=strtolower($name);
            if($name=="modes"){
                $arr=array();
                $cfg=self::config();
                foreach($cfg as $k=>$v){
                    if(is_array($v))
                        $arr[$k]=$v;
                }
                return $arr;
            }
            $name=empty($name)?"":".".$name;
            return Pithy::config("TOP".$name);
        }      

        // 获取或设置应用模式
        final public function mode($name=""){
            if(empty($name))
                return $this->options["mode"];   
            $this->account=$this->options[$name]; 
        }
        
        // 获取当前应用的相关信息
        final public function profile($name=""){              
            // 剔除敏感字段
            $profile=$this->account;
            foreach(array("secret_code") as $k){
                unset($profile[$k]);    
            }                
            // 返回信息
            if(empty($name))
                return $profile;
            if(isset($profile[$name]))
                return $profile[$name];
            return null;            
        }  

        // 生成验证字串
        final protected function makeSign($params){
            ksort($params);
            $str = "";
            foreach($params as $k => $v) {
                if("@" != substr($v, 0, 1)){
                    $str .= "$k$v";
                }
            }
            unset($k, $v);

            $secret=$this->account["secret_code"];
            if($this->options["sign_method"]=="hmac")
                $sign = strtoupper(bin2hex(mhash(MHASH_MD5,$str,$secret)));
            else
                $sign = strtoupper(md5($secret.$str.$secret));

            return $sign;
        }

        // 拼接 url
        final protected function makeUrl($params){
            $url="";
            foreach ($params as $k => $v) {
                $url .= "$k=" . urlencode($v) . "&";
            }
            $url = substr($url, 0, -1); 
            return $url;
        }
        
        
        // trace
        final public function trace($msg=null){
            if( is_null($msg) )
                return $this->$traces;
                
            $this->traces[] = $msg; 
            
            if($this->output == true)
                echo $msg;               
        }   
        
        // log
        final public function log($name,$info,$extend=""){
            if( !is_array($this->logs) || count($this->logs) > 1000 )
                $this->logs = array();
            array_push($this->logs, date("Y-m-d H:i:s")." ".$info);
            
            if( is_array($extend) && !empty($extend) ){
                array_walk_recursive($extend, create_function('&$v,$k','$v = is_string($v) && strlen($v) > 1024 ? substr($v, 0, 1024)." ..." : $v;'));
                $info .= "\t".print_r($extend,true);   
            }                 
            
            $name="top-".intval($this->account["app_key"])."_".$name;
            Pithy::log($info,array("destination"=>$name,"level"=>"ERROR"),true);
        }      

    }
?>