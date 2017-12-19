<?php 
/* 
*提供58，慧聪等资源给营天下手机
*  
*/
class ApiController extends TopController{
    
    public function init(){
        parent::init();
    }
    
    //提供58城市
    public function actionCity(){
        $city = $this->getTree(0, 'b2b_city');
        $city = json_encode($city);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $city .')' : $city;
    }
    
    //提供58类别
    public function actionCategory(){
        $command = Yii::app()->db->createCommand();
        $category = $command->setText("select id as value, name as text from b2b_category where pid=0 and is_hide=0")->queryAll();
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;
    }
    
    //提供手机号码
    public function actionMobile(){
        $command = Yii::app()->db->createCommand();
        $mobile = array();
        if( isset($_GET['tid'], $_GET['cid'], $_GET['offset'])){
            $tid = (int)$_GET['tid'];
            $cid = (int)$_GET['cid'];
            $offset = (int)$_GET['offset'];
            $keyword = !empty($_GET['keyword']) ? $_GET['keyword'] : '';
            
            $limit = 100;
            if(isset($_GET['limit'])){
                $limit = (int)$_GET['limit'];
            }
            
            $sql = "";
            if(empty($keyword)){
                $subCates = $command->setText("select id from b2b_category where pid={$cid}")->queryColumn();
                if( empty($subCates) ){
                    $condition = " and cid={$cid}";
                }else{
                    $str = implode(',', $subCates);
                    $condition = " and cid in ({$str})";
                }
                
                $sql = "select phone from b2b_company where char_length(phone)=11 and phone like '1%' and tid={$tid} {$condition} limit {$offset}, {$limit}";
                $mobile = $command->setText($sql)->queryColumn();
            }else{
                $sql = "select phones from app_provider where main_products like '%{$keyword}%' limit {$offset}, {$limit}";
                
                $res = $command->setText($sql)->queryColumn();
                
                foreach ($res as $value) {
                    $value = str_replace(' ', '', $value);
                    if(preg_match("/1[34578]{1}\d{9}/",$value,$match)){
                        array_push($mobile, $match[0]);
                    }
                }
            }
            
            //如果最后数据为空，则去当前城市所有类目取100条
            if( empty($mobile) ){
                $sql = "select phone from b2b_company where char_length(phone)=11 and phone like '1%' and tid={$tid} order by rand() limit {$offset}, {$limit}";
                $mobile = $command->setText($sql)->queryColumn();
            }
            
        }
        $mobile = json_encode($mobile);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $mobile .')' : $mobile;
    }

	//新版手机号码
	public function actionNMobile(){
        $command = Yii::app()->db->createCommand();
        $mobile = array();
        if( isset($_GET['tid'], $_GET['cid'], $_GET['offset'])){
            $tid = (int)$_GET['tid'];
            $cid = (int)$_GET['cid'];
            $offset = (int)$_GET['offset'];
            $keyword = !empty($_GET['keyword']) ? $_GET['keyword'] : '';
            
            $limit = 100;
            if(isset($_GET['limit'])){
                $limit = (int)$_GET['limit'];
            }
            
            $sql = "";
            if(empty($keyword)){
                $subCates = $command->setText("select id from b2b_category where pid={$cid}")->queryColumn();
                if( empty($subCates) ){
                    $condition = " and cid={$cid}";
                }else{
                    $str = implode(',', $subCates);
                    $condition = " and cid in ({$str})";
                }
                
                $sql = "select phone, name from b2b_company where char_length(phone)=11 and phone like '1%' and tid={$tid} {$condition} limit {$offset}, {$limit}";
                $mobile = $command->setText($sql)->queryAll();
            }else{
                $sql = "select phones as phone, name from app_provider where main_products like '%{$keyword}%' limit {$offset}, {$limit}";
                
                $res = $command->setText($sql)->queryAll();
                
                foreach ($res as $val) {
                    $value = str_replace(' ', '', $val['phone']);
                    if(preg_match("/1[34578]{1}\d{9}/",$value,$match)){
                        array_push($mobile, $val);
                    }
                }
            }
            
            //如果最后数据为空，则去当前城市所有类目取100条
            if( empty($mobile) ){
                $sql = "select phone, name from b2b_company where char_length(phone)=11 and phone like '1%' and tid={$tid} order by rand() limit {$offset}, {$limit}";
                $mobile = $command->setText($sql)->queryAll();
            }
            
        }
        $mobile = json_encode($mobile);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $mobile .')' : $mobile;
    }
    
    private function getChild(&$arr, $id){
        $childs=array();
        foreach ($arr as $k => $v){
            if($v['pid']== $id){
                $childs[]=$v;
            }
        }
        return $childs;
    }
    
