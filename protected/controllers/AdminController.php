<?php
class AdminController extends TopController{
	public $menu;
	public $grade;
	public function init(){
		parent::init();
		$this->layout='//layouts/column3';

		$this->menu = array(
			array("label"=>"基本信息","url"=>"/admin/index"),
			array(
				"label"=>"用户信息",
				"ctr"=>array("modifyPass"),
				"childMenu"=>array(
					array('label'=>"修改密码","url"=>"/admin/modifyPass"),
				),
			),
			array(
				"label"=>"账单流水",
				"ctr"=>array("recharge","rechargeList"),
				"childMenu"=>array(
					array('label'=>'账号充值',"url"=>"/admin/recharge"),
					array('label'=>'充值记录',"url"=>"/admin/rechargeList"),
				),
			),
		);

		$this->grade = array(
			'0'=>"体验版",
			'1'=>'专业版',
			'2'=>'豪华版'
		);
	}

	public function actionIndex(){
		$this->render('index');
	}

	public function actionModifyPass(){
		$this->render('modifypass');
	}

	public function actionList(){
		$this->render('list');
	}

	//登录页面
	public function actionLoginPage(){
		$this->render('login');
	}
	//注册页面
	public function actionRegPage(){
		$this->render('register');
	}

	public function actionLogout(){
		// 清空用户之前的登录状态
        Yii::app()->user->clearStates();
        Yii::app()->user->logout();
        echo json_encode("ok");
	}

	public function actionDoModifyPass(){
		$password = $_POST['password'];
		$newpass = $_POST['newpass'];
		$repass = $_POST['repass'];
		$tel = Yii::app()->user->tel;

		$command = Yii::app()->db->createCommand();
		$num = $command->setText("update `phone_user` set password='{$newpass}' where tel='{$tel}' and password='{$password}'")->execute();
		if($num > 0){
			echo json_encode("修改成功");
		}else{
			echo json_encode("修改失败");	
		}
		
	}

	public function actionDoRegister(){
		if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['repassword']) ||empty($_POST['tel'])){
			echo json_encode("参数错误");
			exit;
		}
		if($_POST['password'] != $_POST['repassword']){
			echo json_encode("两次输入密码不一致");
			exit;
		}
		if(!preg_match("/^1[34578]\d{9}$/", $_POST['tel'])){
			echo json_encode("手机号码格式错误");
			exit;	
		}
		$nick = $_POST['username'];
		$password = $_POST['password'];
		$tel = $_POST['tel'];
		$now = date('Y-m-d H:i:s');
		$deadline = date('Y-m-d',strtotime("+1 month"));
		$remain = 100;//初始化，每天使用100

		$token = $this->actionGetToken("qiye");
		$command = Yii::app()->db->createCommand();
		$sql = "insert into `phone_user` (nick,password,tel,created,updated,deadline,token,remain) values('{$nick}','{$password}','{$tel}','{$now}','{$now}','{$deadline}','{$token}',{$remain})";
		$num = $command->setText($sql)->execute();
		if($num < 1){
			echo json_encode("网络错误");
			exit;
		}
		echo json_encode("ok");
		exit;
	}

	public function actionModSecret(){
		$msg = array("status"=>"ok","msg"=>'');
		if(empty($_POST['secret']) || strlen($_POST['secret'])>8){
			$msg['status'] = "error";
			echo json_encode($msg);exit;
		}
		$secret = $_POST['secret'];
		$id = Yii::app()->user->uid;
		$command = Yii::app()->db->createCommand();
		$num = $command->setText("update `phone_user` set secret='{$secret}' where id={$id}")->execute();
		if($num > 0){
			Yii::app()->user->setState("secret", $secret);
			echo json_encode($msg);exit;	
		}

		$msg['status'] = "error";
		echo json_encode($msg);exit;
	}

	private function actionGetToken($secret){
		return md5(time().$secret."qiye".$secret);
	}
	public function filters(){
		return array(
			"auth - loginPage,regpage,doregister"
		);
	}
}

?>