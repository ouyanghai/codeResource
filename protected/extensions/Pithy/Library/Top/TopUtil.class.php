<?php
    class TopUtil { 
        
        static public $proxys = array(
                                   "219.150.204.30:80",
                                   "120.203.214.182:83",
                                   "120.203.214.182:81",
                                   "58.20.223.230:3128",
                                   "120.203.214.182:80",
                                   "120.203.214.182:82",
                                   "202.98.123.126:8080",
                                   "122.72.76.125:80",
                                   "120.203.214.182:84",
                                   "120.203.214.182:86",
                                   "60.28.183.5:8081",
                                   "61.55.141.10:81",
                                   "120.203.214.182:85",
                                   "211.167.64.112:8080",
                                   "116.228.55.217:8000",
                                   "221.238.28.158:8081",
                                   "116.236.216.116:8080",
                                   "115.25.216.6:80",
                                   "61.55.141.11:81",
                                   "116.228.55.217:80",
                                   "114.80.136.112:7780",
                                   "124.119.50.254:80",
                                   "116.112.66.102:808",
                                   "61.135.179.167:8080",
                                   "114.141.162.53:8080",
                                   "221.176.14.72:80",
                                   "119.75.219.70:80",
                                   "211.142.236.137:8080",
                                   "59.64.38.77:808",
                                   "61.164.42.147:80",
                                   "183.136.129.72:9999",
                                   "203.191.150.11:8081",
                                   "77.94.48.5:80",
                                   "211.142.236.137:80",
                                   "202.202.0.163:3128",
                                   "211.142.236.133:8080",
                                   "211.142.236.133:80",
                                   "116.228.55.184:80",
                                   "220.132.19.136:8080",
                                   "61.242.169.94:81",
                                   "221.238.140.164:8080",
                                   "5.199.166.250:7808",
                                   "66.35.68.145:7808",
                                   "183.136.146.110:8085",
                                   "112.25.137.201:8000",
                                   "162.105.17.79:808",
                                   "221.10.40.236:843",
                                   "211.167.112.14:82",
                                   "110.173.0.18:80",
                                   "211.167.112.14:80",
                                   "107.23.180.148:8080",
                                   "114.113.229.39:8000",
                                   "221.10.40.232:81",
                                   "221.10.40.232:80",
                                   "221.10.40.232:82",
                                   "221.10.40.236:83",
                                   "221.10.40.236:80",
                                   "202.116.160.89:80",
                                   "91.228.53.28:8089",
                                   "125.88.75.151:3128",
                                   "59.47.43.88:8080",
                                   "210.14.133.202:80",
                                   "60.29.59.209:80",
                                   "111.1.32.51:82",
                                   "120.85.132.234:80",
                                   "113.108.92.104:80",
                                   "111.1.55.19:8088",
                                   "111.1.32.51:8088",
                                   "101.226.74.168:8080",
                                   "222.92.141.250:80",
                                   "61.136.68.76:808",
                                   "111.1.32.51:8084",
                                   "111.1.32.51:87",
                                   "111.1.32.51:8086",
                                   "112.213.97.69:80",
                                   "222.90.211.198:1337",
                                   "218.104.148.157:8080",
                                   "121.22.72.61:8080",
                                );


        static public function parseName($name,$type=0) {
            if($type){
                return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
            }
            else{
                $name=preg_replace("/[A-Z]/", "_\\0", $name);
                return strtolower(trim($name, "_"));
            }
        }

        static public function generateToken(){
            $args=func_get_args();
            $argc=func_num_args();
            if($argc==0)
                return null;

            $params=array();                        
            if(is_string($args[0])){
                $params=$args;                 
            }
            if(is_array($args[0])){
                $params=$args[0]; 
            }

            if(!empty($params)){
                $str="";
                foreach($params as $v){
                    $str.=trim($v);
                }                
                return base64_encode(md5($str,true));
            }      

            return null;    
        }

        static public function convert($content,$from="gbk",$to="utf-8"){
            $from = in_array(strtoupper($from),array("UTF","UTF8","UTF-8")) ? 'UTF-8' : strtoupper($from);
            $to = in_array(strtoupper($to),array("UTF","UTF8","UTF-8")) ? 'UTF-8' : strtoupper($to);
            if( empty($content) || (is_scalar($content) && !is_string($content)) ){
                return $content;
            }
            if(is_string($content)) {
                
                if( preg_match('/^.*$/u', $content) > 0 )
                    $from = "UTF-8";
                    
                if( $from != $to ){
                    if(function_exists('mb_convert_encoding')){
                        return mb_convert_encoding($content, $to, $from);
                    }
                    elseif(function_exists('iconv')){
                        return iconv($from,$to,$content);
                    }
                }

                return $content;                
            }
            elseif(is_array($content)){                
                foreach ( $content as $key => $val ) {
                    $_key = self::convert($key,$from,$to);
                    $content[$_key] = self::convert($val,$from,$to);
                    if($key != $_key )
                        unset($content[$key]);
                }
                return $content;
            }
            elseif(is_object($content)){
                foreach ( $content as $key => $val ) {
                    $content->$key = self::convert($val,$from,$to);                    
                }
                return $content;
            }
            else{
                return $content;
            }            
        }

        static public function encode($str,$type="escape"){
            if($type=="base64"){
                $data=base64_encode($str);
                $data=str_replace(array('+','/','='),array('-','_',''),$data);
                return $data;
            }

            if($type=="escape"){
                preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r);  
                $arr=$r[0];  
                foreach($arr as $k=>$v){  
                    if(ord($v[0])<128){
                        $arr[$k]=rawurlencode($v);
                    }
                    else{  
                        $arr[$k]="%u".strtoupper(bin2hex(mb_convert_encoding($v,"UCS-2","GB2312")));
                    }
                }
                return join("",$arr);    
            }

            return $str;    
        }

        static public function decode($str,$type="escape"){
            if($type=="base64"){
                $data=str_replace(array('-','_'),array('+','/'),$str);
                $mod4=strlen($data)%4;
                if($mod4){
                    $data.=substr('====',$mod4);
                }
                return base64_decode($data);
            }

            if($type=="escape"){
                preg_match_all("/(%u[0-9|A-F]{4})/",$str,$r); 
                $arr=$r[0];
                foreach($arr as $k=>$v){  
                    if(substr($v,0,2)=="%u" && strlen($v)==6){
                        $str=str_replace($v,mb_convert_encoding(pack("H4",substr($v,-4)),"GB2312","UCS-2"),$str);                
                    }
                }
                return rawurldecode($str);
            }

            return $str;    
        }

        static public function xml2array($xml){

            $xmlary = array();

            $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
            $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

            preg_match_all($reels, $xml, $elements);

            foreach($elements[1] as $ie => $xx) {
                $xmlary[$ie]["name"] = $elements[1][$ie];

                if($attributes = trim($elements[2][$ie])) {
                    preg_match_all($reattrs, $attributes, $att);
                    foreach($att[1] as $ia => $xx)
                        $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
                }

                $cdend = strpos($elements[3][$ie], "<");
                if($cdend > 0) {
                    $xmlary[$ie]["text"] = substr($elements[3][$ie], 0, $cdend - 1);
                }

                if(preg_match($reels, $elements[3][$ie]))
                    $xmlary[$ie]["elements"] = self::xml2array($elements[3][$ie]);
                elseif ($elements[3][$ie]) {
                    $xmlary[$ie]["text"] = $elements[3][$ie];
                }
            }

            return $xmlary;
        }

        static public function obj2array($obj){
            if( is_object($obj) ){
                $obj = get_object_vars($obj);
            }
            if( is_array($obj) ){
                foreach( $obj as $key => $val ) {
                    $obj[$key] = self::obj2array($val);
                }
            }
            return $obj;
        }

        static public function curl($url, $postFields = null, $proxy=false){
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);

            if(substr($url,0,5)=="https"){
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            
            if( $postFields === true || $proxy === true ){
                shuffle(self::$proxys);
                $proxy = reset(self::$proxys);
                curl_setopt($ch, CURLOPT_PROXY, $proxy);    
            }                

            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36");
            curl_setopt($ch, CURLOPT_REFERER, "http://www.taobao.com");

            curl_setopt($ch, CURLOPT_FAILONERROR, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 支持重定向
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);          // 允许执行的最长秒数
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // 连接前等待的时间

            if(is_array($postFields) && 0 < count($postFields)) {
                $postBodyString = "";
                $postMultipart = false;
                foreach($postFields as $k => $v){
                    //判断是不是文件上传
                    if("@" != substr($v, 0, 1)) {
                        $postBodyString .= "$k=" . urlencode($v) . "&"; 
                    }
                    else{
                        //文件上传用multipart/form-data，否则用www-form-urlencoded                     
                        $postMultipart = true;
                    }
                }
                unset($k, $v);
                curl_setopt($ch, CURLOPT_POST, true);
                if($postMultipart){
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                }
                else{
                    curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
                }
            }
            $reponse = curl_exec($ch);

            if(curl_errno($ch)){
                return 'error';
                throw new Exception(curl_error($ch),0);                
            }
            else{
                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (200 !== $httpStatusCode){
                    //throw new Exception($reponse,$httpStatusCode);
                }
            }
            curl_close($ch);
            return $reponse;
        } 



        // 页面重定向
        static public function redirect($url,$time=0,$msg=''){
            //多行URL地址支持
            $url = str_replace(array("\n", "\r"), '', $url);
            if(empty($msg))
                $msg = "系统将在{$time}秒之后自动跳转到{$url}！"; 

            if(!headers_sent()) {
                if(0===$time) {
                    header("Location: {$url}");
                }
                else {
                    header("refresh:{$time};url={$url}");
                    echo($msg);
                }                
            }
            else {                                      

                if($time>0 and $time<1){
                    $str = "<script language='javascript'>setTimeout(function(){self.location='$url'},".($time*1000).")</script>";
                }
                else{
                    $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
                }

                if($time!=0)
                    $str .= $msg;

                echo($str);
            } 

            exit;            
        } 

    } 
?>