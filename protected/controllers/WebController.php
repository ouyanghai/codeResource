<?php
class WebController extends TopController{
	public function init(){
		parent::init();
		$this->layout = "//layouts/web";
	}

	public function actionIndex(){
		$this->render("index");
	}
	/*
	public function actionGet(){
		$command = Yii::app()->db->createCommand();
		$res = $command->setText("select phones from house_broker where city='深圳'")->queryColumn();
		$str = "";
		foreach ($res as $value) {
			$str .= $value."\r\n";
		}
		file_put_contents("./phone.txt", $str);exit;
		print_r($res);exit;
	}
	*/
}

?>