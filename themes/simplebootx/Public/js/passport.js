$(function () {
	var regmessage = {
		username : {focusmsg : '支持中文、字母、数字、"-" "_"的组合, 5-15个字符'},
		email : {focusmsg : '完成验证后, 可用该邮箱登录和找回账号'},
		password : {focusmsg : '建议使用字母、数字和符号两种以上的组合, 6-20个字符'},
		mobile : {focusmsg : '验证后, 可用该手机登录和找回账号'},
		mobile_verify : {focusmsg : ''}
	}
	var loginmessage = {
		username : {focusmsg : '请输入帐号'},
		password : {focusmsg : '请输入对于账号的密码'},
		verify : {focusmsg : '请输入图中验证码'}
	}
	var forgotmessage = {
		email : {focusmsg : '请输入注册时的邮箱账号'},
		verify : {focusmsg : '请输入图中验证码'}
	}
	var resetpwdmessage = {
		password : {focusmsg : '建议使用字母、数字和符号两种以上的组合, 6-20个字符'},
		repassword : {focusmsg : '请再次输入密码确认'},
		verify : {focusmsg : '请输入图中验证码'}
	}
	var regForm_list = $('.register-form');
    if(regForm_list.length) {
        Wind.use('validate', 'ajaxForm', 'noty', 'md5', function() {
			var form = $('form.register-form');
			//ie处理placeholder提交问题
			if ($.browser.msie) {
				form.find('[placeholder]').each(function() {
					var input = $(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				});
			}
			form.find('input[type!=hidden]').each(function() {
				$(this).bind('focus', function() {
					var wrap = $(this).parent().parent();
					if(wrap.hasClass('error')) {
						wrap.removeClass('error');
					}
					var fieldname = $(this).attr('name');
					$(this).siblings('span[class=help-inline]').text(regmessage[fieldname].focusmsg);
				});
				$(this).bind('blur', function() {
					var wrap = $(this).parent().parent();
					if(!wrap.hasClass('error')) {
						$(this).siblings('span[class=help-inline]').text('');
					}
				});
			});
			//表单验证开始
			form.validate({
				//是否在获取焦点时验证
				onfocusout : function(element) { $(element).valid(); },
				//是否在敲击键盘时验证
				onkeyup : false,
				//当鼠标掉级时验证
				onclick : false,
				focusInvalid: false,
				//验证错误
				showErrors : function(errorMap, errorArr) {
					for(var i = 0; i < errorArr.length; i++) {
						var obj = $(errorArr[i].element);
						var wrap = obj.parent().parent();
						if(!wrap.hasClass('error')) {
							wrap.addClass('error');
						}
						obj.siblings('span[class=help-inline]').text(errorArr[i].message);	
						if(obj.attr('name') == 'mobile') {
							$('#imgcodeModal').modal('hide');
						}
					}
				},
				//验证规则
				rules : {
					'username': {required: true,usernameValidate:true, remote: form.data('ckusernameurl')},
					'password': {required: true, passwordValidate:true},
					'email': {required:true, email:true, remote: form.data('ckemailurl')},
					'mobile': {required:true, mobileValidate:true, remote: form.data('ckmobileurl')},
					'mobile_verify': {required:true,mobile_verifyValidate:true}
				},
				messages : {
					'username' : {remote : '用户名已存在'},
					'email' : {remote : '邮箱已存在'},
					'mobile' : {remote : '手机号已存在'},
				},
				submitHandler : function(forms) {
					var btn = $('button.js-ajax-submit');
					if(btn.data("loading")) {
						return false;
					}
					btn.data("loading", true);
					var text = btn.text();
                    //按钮文案、状态修改
                    btn.text(text + '中...').prop('disabled', true).addClass('disabled');
					$(forms).ajaxSubmit({
						url : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
						dataType : 'json',
						beforeSubmit : function(arr, $form, options) {
							for(var i = 0; i < arr.length; i++) {
								if(arr[i].name == 'password') {
									arr[i].value = md5(arr[i].value);
								}
							}
						},
						success : function(data, statusText, xhr, $form) {
							btn.removeProp('disabled').removeClass('disabled');
							btn.text(btn.text().replace('中...', ''));
							btn.data("loading",false);
							if (data.state === 'success') {
								noty({text: data.info,
									type:'success',
									layout:'center'
								});
								setTimeout(function() {
									if (data.url) {
										window.location.href = data.url;
									} else {
										reloadPage(window);
									}
								}, 1500);
							} else if (data.state === 'fail') {
								noty({text: data.info,
									type:'error',
									layout:'center'
								});
							}
						}
					});
				}
			});
		});
		var regcountdown = 60; 
		function regsmstimecount(obj) { 
			if (regcountdown == 0) { 
				obj.text('获取手机验证码').prop('disabled', false).removeClass('disabled');
				obj.data("loading", false);
				regcountdown = 60; 
			} else { 
				obj.text("(" + regcountdown + "s)后重新发送"); 
				regcountdown--; 
				setTimeout(function() { 
					regsmstimecount(obj);
				}, 1000);
			} 
		}
		$('#getmobilecode').on('click', function() {
			if($('#inputMobile').parent().parent().hasClass('error')) {
				return false;
			}
			var btn = $(this);
			if(btn.data("loading")) {
				return false;
			}
			var mobile = $('#inputMobile');
			if(/^1[3|4|5|7|8]\d{9}$/.test(mobile.val())) {
				$('#imgcodebtn').data("loading", false).text('确定').prop('disabled', false).removeClass('disabled');
				$('#imgcodeverify').val('').siblings('div').text('');
				var $verify_img = $('#imgcodeverify').parent().find(".verify_img");
				if($verify_img.length){
					$verify_img.attr("src",  $verify_img.attr("src")+"&refresh="+Math.random()); 
				}
				$('#imgcodeModal').modal('show');
			} else {
				mobile.trigger('blur');
			}
		});
		$('#imgcodebtn').on('click', function() {
			var btn = $(this);
			if(btn.data("loading")) {
				return false;
			}
			btn.data("loading", true);
			var code = $('#imgcodeverify');
			if(!/^\w{4}$/.test(code.val())) {
				code.siblings('div').text('验证码错误, 请重新填写');
				code.focus();
				btn.data("loading", false);
				return false;
			}
			var oldtext = btn.text();
            btn.text('验证中...').prop('disabled', true).addClass('disabled');
			var data = {type: 1, mobile : $('#inputMobile').val(), verify: code.val()};
			$.getJSON(btn.data('url'), data, function(response) {
				btn.data("loading", false);
				btn.text(oldtext).prop('disabled', false).removeClass('disabled');
				if(response.status == 1 && response.info == 'ok') {
					$('#imgcodeModal').modal('hide');
					var sendbtn = $('#getmobilecode');
					sendbtn.text('验证码已发送').prop('disabled', true).addClass('disabled');
					sendbtn.data("loading", true);
					sendbtn.siblings('span[class=help-inline]').text('手机验证码已发送');
					sendbtn.parent().parent().removeClass('error');
					//倒计时
					regsmstimecount(sendbtn);
				} else {
					code.siblings('div').text(response.info);
					var $verify_img = code.parent().find(".verify_img");
					if($verify_img.length){
						$verify_img.attr("src",  $verify_img.attr("src")+"&refresh="+Math.random()); 
					}
					code.val('');
				}
			});
		});
		$('#imgcodeverify').on('keyup', function() {
			$(this).siblings('div').text('');
		});
    }
	var loginForm_list = $('.js-login-form');
    if (loginForm_list.length) {
        Wind.use('validate', 'ajaxForm', 'noty', 'md5', function() {
			var form = $('form.js-login-form');
			//ie处理placeholder提交问题
			if ($.browser.msie) {
				form.find('[placeholder]').each(function() {
					var input = $(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				});
			}
			form.find('input[type!=hidden]').each(function() {
				$(this).bind('focus', function() {
					var wrap = $(this).parent().parent();
					if(wrap.hasClass('error')) {
						wrap.removeClass('error');
					}
					var fieldname = $(this).attr('name');
					$(this).siblings('span[class=help-inline]').text(loginmessage[fieldname].focusmsg);
				});
				$(this).bind('blur', function() {
					var wrap = $(this).parent().parent();
					if(!wrap.hasClass('error')) {
						$(this).siblings('span[class=help-inline]').text('');
					}
				});
			});
			$.validator.addMethod("loginValidate", function(value,element,params){
				return /^[\u4e00-\u9fa5-\w]{5,15}$/.test(value) || /^1[3|4|5|7|8]\d{9}$/.test(value) || /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(value);
			}, '登录账号错误, 请使用帐号登录');
			
			//表单验证开始
			form.validate({
				//是否在获取焦点时验证
				onfocusout : function(element) { $(element).valid(); },
				//是否在敲击键盘时验证
				onkeyup : false,
				//当鼠标掉级时验证
				onclick : false,
				focusInvalid: false,
				//验证错误
				showErrors : function(errorMap, errorArr) {
					for(var i = 0; i < errorArr.length; i++) {
						var obj = $(errorArr[i].element);
						var wrap = obj.parent().parent();
						if(!wrap.hasClass('error')) {
							wrap.addClass('error');
						}
						obj.siblings('span[class=help-inline]').text(errorArr[i].message);	
					}
				},
				//验证规则
				rules : {
					'username': {required: true, loginValidate:true},
					'password': {required: true, passwordValidate:true},
					'verify': {required:true, verifyValidate:true}
				},
				submitHandler : function(forms) {
					var btn = $('button.js-ajax-submit');
					if(btn.data("loading")) {
						return false;
					}
					btn.data("loading",true);
					var text = btn.text();
                    //按钮文案、状态修改
                    btn.text(text + '中...').prop('disabled', true).addClass('disabled');
					$(forms).ajaxSubmit({
						url : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
						dataType : 'json',
						beforeSubmit : function(arr, $form, options) {
							for(var i = 0; i < arr.length; i++) {
								if(arr[i].name == 'password') {
									arr[i].value = md5(arr[i].value);
								}
							}
						},
						success : function(data, statusText, xhr, $form) {
							btn.removeProp('disabled').removeClass('disabled');
							btn.text(btn.text().replace('中...', ''));
							btn.data("loading",false);
							if (data.state === 'success') {
								noty({text: data.info,
									type:'success',
									layout:'center'
								});
								setTimeout(function() {
									if (data.url) {
										window.location.href = data.url;
									} else {
										reloadPage(window);
									}
								}, 1200);
							} else if (data.state === 'fail') {
								var $verify_img = form.find(".verify_img");
								if($verify_img.length){
									$verify_img.attr("src",  $verify_img.attr("src")+"&refresh="+Math.random()); 
								}
								var $verify_input = form.find("[name='verify']").val("");
								noty({text: data.info,
									type:'error',
									layout:'center'
								});
							}
						}
					});
				}
			});
		});
    }
	
	var forgotForm_list = $('.js-forgot-form');
    if (forgotForm_list.length) {
        Wind.use('validate', 'ajaxForm', 'noty', function() {
			var form = $('form.js-forgot-form');
			//ie处理placeholder提交问题
			if ($.browser.msie) {
				form.find('[placeholder]').each(function() {
					var input = $(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				});
			}
			form.find('input[type!=hidden]').each(function() {
				$(this).bind('focus', function() {
					var wrap = $(this).parent().parent();
					if(wrap.hasClass('error')) {
						wrap.removeClass('error');
					}
					var fieldname = $(this).attr('name');
					$(this).siblings('span[class=help-inline]').text(loginmessage[fieldname].focusmsg);
				});
				$(this).bind('blur', function() {
					var wrap = $(this).parent().parent();
					if(!wrap.hasClass('error')) {
						$(this).siblings('span[class=help-inline]').text('');
					}
				});
			});
			//表单验证开始
			form.validate({
				//是否在获取焦点时验证
				onfocusout : function(element) { $(element).valid(); },
				//是否在敲击键盘时验证
				onkeyup : false,
				//当鼠标掉级时验证
				onclick : false,
				focusInvalid: false,
				//验证错误
				showErrors : function(errorMap, errorArr) {
					for(var i = 0; i < errorArr.length; i++) {
						var obj = $(errorArr[i].element);
						var wrap = obj.parent().parent();
						if(!wrap.hasClass('error')) {
							wrap.addClass('error');
						}
						obj.siblings('span[class=help-inline]').text(errorArr[i].message);	
					}
				},
				//验证规则
				rules : {
					'email': {required: true, email:true},
					'verify': {required:true, verifyValidate:true}
				},
				submitHandler : function(forms) {
					var btn = $('button.js-ajax-submit');
					if(btn.data("loading")) {
						return false;
					}
					btn.data("loading",true);
					var text = btn.text();
                    //按钮文案、状态修改
                    btn.text('验证中...').prop('disabled', true).addClass('disabled');
					$(forms).ajaxSubmit({
						url : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
						dataType : 'json',
						success : function(data, statusText, xhr, $form) {
							btn.removeProp('disabled').removeClass('disabled');
							btn.text('确定');
							btn.data("loading",false);
							if (data.state === 'success') {
								noty({text: data.info,
									type:'success',
									layout:'center'
								});
								setTimeout(function() {
									if (data.url) {
										window.location.href = data.url;
									} else {
										reloadPage(window);
									}
								}, 2000);
							} else if (data.state === 'fail') {
								var $verify_img = form.find(".verify_img");
								if($verify_img.length){
									$verify_img.attr("src",  $verify_img.attr("src")+"&refresh="+Math.random()); 
								}
								var $verify_input = form.find("[name='verify']").val("");
								noty({text: data.info,
									type:'error',
									layout:'center'
								});
							}
						}
					});
				}
			});
		});
    }
	
	
	var resetpwdForm_list = $('.js-resetpwd-form');
    if (resetpwdForm_list.length) {
        Wind.use('validate', 'ajaxForm', 'noty', function() {
			var form = $('form.js-resetpwd-form');
			//ie处理placeholder提交问题
			if ($.browser.msie) {
				form.find('[placeholder]').each(function() {
					var input = $(this);
					if (input.val() == input.attr('placeholder')) {
						input.val('');
					}
				});
			}
			form.find('input[type!=hidden]').each(function() {
				$(this).bind('focus', function() {
					var wrap = $(this).parent().parent();
					if(wrap.hasClass('error')) {
						wrap.removeClass('error');
					}
					var fieldname = $(this).attr('name');
					$(this).siblings('span[class=help-inline]').text(loginmessage[fieldname].focusmsg);
				});
				$(this).bind('blur', function() {
					var wrap = $(this).parent().parent();
					if(!wrap.hasClass('error')) {
						$(this).siblings('span[class=help-inline]').text('');
					}
				});
			});
			//表单验证开始
			form.validate({
				//是否在获取焦点时验证
				onfocusout : function(element) { $(element).valid(); },
				//是否在敲击键盘时验证
				onkeyup : false,
				//当鼠标掉级时验证
				onclick : false,
				focusInvalid: false,
				//验证错误
				showErrors : function(errorMap, errorArr) {
					for(var i = 0; i < errorArr.length; i++) {
						var obj = $(errorArr[i].element);
						var wrap = obj.parent().parent();
						if(!wrap.hasClass('error')) {
							wrap.addClass('error');
						}
						obj.siblings('span[class=help-inline]').text(errorArr[i].message);	
					}
				},
				//验证规则
				rules : {
					'password': {required: true, passwordValidate:true},
					'repassword': {required: true, equalTo:"#inputPassword"},
					'verify': {required:true, verifyValidate:true}
				},
				submitHandler : function(forms) {
					var btn = $('button.js-ajax-submit');
					if(btn.data("loading")) {
						return false;
					}
					btn.data("loading",true);
					var text = btn.text();
                    //按钮文案、状态修改
                    btn.text('验证中...').prop('disabled', true).addClass('disabled');
					$(forms).ajaxSubmit({
						url : form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
						dataType : 'json',
						success : function(data, statusText, xhr, $form) {
							btn.removeProp('disabled').removeClass('disabled');
							btn.text('确定');
							btn.data("loading",false);
							if (data.state === 'success') {
								noty({text: data.info,
									type:'success',
									layout:'center'
								});
								setTimeout(function() {
									if (data.url) {
										window.location.href = data.url;
									} else {
										reloadPage(window);
									}
								}, 2000);
							} else if (data.state === 'fail') {
								var $verify_img = form.find(".verify_img");
								if($verify_img.length){
									$verify_img.attr("src",  $verify_img.attr("src")+"&refresh="+Math.random()); 
								}
								var $verify_input = form.find("[name='verify']").val("");
								noty({text: data.info,
									type:'error',
									layout:'center'
								});
							}
						}
					});
				}
			});
		});
    }
});