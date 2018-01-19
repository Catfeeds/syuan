function getJson(url, method, data, success, error, always, silent) {
	if(!silent) {
		$.showLoading("正在加载...");
	}
	$.ajax({
		url: url,
		cache: false,
		data: data,
		method: method,
		dataType: 'json'
	}).done(function(response) {
		$.hideLoading();
		if(response.status == 1) {
			if(typeof success == "function") {
   				success(response.info, response.url);
   			} else {
   				$.toast(response.info, '', function() {
   					if(response.url) {
	                	window.location.href = response.url;
	           		}
   				});
   			}
		} else {
			if(typeof error == "function") {
           		if(error(response.info) && response.url) {
                	window.location.href = response.url;
           		}
           	} else {
           		$.toast(response.info, 'forbidden', function() {
					if(response.url) {
	                	window.location.href = response.url;
	           		}
				});
           	}
		}
	}).fail(function() {
		$.hideLoading();
		$.toast('服务器异常', 'forbidden');
	}).always(function() {
		if(typeof always == "function") {
			always();
		}
	});
}
function getHtml(url, method, data, success, error, always, silent) {
	if(silent) {
		$.showLoading("正在加载...");
	}
	$.ajax({
		url: url,
		cache: false,
		data: data,
		method: method
	}).done(function(response) {
		$.hideLoading();
		if(response.status == 0) {
			if(typeof error == "function") {
   				if(error(response.info) && response.url) {
                	window.location.href = response.url;
           		}
   			} else {
   				$.toast(response.info, 'forbidden', function() {
					if(response.url) {
	                	window.location.href = response.url;
	           		}
				});
   			}
		} else {
            success(response);
		}
	}).fail(function() {
		$.hideLoading();
		$.toast('服务器异常', 'forbidden');
	}).always(function() {
		if(typeof always == "function") {
			always();
		}
	});
}
$(function() {
	if(typeof FastClick != 'undefined') {
		FastClick.attach(document.body);
	}
});