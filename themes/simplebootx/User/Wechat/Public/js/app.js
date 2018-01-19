$(function(){
	$('.weui-loadmore').bind('click', function() {
		var url = $(this).data('url');
		var nextpage = $(this).data('nextpage');
		var loading = $(this).data('loading');
		if(nextpage > 0 && !loading) {
			$(this).data('loading', 1);
			$(this).text('加载中...');
			getHtml(url, 'GET', {page: nextpage}, function(response) {
	      		if(response) {
	      			$(response).appendTo("div.weui-panel__bd");
	      			$('div.weui-loadmore').data('nextpage', nextpage + 1);
	      		} else {
	      			$('.weui-loadmore').text('没有更早数据');
	      			$('.weui-loadmore').fadeOut('flow', function(){
						$('.weui-loadmore').remove();
					});
	      		}
	      	}, '', function() {
	      		$('.weui-loadmore').data('loading', 0);
	      		$('.weui-loadmore').text('加载更多');
	      	});
		}
	});
});