    private function getTree($pid=0){
        global $rows;
        if( gettype($rows) != 'array' ){
            $num = func_num_args();
            $args = func_get_args();
            
            $command = Yii::app()->db->createCommand();
            $sql = "select id as value, name as text, pid from {$args[1]}";
            $rows = $command->setText($sql)->queryAll();
        }
        
        $childs = $this->getChild($rows, $pid);
        
        if( empty($childs) )
            return null;
        
        foreach ($childs as $k => $v){
            $rescurTree=$this->getTree($v['value']);
            if( null !=  $rescurTree){ 
                $childs[$k]['children']=$rescurTree;
            }
        }
        return $childs;
    }
    
    //接收文章数据
    public function actionData(){
        $post = $_POST;
        
        if( !isset($post['title'], $post['content']) )
            exit('参数错误，请重试！');
        
        $shopId = preg_match('/app\./Ui', $_SERVER['HTTP_HOST']) ? 8244 : 19322334;
        $pid = isset($post['company']) ? $post['company'] : $shopId;        //公司编号
        $cname = '互联网';     //类别名
        $tname = '深圳';      //城市名
        $cid = 2;       //类别编号
        $tid = 702;      //城市编号
        
        $data = array(
            'title' => addslashes(htmlspecialchars($post['title'])),
            'cname' => $cname,
            'tname' => $tname,
            'cid' => $cid,
            'tid' => $tid,
            'pid' => $pid,
            'create_time' => time(),
        );
        
        $db = Yii::app()->db;
        $command = $db->createCommand();
        $command->insert('b2b_info', $data);
        $infoId = $db->lastInsertId;
        
        $descData = array(
            'pid' => $infoId,
            'desc' => $post['content'],
        );
        
        $rtn = $command->insert('b2b_desc', $descData);
        echo $rtn;
        
    }
    
    //任务管理接口
    public function actionDataApi(){
        $command = Yii::app()->db->createCommand();
        if( !isset($_GET['sql']) ){
            echo '无效的参数';
            Yii::app()->end();
        }
        
        $sql = $_GET['sql'];
        $limit = 13;
        $page = !empty($_GET['p']) ? (int)$_GET['p'] : 1;
        $callback = !empty($_GET['callback']) ? $_GET['callback'] : '';
        
        $operates = array('select', 'insert', 'update', 'delete');
        $operate = strtolower(substr($sql, 0,  6));
        if( !in_array($operate, $operates) ){
            echo '无效的操作';
            Yii::app()->end();
        }
        
        if( $operate == 'select' ){
            $query = $sql . ' order by id desc limit :offset, :limit';
            if( $page == 'all' ){       //不分页
                $query = $sql . ' order by id desc';
            }
            $command->setText($query);
            
            if( $page != 'all' ){       //不分页
                $command->bindValue(':offset', ($page - 1) * $limit);
                $command->bindValue(':limit', $limit);
            }
            $result = $command->queryAll();
            
            $arr = explode('from', $sql);
            $table = $arr[1];
            $count = $command->setText("select count(*) as num from {$table}")->queryScalar();
            $result = array('data'=>$result, 'total'=>$count, 'page'=>$page);
        }else{
            $result = $command->setText($sql)->execute();
        }
        
        echo !empty($callback) ? $callback . "(". json_encode($result) .")" : json_encode($result);
    }
    
    //显示分组及分组下的所有设备
    public function actionDevices(){
        $callback = !empty($_GET['callback']) ? $_GET['callback'] : '';
        
        $command = Yii::app()->db->createCommand();
        $groups = $command->setText("select * from ytx_group where mode=1 order by id desc")->queryAll();
        $devices = $command->setText("select * from ytx_device order by id desc")->queryAll();
        foreach($devices as $device){
            foreach($groups as $k=>$group){
                if( $device['group_id'] == $group['id'] ){
                    $groups[$k]['children'][] = $device;
                }
            }
        }
        
        echo !empty($callback) ? $callback . "(". json_encode($groups) .")" : json_encode($groups);
    }
    
    //显示分组及分组下的所有脚本
    public function actionScripts(){
        $command = Yii::app()->db->createCommand();
    }
    
    //接收数据
    public function actionReceiveData(){
        $post = $_POST;
        print_r($post);
        //echo '<script>window.opener.location="http://app.task.com/index.php?r=task";window.close();</script>';
    }
    
    //测试
    public function actionTest(){
        $get = $_GET;
        if( !isset($get['imei'], $get['timestamp']) )
            exit('参数错误');
        
        $time = time();
        $arr = array(
            'datetime' => $time,
            'level' => 2,
        );
        echo isset($get['callback']) ? $get['callback'] . '('. json_encode($arr) .')' : json_encode($arr);
    }
    
