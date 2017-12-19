$(function(){
	//后台菜单的初始化
	$(".open").css("display","block");
	$(".open li").css("height","40px");

	//登录注册框的初始化
	$("#login-form,#regist-form").css("opacity",1).css("transform","scale(1.0,1.0)");

	$("#login-btn").click(function(){
		var tel = $("#login-tel").val();
		var pass = $("#login-password").val();
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/auth/login',
			data:{'tel':tel,"password":pass},
			success:function(msg){
				if(msg=='ok'){
					window.location.href="/admin/index";
					return true;
				}
				
			}
		});
	});

	$("#register-btn").click(function(){
		var name = $("#register-name").val();
		var password = $("#register-password").val();
		var repassword = $("#re-register-password").val();
		var tel = $("#register-tel").val();

		if(password=='' || repassword=='' || name=='' || tel==''){
			$("#regist_tip").text('请将信息填写完整');
			return false;
		}

		if( password!=repassword ){
			$("#regist_tip").text('密码不一致');
			return false;	
		}
		
		var preg = /^1[34578]\d{9}$/;
		if(!preg.test(tel)){
			$("#regist_tip").text('手机号码格式错误');
			return false;		
		}
		
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/admin/doregister',
			data:{'username':name,"password":password,"repassword":repassword,"tel":tel},
			success:function(msg){
				if(msg=='ok'){
					window.location.href="/admin/loginpage";
					return true;
				}
				$("#regist_tip").text(msg);
			},
			error:function(data){
				alert('网络错误');
				console.log(data);
			}
		});
	});
	$('.menu-li-f').click(function(){
		var ul = $(this).find('ul');
		
		if(ul.css("display")=="block"){
			ul.find('li').animate({height:"0px"},function(){
				ul.hide();	
			});
		}else{
			ul.show();
			ul.find('li').animate({height:"40px"});
		}
		
	});
	$("#modify-btn").click(function(){
		var password = $("#login-password").val();
		var newpass = $("#new-password").val();
		var repass = $("#re-new-password").val();
		if(password=='' || newpass==''){
			return false;
		}
		if(newpass!=repass){
			$("#modify-tip").html("新密码两次输入不一致");
		}
		$.ajax({
			dataType:'json',
			type:'post',
			data:{"password":password,"newpass":newpass,"repass":repass},
			url:'/admin/domodifypass',
			success:function(msg){
				$(".modify-form")[0].reset();
				$("#modify-tip").html(msg);
				window.location.href="/admin/index";
			},
			error:function(msg){
				alert('网络错误');
			}
		});
	});
	$("#modify-cancel").click(function(){
		window.location.href="/admin/index";
	});
	$("#mod-secret-btn").click(function(){
		var secret = $("#new-secret").val();
		if(secret==''){
			return alert("新secret不能为空");
		}
		if(secret.length>8){
			return alert("新secret长度不能超过8位");
		}
		$.ajax({
			type:'post',
			dataType:'json',
			url:'/admin/modsecret',
			data:{"secret":secret},
			success:function(msg){
				if(msg.status=='error'){
					return false;
				}
			$("#user-secret").text(secret);
			$("#mod-secret-input").hide();			
			}
		});
	});
});
function modSecret(){
	$("#mod-secret-input").show();
}

function logout(){
	$.ajax({
		type:'post',
		dataType:'json',
		url:'/admin/logout',
		data:{},
		success:function(msg){
			if(msg.status=='error'){
				return false;
			}
			window.location.href="/web/index";
		}
	});
}