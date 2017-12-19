<?php
    class TopApi extends Top {

        public $method;
        public $session;

        public $request;
        public $response;
        public $object;

        private $params;                                  

        // 魔术函数 

        public function __get($name){             
            if($name=="mode")
                return $this->mode();
            $name=TopUtil::parseName($name);          
            return isset($this->params[$name])?$this->params[$name]:null;
        }   

        public function __set($name,$value){
            if(!empty($name)){
                if(is_array($name)){                    
                    foreach($name as $key=>$value){
                        $this->__set($key,$value);
                    }
                    return;
                }
                if(is_string($name)){
                    if(is_null($value) && isset($this->params[$name])){
                        unset($this->params[$name]);
                        return;    
                    }
                        
                    if($name=="mode")
                        return $this->mode($value);
                                                
                    $name=TopUtil::parseName($name);
                    $value=TopUtil::convert($value,$this->options["charset"],"utf-8");
                    $this->params[$name]=$value;
                    return;
                }
            }
        } 

        public function __isset($name){
            return isset($this->params[$name]);
        }

        public function __unset($name){
            unset($this->params[$name]);
        }

        public function __call($method,$args){       
            if($method=="method"){
                return $this->method=$args[0];
            }       
            if($method=="session"){
                return $this->session=$args[0];
            }               
            if(substr($method,0,3)=="get"){
                return $this->__get(substr($method,3));
            }
            if(substr($method,0,3)=="set"){
                return $this->__set(substr($method,3),$args[0]); 
            }

            trigger_error("Method ($method) not exists!",E_USER_WARNING);
        }  

        // 初始化
        public function init(){  

            $args = func_get_args();            

            if( !empty($args[0]) ){
                if( is_string($args[0]) ){
                    if( strstr($args[0], ".")!="" )                    
                        $this->method = $args[0];
                    else
                        $this->mode = $args[0];
                }
                if( is_array($args[0]) ){
                    $this->options = array_merge($this->options, $args[0]);

                    if( isset($this->options["mode"]) ){
                        $_mode=$this->options["mode"];
                        if( !empty($_mode) )
                            $this->mode = $_mode;
                    }

                    if( isset($this->options["method"]) ){
                        $_method = $this->options["method"];
                        if( !empty($_method) )
                            $this->method = $_method;
                    }

                    if( isset($this->options["session"]) ){
                        $_session = $this->options["session"];
                        if( !empty($_session) )
                            $this->session = $_session;
                    }
                } 
            }

            if( !empty($args[1]) ){                
                $this->session = $args[1];
            }
        }

        // 获取变量的值
        public function get($name){
            return $this->__get($name);
        }

        // 设置变量的值
        public function set($name,$value){
            $this->__set($name,$value); 
        }                                      

        // 获取 params 的值
        public function getParams(){              
            return is_array($this->params)?$this->params:array();
        }  

        // 设置 params 的值
        public function setParams($data,$clear=false){ 
            if(is_null($data) || $clear){
                $this->params = array();
            } 
            if(is_array($data)){
                $this->__set($data,"");
            }           
        }        

        /**
        *  查询接口（带查询参数，并且参数不会影响上下文的调用，支持批量查询）
        * 
        * @param mixed $data  查询参数
        * @param mixed $page  查询页码
        * @param mixed $size  每页的记录数
        * @param mixed $total_var  当前接口总记录数的变量名称
        */
        public function query($data=array(), $page=false, $size=100, $total_var="total_results")
        {
            $method = $this->method;
            $session = $this->session;
            $params = $this->getParams();
            
            // 如果 $data 变量不是数组、则表示所有参数前移一位
            if( !is_array($data) ){
                is_string($size) && $total_var = $size;
                $size = $page;
                $page = $data;
            }
            else{
                // 将给定的参数添加到应用参数中，执行完毕后会进行清除和还原

                if(isset($data["method"]) && is_string($data["method"])){
                    $this->method=$data["method"];
                    unset($data["method"]);
                }
                                
                if(isset($data["session"]) && is_string($data["session"])){
                    $this->session=$data["session"];
                    unset($data["session"]);
                }

                $this->setParams($data);
            }


            // 获取分页信息
            $pages = array(1);
            if( $page !== false ){                 
                if( $page === true ){
                    $pages = range(1,1000);
                }
                elseif( is_array($page) ){
                    $pages = $page;
                }
                elseif( is_numeric($page) ){
                    $pages = array($page);
                }
                elseif( is_string($page) ){
                    if( strstr($page, ",") != "" )
                        $pages = explode(",", $page);
                    if( strstr($page, "-") != "" ){
                        list($min, $max) = explode("-", $page);
                        $pages = range($min, $max);
                    }
                }
            }


            $this->trace("\r\nExecute method : ".$this->method."\r\n");

            // 执行批量获取操作
            $this->trace("Query starting ... \r\n");
            $result = array();

            $_page = 1;
            $_size = intval($size) <=0 ? 100 : intval($size);

            while(1){

                if( $page !== false ){
                    $_page = array_shift($pages);
                    
                    if( empty($_page) || $_page<=0 )
                        break;

                    $this->set("page_no", $_page);
                    $this->set("page_size", $_size);
                    $this->trace(" -=> Fetch page ".$_page." : ");
                }

                if( ( $rtn=$this->execute() ) != 1 ){
                    $this->trace(" Error ".$rtn."\r\n");
                    break;
                }

                $total = 0;
                if( isset($this->object) ){
                    $result[$_page] = $this->object;
                    if( isset($this->object->$total_var) )
                        $total = $this->object->$total_var;
                }

                if( $page === false || ceil($total / $_size) <= $_page ){
                    $this->trace(" Finish!\r\n");
                    break;
                }

                $this->trace(" Done!\r\n");
            }

            $this->trace("Query end ! \r\n");

            // 清空之前添加的应用级参数，使调用此方法时传递的参数不会保留在应用参数内
            if( is_array($data) ){
                $this->method = $method;
                $this->session = $session;
                $this->setParams($params,true);
            }
            
            return $result;
        }

        // 查询接口（不带查询参数）
        public function execute($session=""){
            if(empty($this->method) || !is_string($this->method)){
                trigger_error("API method is null",E_USER_WARNING);
                return 0;
            }

            $session=empty($session)?$this->session:$session;

            // 获取系统参数
            $sysParams["method"]=$this->method;
            $sysParams["app_key"]=$this->account["app_key"];
            $sysParams["v"]=$this->options["v"];
            $sysParams["format"]=$this->options["format"];
            $sysParams["sign_method"]=$this->options["sign_method"];            
            $sysParams["timestamp"]=date("Y-m-d H:i:s");
            if(!empty($session))
                $sysParams["session"]=$session;

            // 获取业务参数
            $appParams=$this->getParams();

            // 获取签名
            $sysParams["sign"] = $this->makeSign(array_merge($appParams, $sysParams));

            // 系统参数放入GET请求串
            $requestUrl = ( empty($this->account["gateway"]) ? $this->options["gateway"] : $this->account["gateway"] ) . "?" . $this->makeUrl($sysParams);

            // 保存相关request变量
            $this->request = array("url"=>$requestUrl, "sys"=>$sysParams, "app"=>$appParams);

            //发起HTTP请求
            try{
                $this->response=null;
                $this->object=null;
                $resp = TopUtil::curl($requestUrl, $appParams);
            }
            catch(Exception $e){                
                $this->log("connect","[".$this->method."] ".$e->getMessage(),$this->request);
                return -1;
            }

            //替换掉结果中的换行符
            $resp=str_replace(array("\r\n","\n"),array("\\r\\n","\\n"),$resp);

            //保存TOP返回原始结果
            $this->response=$resp; 
            
            //Pithy::dump($this->request);
            //Pithy::dump($this->response);
            //exit; 

            //解析TOP返回结果
            $respResult = false;
            if ("json" == $this->options["format"]){                                
                //$resp = preg_replace('/([^\\\\]"[^:]*":)(\d{8,})/i', '${1}"${2}"', $resp);
                $resp = preg_replace('/([\\\\]?)(":)([0-9]{8,})(}|,"|,\\\\")/i', '$1$2$1"$3$1"$4', $resp);  // PHP5.3以下版本的json_decode存在整型数溢出问题
                $respObject = json_decode($resp);
                if (null == $respObject){                     
                    $resp = preg_replace_callback('/("[^:]*":")(.+?)("[,}\s])/', create_function('$matches','return $matches[1].str_replace("\"","\\\\\"",$matches[2]).$matches[3];'), $resp);    // 替换值中的 " 
                    $respObject = json_decode($resp);
                }  
                if (null == $respObject){                     
                    $resp = TopUtil::convert($resp,"gbk","utf"); // 替换值中GBK编码的中文字符为UTF编码
                    $respObject = json_decode($resp);
                }                
                if (null !== $respObject){
                    $respResult = true;
                    if(!empty($respObject)){                         
                        foreach ($respObject as $propKey => $propValue){
                            $respObject = $propValue;
                        }
                    } 
                    $respObject=TopUtil::convert($respObject,"utf-8",$this->options["charset"]);
                }
            }
            elseif("xml" == $this->options["format"]){
                $respObject = @simplexml_load_string($resp);
                if (false !== $respObject){
                    $respResult = true;
                }
            }

            //返回的HTTP文本不是标准JSON或者XML，记下错误日志
            if(false === $respResult){
                $this->log("format","[".$this->method."] HTTP_RESPONSE_NOT_WELL_FORMED \t".$this->response,$this->request);
                return 0;
            }

            // 保存TOP返回结果
            $this->object=$respObject;

            //如果TOP返回了错误码，记录到业务错误日志中
            if(isset($respObject->code)){
                $code=intval($respObject->code);
                $this->log($this->method,"[".$this->method."] ".$code."\t".$this->response,$this->request);                                
                return $code;
            }

            return 1;
        }

    }
?>