    //得到58手机app类目
    public function actionMobileCategory(){
        /*$category = $this->getTree(0, 'ytx_58category');
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;*/
        $category = $this->getTree(0, 'b2b_category');
        foreach($category as $k => $v){
            if( isset($v['children']) ){
                $category[$k]['children'] = array_merge( $category[$k]['children'] );
            }
        }
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;
    }
    
    //得到58定制版手机app类目
    public function actionMobileCategorydz(){
        /*$category = $this->getTree(0, 'ytx_58category');
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;*/
        $category = $this->getTree(0, 'b2b_category');
        foreach($category as $k => $v){
            if( isset($v['children']) ){
                $category[$k]['children'] = array_merge(array(array('value' => '0', 'text' => '全部', 'pid' => $v['pid'])), $category[$k]['children']);
            }else{
                $category[$k]['children'] = array(array('value' => '0', 'text' => '全部', 'pid' => $v['pid']));
            }
        }
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;
    }
    
    //得到百姓手机app类目
    public function actionBxwCategory(){
        $category = $this->getTree(0, 'bxw_category');
        foreach($category as $k => $v){
            if( isset($v['children']) ){
                $category[$k]['children'] = array_merge(array(array('value' => '0', 'text' => '全部', 'pid' => $v['pid'])), $category[$k]['children']);
            }
        }
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;
    }
    
    //接收营天下手机数据，保存到数据库
    public function actionSaveData(){
        $get = $_GET;
        if( !isset($get['phone']) || !isset($get['cityname']) || !isset($get['companyname']) || !isset($get['catename']) ){
            echo '参数错误';
            Yii::app()->end();
        }
        
        $command = Yii::app()->db->createCommand();
        $data = array(
            'company' => addslashes($get['companyname']),
            'phone' => addslashes(trim($get['phone'])),
            'city_name' => addslashes($get['cityname']),
            'cate_name' => addslashes($get['catename']),
            'add_time' => time(),
        );
        $city_id = $command->setText("select id from b2b_city where name = '{$data['city_name']}' and pid!=0")->queryScalar();
        $cate_id = $command->setText("select id from b2b_category where name = '{$data['cate_name']}' and pid!=0")->queryScalar();
        $sql = "insert ignore into b2b_company (name, phone, create_time, tid, cid) values ('{$data['company']}', '{$data['phone']}', '{$data['add_time']}', '{$city_id}', '{$cate_id}')";
        $command->setText($sql)->execute();
    }
    
    public function actionMcategory(){
        $command = Yii::app()->db->createCommand();
        $get = $_GET;
        $pid = 0;
        
        if( isset($get['pname']) && !empty($get['pname']) ){
            $pname = $get['pname'];
            $pid = $command->setText("select id from b2b_category where name = '{$pname}'")->queryScalar();
        }
        
        $data = array(
            'name' => $get['name'],
            'pid' => $pid
        );
        //$command->insert('b2b_category', $data);
        $sql = "insert ignore into b2b_category (name, pid) values ('{$data['name']}', {$data['pid']})";
        $command->setText($sql)->execute();
    }

	//得到闲转类目
    public function actionXzCategory(){
        $category = $this->getTree(0, 'xz_category');
        foreach($category as $k => $v){
            if( isset($v['children']) ){
                $category[$k]['children'] = array_merge( $category[$k]['children'] );
            }
        }
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;
    }

    public function actionGetInfo(){
        if(empty($_GET['id']) || empty($_GET['timestamp']) || empty($_GET['token']) || empty($_GET['tid']) || empty($_GET['cid']) || !isset($_GET['offset'])){
            echo json_encode("缺少参数");exit;
        }

        $id = $_GET['id'];
        $tid = (int)$_GET['tid'];
        $cid = (int)$_GET['cid'];
        $offset = (int)$_GET['offset'];

        $command = Yii::app()->db->createCommand();
        $uInfo = $command->setText("select * from `phone_user` where id={$id}")->queryRow();
        if(empty($uInfo)){
            echo json_encode("用户不存在");exit;
        }
        
        if(strtotime($uInfo['deadline']) < time()){
            echo json_encode("用户账号已到期");exit;
        }

        $remain = 0;//今日剩余数

        if(strtotime($uInfo['updated']) > strtotime(date('Y-m-d'))){
            $remain = $uInfo['remain'];
        }else{
            $remain = $uInfo['sum'];
        }

        if($remain <=0){
            echo json_encode("今日数量已用完");exit;
        }

        $token = $this->generateToken($_GET['id'],$uInfo['secret'],$_GET['timestamp']);
        if($token != $_GET['token']){
            echo json_encode("token验证失败");exit;
        }

        $limit = $remain < 100 ? $remain : 100;
        $mobile = array();
        $subCates = $command->setText("select id from b2b_category where pid={$cid}")->queryColumn();
        if( empty($subCates) ){
            $condition = " and cid={$cid}";
        }else{
            $str = implode(',', $subCates);
            $condition = " and cid in ({$str})";
        }
        
        $sql = "select phone from b2b_company where char_length(phone)=11 and phone like '1%' and tid={$tid} {$condition} limit {$offset}, {$limit}";
        $mobile = $command->setText($sql)->queryColumn();
        
        $remain = $remain-count($mobile);
        $now = date('Y-m-d H:i:s');
        $command->setText("update `phone_user` set remain={$remain},updated='{$now}' where id={$id}")->execute();

        $mobile = json_encode($mobile);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $mobile .')' : $mobile;
        
    }

