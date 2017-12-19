<?php
    class TopList extends Top {
        
        static private $_instance=null;
        
        // 初始化
        public function initialize(){
            
        } 
        
        
        // 获取店铺分类
        static public function getSellerCats($nick,$top=true){
            if(self::$_instance==null)
                self::$_instance=new TopApi();        
            $api=self::$_instance;
            
            $api->method="taobao.sellercats.list.get";
            $api->nick=$nick;
            if(($rtn=$api->execute())==1 && isset($api->object,$api->object->seller_cats,$api->object->seller_cats->seller_cat)){
                $objs=$api->object->seller_cats->seller_cat;
                if($top){
                    foreach($objs as $n=>$obj){
                        if($obj->parent_cid!=0)
                            unset($objs[$n]);
                    }
                }
                return $objs;            
            }
            echo $api->response;
            return null;            
        }
        
        
        // 获取商品列表
        static public function getSellerItems($session,$fields=array("num_iid","title"),$options=array()){
            if(self::$_instance==null)
                self::$_instance=new TopApi();        
            $api=self::$_instance; 
            
            $api->method="taobao.items.onsale.get";
            $api->session=$session;
            $api->fields=is_array($fields)?implode(",",$fields):$fields;
            if(!empty($options) && is_array($options)){
                foreach($options as $k=>$v)
                    $api->set($k,$v);    
            }
            if(($rtn=$api->execute())==1 && isset($api->object,$api->object->items,$api->object->total_results)){
                 return $api->object;            
            }
            echo $api->response;
            return null;            
        }
         
    }
?>