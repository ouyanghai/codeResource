<?php
	class TopAuth extends Top {

		private $_info=array();                 
		private $_error="";
        
        
        // 初始化
        public function init(){  

            $args = func_get_args();            

            if( !empty($args[0]) && is_string($args[0]) && !empty($args[0]) ){
                $this->mode($args[0]);               
            }
        }

		// 取值
		public function get($name=""){
			$key=$this->account["app_key"];

			// 返回当前app_key的 _info
			if(empty($name))
				return $this->_info[$key];                

			// 返回指定app_key的 _info
			if(is_numeric($name) && isset($this->_info[$name]))
				return $this->_info[$name];

			// 返回当前app_key的 _info 中指定变量的值
			if(isset($this->_info[$key])){
				$info=$this->_info[$key];
				if(is_array($info))
					return isset($info[$name]) ? $info[$name] : null;     
			}
			
			return null;
		}
		// 赋值
		public function set($name,$value=""){
			$key=$this->account["app_key"];

			if(!is_array($this->_info)){
				$this->_info=array();
			}
			if(!isset($this->_info[$key])){
				$this->_info[$key]=array("appid"=>$key);    
			}

			if(is_array($name)){
				$this->_info[$key]=$name;
			}
			elseif(!empty($value)){
      		    $this->_info[$key][$name]=$value;
			}
			else{
				$this->_info[$key]["session"]=$name;    
			}
		} 

		// 获取出错信息
		public function getError(){
			return $this->_error;
		}
		// 设置出错信息
		public function setError($value){
			$this->_error=$value;    
		}
        

        
		// 判断当前页面是否是从淘宝登录过来的，如果是则可以进行登录验证 validate ，否则跳转到淘宝授权页面 auth
		public function check(){
			if(is_array($_GET) && ( isset($_GET["top_parameters"],$_GET["top_sign"]) ||  isset($_GET["notify_type"],$_GET["notify_result"]) || isset($_GET["code"]) || isset($_GET["error"]) ) ) 
				return true;
			return false;       
		}

		// 登录验证
		public function validate(){

			// 是否是回调页面
			$callback=false;
			$msg="未知错误！";

			// 如果当前页面是回调地址，并且是TOP进行回调请求( 即 url 地址里面包含特殊变量 )
			if( isset($_GET["top_parameters"],$_GET["top_sign"]) ){

				// 基于淘宝服务平台登录验证授权
				$callback=true;

				// 设置参数及验证
				$top_parameters=trim(rawurldecode($_GET["top_parameters"])); 				
                if(isset($_GET["top_session"])){
                    $top_session=trim($_GET["top_session"]); 
                    $token=TopUtil::generateToken($this->account["app_key"],$top_parameters,$top_session,$this->account["secret_code"]);    
                }
                else{
                    $top_session="";
                    $token=TopUtil::generateToken($top_parameters,$this->account["secret_code"]);    
                }
                
                /*
                echo $this->account["app_key"]."<br>".$token."<br>".rawurldecode($_GET["top_sign"])."<hr><pre>";
                var_dump( $token==rawurldecode($_GET["top_sign"]) );
                print_r( $_GET );
                exit;
                */
                
                if($token==rawurldecode($_GET["top_sign"])){ 

					// 解析参数
					parse_str(base64_decode($top_parameters),$arr); //Pithy::dump($arr);exit;                   

                    $ts = isset($arr["ts"]) ? ceil($arr["ts"]/1000) : 0 ;

					// 时间验证
					if( abs( time() - $ts ) < 60*60 ){ 
                        
                        if( isset($arr["visitor_role"]) && $arr["visitor_role"]==5 ){
                            $msg = "匿名用户尚未登录淘宝网！";    
                        }
                        else{
                            
                            $arr = TopUtil::convert($arr,"gbk","utf-8");
                        						
                            $this->set($arr);
						    
                            $this->set($top_session);
										       
						    if(isset($arr["user_id"]))
                                $this->set("uid",$arr["user_id"]);
                                
                            if(isset($arr["visitor_id"]))
							    $this->set("uid",$arr["visitor_id"]);
							    
						    if(isset($arr["visitor_nick"]))
							    $this->set("nick",urldecode($arr["visitor_nick"]));                                            						    

                            //Pithy::dump($this->get());exit; 
						    return true;
                        }
					}
					else{
						$msg="登录超时！".date("Y-m-d H:i:s", $ts);
					}    
				}
				else{                     
					$msg="返回的URL参数未通过验证！";
				}    
			}
			elseif( isset($_GET["notify_type"],$_GET["notify_result"]) ){ 

				// 基于淘宝开放平台技术标准登录验证授权
				$callback=true;

				parse_str($_SERVER["QUERY_STRING"],$arr);
				$sign=$arr["sign"];
				unset($arr["sign"]);
				if($sign==$this->makeSign($arr)){
					if(isset($arr["notify_result"]) && $arr["notify_result"]=="success"){                             
						$timestamp=$arr["timestamp"];
						if(abs(time()-strtotime($timestamp))<3600){
							$this->set($arr["session"]);
							return true;    
						}
						else{
							$msg="登录超时！".$timestamp;    
						}
					}
					else{
						$msg="验证信息出错！";    
					}                            
				}
				else{
					$msg="返回的URL参数未通过验证！";    
				}

			}
			elseif( isset($_GET["code"]) || isset($_GET["error"]) ){

				// 基于OAuth2.0登录验证授权 
				$callback=true;

				if( isset($_GET["code"]) ){
					$uri = empty($uri) ? "http://".$_SERVER["HTTP_HOST"] : $uri;
					$requestUrl=empty($this->account["authorize_token"])?$this->options["authorize_token"]:$this->account["authorize_token"];
					$params="grant_type=authorization_code&code=".$_GET["code"]."&redirect_uri=".$uri."&client_id=".$this->account["app_key"]."&client_secret=".$this->account["secret_code"]; 
					parse_str($params,$requestParams);                                                                                  
					$response=TopUtil::curl($requestUrl,$requestParams);                        
					$obj=json_decode($response); //Pithy::dump($obj);exit;
					if(is_object($obj)){ 
						
                        if( isset($obj->error) ){
                            $msg = "授权码失效，可能由于您刷新页面导致！"; 
                        }
                        elseif( !isset($obj->taobao_user_id,$obj->taobao_user_nick) ){
                            $msg = "匿名用户尚未登录淘宝网！";    
                        }
                        else{
                            
                            //$obj = TopUtil::convert($obj,"gbk","utf-8");
                            
                            $this->set(TopUtil::obj2array($obj));
                            
                            if(isset($obj->access_token) && !empty($obj->access_token))
                                $this->set($obj->access_token);
                               
                            if(isset($obj->taobao_user_id))
                                $this->set("uid",$obj->taobao_user_id);
                                
                            if(isset($obj->taobao_user_nick))
                                $this->set("nick",urldecode($obj->taobao_user_nick));    
                            
                            //Pithy::dump($this->get());exit;
                            return true;    
                        }                        
					}
					else{
						$msg="返回的URL参数未通过验证！";
					}                       
				}
				else{		
					$msg="其他错误信息[".$_GET["error"]." : ".$_GET["error_description"]."]！";           
				}
			}

			$error=$callback?$msg:"验证失败，请先登录再进行验证！";
            $this->log("login",$error,$_GET);                     
			$this->setError($error);         
			return false; 
		}   
                        


		// 授权方式1：使用 普通登录验证 方式授权（支持登录，支持短授权）
		public function home($params=array(),$redirect=true){
			$url = ( empty($this->account["container"]) ? $this->options["container"] : $this->account["container"] )."?encode=utf-8&appkey=".$this->account["app_key"]."&".$this->arr2url($params);    

			if( $redirect )
                TopUtil::redirect($url);
            else
                return $url;
		}


		// 授权方式2：使用 开放平台标准 方式授权（支持登录、注销、注册）          
		public function tAuth($type="login",$params=array(),$redirect=true){
			$options=array(
            "timestamp"=>date("Y-m-d H:i:s"),
            "sign_method"=>"md5",
			"app_key"=>$this->account["app_key"],
			);
			$params=array_merge($options,$params);
			$params["sign"]=$this->makeSign($params); 
			$url=( empty($this->account["container_".$type]) ? $this->options["container_".$type] : $this->account["container_".$type] ) . "?" . $this->makeUrl($params);  
			
            if( $redirect )
                TopUtil::redirect($url);
            else
                return $url; 
		}
		public function register($nick,$email,$mobile){
			$params=array(
			"app_user_nick"=>$nick,
			"app_user_email"=>$email,
			"app_user_mobile"=>$mobile,
			);
			$this->tAuth("register",$params);
		}
		public function logon($target="",$nick=""){
			$params=array(
			"app_user_nick"=>$nick,
			"target"=>$target,
			);
			$this->tAuth("login",$params);
		}
		public function logoff(){
			$this->tAuth("logout"); 
		}  


		// 授权方式3： 使用 OAuth2.0 方式授权（支持登录、注销）
		public function oAuth($type="login",$target="",$params=array(),$redirect=true){
			$target = empty($target) ? "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"] : $target;
			$target = urldecode($target);  
            if( strstr($target,"?") != "" ){                
                $target = preg_replace_callback("/=([^&]*)/",create_function('$m','return "=".urlencode(TopUtil::convert($m[1],"gbk","utf-8"));'),$target)."";
            }
            
            strstr($target, "?") == "" && $target .=  "?";

			if($type=="logout"){       
                $url = ( empty($this->account["authorize_logout"]) ? $this->options["authorize_logout"] : $this->account["authorize_logout"] )."?client_id=".$this->account["app_key"]."&redirect_uri=".urlencode($target); 
            }  
			else{
                $url = ( empty($this->account["authorize_login"]) ? $this->options["authorize_login"] : $this->account["authorize_login"] )."?client_id=".$this->account["app_key"]."&response_type=".($type=="login"?"code":"user")."&redirect_uri=".urlencode($target);
                //$params["authorize"] = $url;                    
                $url .= $this->arr2url($params, ( $type != "login" )); 
            }                                                                                   

            //exit("<a href='$url' target='_blank'>$url</a>");
            
            if( $redirect )
			    TopUtil::redirect($url);
            else
                return $url;
		}         
        public function auth($target="",$params=array(),$redirect=true){
            return $this->oAuth("auth",$target,$params,$redirect);    
        }        
        public function login($target="",$params=array(),$redirect=true){
            return $this->oAuth("login",$target,$params,$redirect);    
        }        
        public function logout($target="",$params=array(),$redirect=true){
            return $this->oAuth("logout",$target,$params,$redirect);    
        }

        
        
        // 将相关参数转换成url地址
        private function arr2url($params,$encode=false){
            $state = "";
            $scope = "";
            if( is_array($params) && !empty($params) ){
                $params["sign"] = "HDP";
                $params["token"] = TopUtil::generateToken($params);
                unset($params["sign"]);                
                $state = "&state=".base64_encode(serialize($params));
                
                if( $encode )
                    $state = urlencode($state);  
                if( isset($params["scope"]) && !empty($params["scope"]) )
                    $scope = "&scope=".$params["scope"]; 
            }  
            return $state.$scope;  
        }
	}
?>