    public function generateToken($id,$secret,$timestamp){
        return md5($id.$timestamp.$secret);
    }
    
    //发送短信
    public function actionSms(){
        header('Content-Type: text/html;charset=utf8;');
        
        $get = $_GET;
        
        if( empty($get['mobile'])
        || empty($get['city'])
        || empty($get['category'])
        || empty($get['wx'])
        || empty($get['linkman'])
         ){
             echo '参数错误';
             Yii::app()->end();
         }
         
        $command = Yii::app()->db->createCommand();
        
        extract($get);
        
        $command = Yii::app()->db->createCommand();
        $row = $command->setText("select id, create_time from ytx_sms_log where mobile = '{$mobile}'")->queryRow();
        if( !empty($row) ){
            Pithy::log($mobile . '已经发送过', 'ytx_sms', true);
            echo $mobile . '已发送过';
            Yii::app()->end();
        }
        
        $linkman = $linkman == 'undefined' ? '' : $linkman;
        $category = $category == 'undefined' ? '' : $category;
        $wx = $wx == 'undefined' ? '' : $wx;
        
        $content = "【同城网】{$linkman}，您有客户咨询{$category}，客户微号： {$wx} ，请及时联系，回TD退订。";
        
        $token = md5($mobile .'sms-haodianpu');
        $timestamp = time();
        $url = "http://i.haodianpu.com/site/sms?mobile={$mobile}&token={$token}&timestamp={$timestamp}&content={$content}";
        $str = @HTTP::get($url);
        //$str = 'success';
        
        //百分之一的机率发给屈总
        if( $this->getRand(1, 100) ){
            $url = "http://i.haodianpu.com/site/sms?mobile=15889528589&token={$token}&timestamp={$timestamp}&content={$content}";
            @HTTP::get($url);
        }
         
         if( $str != 'success' ){
             echo $str;
             Yii::app()->end();
         }
         $data = array(
            'mobile' => $mobile,
            'linkman' => $linkman,
            'city' => $city,
            'category' => $category,
            'create_time' => time(),
            'content' => $content
         );
         
         $rtn = $command->insert('ytx_sms_log', $data);
         
         $state = $rtn == 1 ? '成功' : '失败';
         Pithy::log($mobile .'发送成功，保存'. $state, 'ytx_sms', true);
         echo $rtn;
        
    }
    
    public function getRand($i=1, $ratio){
        $result = 0;
        
        $randNum = mt_rand(0, $ratio);
        
        return $randNum == $i ? true : false;
    }
    
    public function actionXyCategory(){
        $category = $this->getTree(0, 'xy_category');
        foreach($category as $k => $v){
            if( isset($v['children']) ){
                $category[$k]['children'] = array_merge(array(array('value' => '0', 'text' => '全部', 'pid' => $v['pid'])), $category[$k]['children']);
            }
        }
        $category = json_encode($category);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $category .')' : $category;
    }

    public function actionGetHouseBroker(){
        if(empty($_GET['name'])||empty($_GET['phones'])||empty($_GET['city'])){
            echo json_encode("参数错误");exit;
        }
        $name = $_GET['name'];
        $phones = trim($_GET['phones']);
        if(strlen($phones) !=11){
            echo json_encode("电话参数错误");exit;
        }

        $city = $_GET['city'];
        $date = date('Y-m-d H:i:s');

        $command = Yii::app()->db->createCommand();
        $num = $command->setText("insert ignore into `house_broker` (name,phones,city,created) values('{$name}','{$phones}','{$city}','{$date}')")->execute();
        if($num>0){
            echo json_encode("ok");exit;
        }
        echo json_encode("error");exit;
    }

