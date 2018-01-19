/**
多图文上传完回调方法
dialogid
dialogname 名称
single 是否多图文
**/
function wechatArticle(dialogid, dialogname, single) {
    Wind.use("artDialog","iframeTools",function() {
    	var args = '&single=' + (single ? '1' : 0) + '&dialogid' + dialogid;
        art.dialog.open(GV.DIMAUB+'index.php?a=wechat&m=AdminPost&g=Portal' + args, {
			title: dialogname,
			left: '5%',
			id: dialogid,
			width: '650px',
			height: '500px',
			lock: false,
			fixed: true,
			background:"#CCCCCC",
			opacity:0
		});
    });
}

/**
 * 插入多图文
 */
function multArticleInsert(data) {
	var aid = data.from + data.oid;
	if($('ul[class="multiarticle"]').children('li[aid="'+aid+'"]').length > 0) {
		return false;
	}
	var wrap = $('<li class="item" aid="' + aid + '" oid="'+data.oid+'" from="'+data.from+'" title="'+data.title+'" subtitle="">' +
					'<img src="' + data.thumb+ '" />' +
					'<p>'+data.title+'</p>' +
					'<span class="delete"><i class="fa fa-trash" onclick="multiarticle_delete(\'' + aid + '\')" aria-hidden="true"></i></span>' +
				'</li>');
	$('ul[class="multiarticle"]').append(wrap);
}

/**
 * 删除多图文
 */
function multiarticle_delete(aid) {
	$('ul[class="multiarticle"]').children('li[aid="'+aid+'"]').remove();
}
/**
 * 插入单图文
 */
function singleArticleInsert(data) {
	var aid = data.from + data.oid;
	$('ul[class="singlearticle"]').html('<li class="item" aid="' + aid + '" oid="'+data.oid+'" from="'+data.from+'" title="'+data.title+'" subtitle="'+data.subtitle+'">' +
			'<h4>'+data.title+'</h4>' + 
			'<img src="' + data.thumb+ '" />' +
			'<p>'+data.subtitle+'</p><hr>' +
			'<span>阅读原文</span>' +
		'</li>');
}