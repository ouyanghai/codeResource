<div class="header">
	<div class="user_logo">zhaoqiye.net</div>
	<div class="user_info">
		<span>欢迎您:<?php echo Yii::app()->user->nick; ?></span>&nbsp;&nbsp;
		<a onclick="javascript:logout();">退出</a>
	</div>
</div>
<div style="width:100%;height:100%;margin-top:80px;">
	<div class="main-info">
		<div class='main-info-list'>
			<span class="info-flag">个人信息&nbsp;<a href="<?php echo $this->createUrl('/admin/modifypass'); ?>" style='font-size:16px;color:#AAB5BC'>修改</a></span>
			<span class="info-con"><font color="#676767">昵称:</font><?php echo Yii::app()->user->nick; ?></span>
			<span class="info-con"><font color="#676767">手机:</font><?php echo Yii::app()->user->tel; ?></span>
			
		</div>
		<div class='main-info-list'>
			<span class="info-flag">订单信息</span>
			<span class="info-con"><font color="#676767">版本:</font><?php echo $this->grade[Yii::app()->user->level]; ?></span>
			<span class="info-con"><font color="#676767">总数量:</font><?php echo Yii::app()->user->sum; ?></span>
			<span class="info-con"><font color="#676767">未使用:</font><?php echo Yii::app()->user->remain; ?></span>
			<span class="info-con"><font color="#676767">有效期至:</font><?php echo date('Y-m-d',strtotime(Yii::app()->user->deadline)); ?></span>
			<!--<span class="info-con"><font color="#676767">充值:</font>请联系微信客服:</span>-->
		</div>
		<div class='main-info-list'>
			<span class="info-flag">接口信息</span>
			<span class="info-con"><font color="#676767">用户ID:</font><?php echo Yii::app()->user->uid; ?></span>
			<span class="info-con">
				<font color="#676767">secret:</font><label id="user-secret"><?php echo Yii::app()->user->secret; ?></label>
				<a style="display:inline-block;margin-left:50px;color:#666;" href="javascript:modSecret();">修改</a>
			</span>
			<span class="info-con" id="mod-secret-input">
				<input type="text" id="new-secret" placeholder="请输入新secret" />
				<input type="button" id="mod-secret-btn" value="确定" />
			</span>
		</div>
	</div>

	<div id="direction">
		<span id="direction-title">接口说明</span>
		<div id="direction-content">
			<h3>1.请求地址</h3>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo "http://".$_SERVER['HTTP_HOST']."/ytx/getinfo"; ?></p>

			<h3>2.请求方法</h3>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;GET</p>

			<h3>3.请求参数</h3>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;id:用户ID</p>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;tid:地区ID (获取地区列表地址:<?php echo "http://".$_SERVER['HTTP_HOST']."/ytx/city"; ?>)</p>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;cid:行业ID (获取地区列表地址:<?php echo "http://".$_SERVER['HTTP_HOST']."/ytx/category"; ?>)</p>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;offset:搜索结果的索引</p>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;timestamp:时间戳</p>								
			<p>&nbsp;&nbsp;&nbsp;&nbsp;token:生成方式如:md5(id+timestamp+secret)</p>

			<h3>4.响应结果</h3>
			<p>&nbsp;&nbsp;&nbsp;&nbsp;json数组，如：["11111","22222"]</p>
		</div>
	</div>
</div>