    public function actionMfwCity(){
        $city = $this->getTree(0, 'mfw_city');
        $city = json_encode($city);
        echo isset($_GET['callback']) ? $_GET['callback'] . '('. $city .')' : $city;
    }
    
    //接收报表数据
    public function actionWxhReport(){
        //$arr = {"123456":{"total":"123", "pyq":"12", "increment":"55"}, "456789":{"total":"123", "pyq":"12", "increment":"55"}}
        $info = array(
            'state' => 0,
            'msg' => '',
			'act' => ''
        );
        
        $get = $_GET;
        if( empty($get['data']) ){
            $info['msg'] = '接收的数据为空';
            echo !empty($get['callback']) ? $get['callback']. "("+ json_encode($info) +");" : json_encode($info) .';';
            Yii::app()->end();
        }
        
        $data = $get['data'];
        $arr = json_decode($data, true);

        $arry = array();
        $today = date("Y-m-d");
        $time = time();
		$rtn = 0;
        $command = Yii::app()->db->createCommand();
        foreach($arr as $wxh => $j){
            $id = $command->setText("select id from wxh_report where wxh='{$wxh}' and dt='{$today}'")->queryScalar();
			$exist = $command->setText("select id from wxh where wxh='{$wxh}'")->queryRow();
			if( empty($exist) ){
				$command->setText("insert into wxh (wxh, add_time) values ('{$wxh}', '{$time}')")->execute();
			}
            
            if( !empty($id) ){
				$info['act'] = 'edit';
                $sql = "update wxh_report set total='{$j['total']}', pyq='{$j['pyq']}', increment='{$j['increment']}' where id=". $id;
            }else{
				$info['act'] = 'insert';
                $sql = "insert into wxh_report (wxh, total, pyq, increment, dt) values ('{$wxh}', '{$j['total']}', '{$j['pyq']}', '{$j['increment']}', '{$today}')";
            }
            $rtn = $command->setText($sql)->execute();
        }
		
		$info['state'] = 1;
		if($rtn <= 0){
			$info['state'] = 0;
			$info['msg'] = $data;
		}
        
        echo !empty($get['callback']) ? $get['callback'] ."(". json_encode($info) .");" : json_encode($info) .';';

    }
    
    public function actionWxhList($owner=''){

		$cond = "1=1";

		if(!empty($owner)){
			$cond .= " and owner='{$owner}'";
		}
        
        $command = Yii::app()->db->createCommand();
        
        $data = $command->setText("select * from wxh where {$cond} order by id desc")->queryAll();
        
        echo json_encode($data);
        
    }
    
    public function actionWxhRp($condition=''){
        $cond = urldecode($condition);
        $command = Yii::app()->db->createCommand();
        $data = $command->setText("select * from wxh_report where 1=1 {$cond} order by id desc")->queryAll();
        
        echo json_encode($data);
        
    }

	public function actionReportDel($condition=''){
		$cond = urldecode($condition);
        $command = Yii::app()->db->createCommand();
        $data = $command->setText("delete from wxh where {$cond}")->execute();
        
        echo json_encode($data);
	}

	public function actionGetReport($condition=''){
		$cond = urldecode($condition);
        $command = Yii::app()->db->createCommand();
        $data = $command->setText("select * from wxh where 1=1 {$cond}")->queryRow();
        
        echo json_encode($data);
	}

	public function actionReportSave($data='', $condition=''){
		$data = urldecode($data);
		$cond = urldecode($condition);
        $command = Yii::app()->db->createCommand();
		if( !empty($cond) ){
			$data = $command->setText("update wxh set {$data} where {$cond}")->execute();
		}else{
			$data .= ", add_time='". time() ."'";
			$data = $command->setText("insert into wxh set {$data}")->execute();
		}
        
        
        echo json_encode($data);
	}
    
    public function actionWxhTongji($owner=''){
        $cond = urldecode($owner);
        $command = Yii::app()->db->createCommand();
        
        //查出所有微信号
        $result = $command->setText("select * from wxh where owner='{$owner}'")->queryAll();
        $pyq = 0;
        $total = 0;
        $today = date("Y-m-d", strtotime("-1 day"));
        foreach($result as $k => $v){
            $row = $command->setText("select * from wxh_report where wxh='{$v['wxh']}' and dt='{$today}'")->queryRow();
            $pyq += $row['pyq'];
            
            if( $row['increment'] > 0 )
                $total += $row['increment'];
        }
        
        echo "昨日好友总数量：{$total}，朋友圈总数量：{$pyq}";
    }
    
}