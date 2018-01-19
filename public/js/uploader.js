/**
web uploader 上传方法
picker picker
field form表单字段名称
single 是否是单图上传
**/
function webupload(picker, field, single, srcs) {
	var uploadurl = GV.DIMAUB+'index.php?a=swfupload&m=asset&g=asset';
	Wind.use("webupload", "noty",function() {
		$list = $('#list1');
		var uploader = WebUploader.create({
			auto: true,
			swf: GV.JS_ROOT + 'WebUploader/Uploader.swf',
			server: uploadurl,
			fileNumLimit: single ? 1 : 100,
			fileSingleSizeLimit: 2 * 1024 * 1024,
			pick: '#' + picker,
			accept: {
				title: 'Images',
				extensions: 'gif,jpg,jpeg,bmp,png',
				mimeTypes: 'image/*'
			}
		});
		// 文件上传过程中创建进度条实时显示。
		uploader.on('uploadProgress', function(file, percentage ) {
			var $li = $( '#'+ picker);
			var $percent = $li.find('.progress .progress-bar');

			// 避免重复创建
			if ( !$percent.length ) {
				$percent = $('<div class="progress progress-striped active">' +
				  '<div class="progress-bar" role="progressbar" style="width: 0%">' +
				  '</div>' +
				'</div>').appendTo( $li ).find('.progress-bar');
			}
			$percent.css( 'width', percentage * 100 + '%' );
		});
		uploader.on('uploadSuccess', function( file , response) {
			var data = response._raw.split(',');
			if(data.length < 2 || data[0] == '0') {
				var msg = '登录已超时, 请重新登录!';
				if(data[0] == '0') {
					msg = data[1];
				}
				noty({
					type: 'error',
					text: msg,
					layout: "center"
				});
				return false;
			}
			var src = data[1];
			var wrap = $('<li class="item" id="' + file.id + '">' +
					'<input type="hidden" name="'+field+(single ? '' : '[]')+'" value="' + src + '">' +
					'<span class="view" onclick="img_priview(\'' + src + '\');"><i class="fa fa-eye" aria-hidden="true"></i></span>' +
					'<span class="delete"><i class="fa fa-trash" onclick="img_delete(\'' + file.id + '\')" aria-hidden="true"></i></span>' +
			'</li>');
			$('#'+picker).before(wrap);
			var img = $('<img src="'+src+'">');
            wrap.append( img );
			if(single && !$('#'+picker).hasClass('hide')) {
				$('#'+picker).addClass('hide');
			}
			uploader.removeFile(file);
		});

		uploader.on('uploadError', function( file ) {
			noty({
				type: 'error',
				text: '上传失败!',
				layout: "center",
				timeout: 3000
			});
		});
		uploader.on('error', function(error) {
			var msg = '';
			switch(error) {
				case 'Q_EXCEED_NUM_LIMIT':
					msg = '添加的文件总数量超出限制!';
					break;
				case 'Q_EXCEED_SIZE_LIMIT':
					msg = '上传文件总大小超过限制';
					break;
				case 'Q_TYPE_DENIED':
					msg = '上传文件类型错误!';
					break;
				case 'F_EXCEED_SIZE':
					msg = '单个图片大小超过限制, 请上传2M以内大小的图片!';
					break;
				case 'F_DUPLICATE':
					msg = '上传文件重复!';
					break;
				default:
					msg = '上传错误:' + error + '!';
			}
			noty({
				type: 'error',
				text: msg,
				layout: "center",
				timeout: 3000
			});
		});
		uploader.on( 'uploadComplete', function( file ) {
			$( '#'+picker ).find('.progress').fadeOut();
		});
		uploader.on('ready', function() {
			if(srcs) {
				var imgs = srcs.split(',');
				for(var i = 0; i < imgs.length; i++) {
					var src = imgs[i];
					var id = '';
					if(field.indexOf('[') >= 0 || field.indexOf(']') >= 0) {
						id = field.replace('[', '_');
						id = id.replace(']', '_');
						id = 'FILES_' + id + i;
					} else {
						id = 'FILES_' + field + i;
					}
					var wrap = $('<li class="item" id="' + id + '">' +
						'<input type="hidden" name="'+field+(single ? '' : '[]')+'" value="' + src + '">' +
						'<span class="view" onclick="img_priview(\'' + src + '\');"><i class="fa fa-eye" aria-hidden="true"></i></span>' +
						'<span class="delete"><i class="fa fa-trash" onclick="img_delete(\'' + id + '\')" aria-hidden="true"></i></span>' +
					'</li>');
					$('#'+picker).before(wrap);
					var img = $('<img src="'+src+'">');
					wrap.append( img );
				}
				if(single && !$('#'+picker).hasClass('hide')) {
					$('#'+picker).addClass('hide');
				}
			}
		});
	});
}

function img_priview(img) {
    if(img == ''){
        return;
    }
    Wind.use("artDialog",function(){
        art.dialog({
            title: '图片查看',
            fixed: true,
            width:"600px",
            height: '420px',
            id:"image_priview",
            lock: false,
            background:"#CCCCCC",
            opacity:0,
            content: '<img src="' + img + '" />',
            time: 5
        });
    });
}
function img_delete(id) {
	 Wind.use('artDialog', function () {
        art.dialog({
            title: false,
            icon: 'warning',
            content: '确定彻底删除文件？删除后不可恢复！',
            okVal:"确定",
            ok: function () {
            	var obj = $('#' + id);
				var src = obj.find('img').attr('src');
				$.post(GV.DIMAUB+'index.php?a=del&m=asset&g=asset', {path: src}, function(result){});
				var childnum = obj.parent().find('li').length;
				if(childnum > 2) {
					obj.remove();
				} else if(childnum == 2) {
					if(obj.next().hasClass('hide')) {
						obj.next().removeClass('hide');
					}
					obj.remove();
				}
            },
            cancelVal: '取消',
            cancel: true
        });
    });
}
