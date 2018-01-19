$(function() {
	$('.theme-login.sign-btn').bind('click', function() {
		$('.theme-popover').slideUp(200);
		if($('div.share-tip').hasClass('hidden')) {
			$('div.share-tip').addClass('hidden');
		}
		$('.theme-popover-mask').fadeIn(800);
		$('.theme-popover.sign').slideDown(200);
	});
	$('a.btn-sign-again').bind('click', function() {
		$('.theme-popover-mask').fadeOut(10);
		$('.theme-popover').slideUp(10);
		$('.theme-popover').slideUp(200);
		if(!$('div.getup-tip').hasClass('hidden')) {
			$('div.getup-tip').addClass('hidden');
		}
		if($('div.share-tip').hasClass('hidden')) {
			$('div.share-tip').addClass('hidden');
		}
		$('.theme-popover-mask').fadeIn(800);
		$('.theme-popover.sign').slideDown(200);
	});

	$('.theme-login.getup-btn').bind('click', function() {
		if($('a.close').hasClass('doreload')) {
			$('a.close').removeClass('doreload')
		}
		$('.theme-popover').slideUp(200);
		if($('div.share-tip').hasClass('hidden')) {
			$('div.share-tip').addClass('hidden');
		}
		if(!$(this).hasClass('weui-btn_loading')) {
			$(this).addClass('weui-btn_loading');
			getJson("/Activity/Morning/getup?t=" + Math.random(), 'POST', {}, function(info, url) {
				if($('div.getup-tip').hasClass('hidden')) {
					$('div.getup-tip').removeClass('hidden');
				}
				$('a.close').addClass('doreload');
				$('div.getup-tip > span > span').text(info);
				$('div.header div.myrecord strong').text(info);
				$('.theme-popover-mask').fadeIn(800);
				$('.theme-popover.getup-success').slideDown(200);
				$('.theme-login.getup-btn').addClass('hidden');
				$('.theme-login.sign-btn').removeClass('hidden');
			}, function(info) {
				$.toptip(info, 2000, 'warning');
				return true;
			}, function() {
				$('.theme-login.getup-btn').removeClass('weui-btn_loading');
			});
		}
	});
	
	$('div.theme-popover a.btn-share').bind('click', function() {
		if(!$('div.getup-tip').hasClass('hidden')) {
			$('div.getup-tip').addClass('hidden');
		}
		$(this).parent().parent().find('div.share-tip').removeClass('hidden');
	});
	$('a.btn-pay-sign').bind('click', function() {
		if(!$(this).hasClass('weui-btn_loading')) {
			$(this).addClass('weui-btn_loading');
			if($('a.close').hasClass('doreload')) {
				$('a.close').removeClass('doreload')
			}
			$('.theme-poptit .close,.theme-popover-mask').trigger('click');
			getJson("/Activity/Morning/sign?t=" + Math.random(), 'POST', {}, function(info, url) {
				window.location.href = url;
			}, '', function() {
				$('a.btn-pay-sign').removeClass('weui-btn_loading');
			});
		}
	});
	$('div.header > div.container > div.weui-flex div.weui-flex__item div.myrecord > span').bind('click', function(){
		$.toast("连续早起可收集太阳", "text");
	});
	$('.theme-poptit .close,.theme-popover-mask').bind('click', function() {
		if($(this).hasClass('doreload')) {
			window.location.reload();
		}
		$('.theme-popover-mask').fadeOut(100);
		$('.theme-popover').slideUp(200);
	});

	$('div.wrap-more a.loading-more').bind('click', function() {
		if(!$(this).hasClass('weui-btn_loading')) {
			$(this).addClass('weui-btn_loading');
			var point = $(this).attr('point');
			var sign_at = $(this).attr('sign_at');
			getJson("/Activity/Morning/getuplist?t=" + Math.random(), 'POST', {sign_at: sign_at, point: point}, function(info, url) {
				if(info.html != '') {
					$(info.html).insertBefore('div.wrap > div.wrap-ul > ul > li.ul-start-li');
					$('div.wrap-more a.loading-more').attr('point', info.point);
				} 
				if(info.total == 0) {
					$(this).addClass('weui-btn_loading');
					$('div.wrap-more').text('没有更早的打卡记录');
				}
			}, '', function() {
				$('div.wrap-more a.loading-more').removeClass('weui-btn_loading');
			});
		}
	});
	$('div.wrap-more a.loading-more').trigger('click');


	if($('div.personal > div.calendar').length > 0) {
		$('div.personal > div.calendar').removeClass('hidden');
		var nowyear = $('div.personal > div.calendar div.weui-flex__item.month').attr('year');
		var nowmonth = $('div.personal > div.calendar div.weui-flex__item.month').attr('month');
		refreshCalendar();
		$('div.personal > div.calendar div.weui-flex__item.left').bind('click', function() {
			var year = $('div.personal > div.calendar div.weui-flex__item.month').attr('year');
			var month = $('div.personal > div.calendar div.weui-flex__item.month').attr('month');
			if(month == 1) {
				year = year - 1;
				month = 12;
			} else {
				month = month - 1;
			}
			$('div.personal > div.calendar div.weui-flex__item.month').attr('year', year);
			$('div.personal > div.calendar div.weui-flex__item.month').attr('month', month);
			$('div.personal > div.calendar div.weui-flex__item.month').text(month + '月');
			refreshCalendar();
			if(year == nowyear && month == nowmonth) {
				$('div.personal > div.calendar div.weui-flex__item.right').text('');
			} else {
				$('div.personal > div.calendar div.weui-flex__item.right').text('〉');
			}
		});
		$('div.personal > div.calendar div.weui-flex__item.right').bind('click', function() {
			var text = $(this).text();
			if(text.length > 0) {
				var year = $('div.personal > div.calendar div.weui-flex__item.month').attr('year');
				var month = $('div.personal > div.calendar div.weui-flex__item.month').attr('month');
				if(month == 12) {
					year = year -1 + 2;
					month = 1;
				} else {
					month = month -1 + 2;
				}
				$('div.personal > div.calendar div.weui-flex__item.month').attr('year', year);
				$('div.personal > div.calendar div.weui-flex__item.month').attr('month', month);
				$('div.personal > div.calendar div.weui-flex__item.month').text(month + '月');
				refreshCalendar();
				if(year == nowyear && month == nowmonth) {
					$('div.personal > div.calendar div.weui-flex__item.right').text('');
				}
			}
		});
		$('div.personal > div.calendar div.weui-flex.week > div.weui-flex__item').bind('click', function() {
			if($(this).hasClass('success')) {
				if(!$('div.personal > div.personal-more').hasClass('hidden')) {
					$('div.personal > div.personal-more').addClass('hidden');
				}
				$('div.personal > div.calendar-result').removeClass('hidden');
				$('div.personal > div.calendar-result div.weui-flex');
				$('div.personal > div.calendar-result div.right').removeClass('fail');
				if(!$('div.personal > div.calendar-result div.right').hasClass('success')) {
					$('div.personal > div.calendar-result div.right').addClass('success');
				}
				$('div.personal > div.calendar-result div.right').text('挑战成功');
				$('div.personal > div.calendar-result div.weui-flex div.weui-flex__item p').text('我在' + $(this).attr('waketime') + '分起床, 连续早起' + $(this).attr('days') + '天啦!');
			} else if($(this).hasClass('fail')) {
				if(!$('div.personal > div.personal-more').hasClass('hidden')) {
					$('div.personal > div.personal-more').addClass('hidden');
				}
				$('div.personal > div.calendar-result').removeClass('hidden');
				$('div.personal > div.calendar-result div.weui-flex');
				$('div.personal > div.calendar-result div.right').removeClass('success');
				if(!$('div.personal > div.calendar-result div.right').hasClass('fail')) {
					$('div.personal > div.calendar-result div.right').addClass('fail');
				}
				$('div.personal > div.calendar-result div.right').text('挑战失败');
				$('div.personal > div.calendar-result div.weui-flex div.weui-flex__item p').text($(this).attr('total') + '人瓜分了我的挑战金!');
			} else {
				if(!$('div.personal > div.calendar-result').hasClass('hidden')) {
					$('div.personal > div.calendar-result').addClass('hidden');
				}
				$('div.personal > div.personal-more').removeClass('hidden');
			}
			if(!$(this).hasClass('focus')) {
				$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item.focus').removeClass('focus');
				$(this).addClass('focus');
			}
		});
		setTimeout(function() {
			$('div.personal > div.calendar div.weui-flex.week > div.weui-flex__item.item' + (new Date()).getDate()).trigger('click');
		}, 1000);
	}
});

function refreshCalendar() {
	$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item').removeClass('focus');
	$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item').removeClass('success');
	$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item').removeClass('fail');
	$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item').attr('days', '');
	$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item').attr('total', '');
	$('div.personal > div.calendar div.weui-flex.week div.weui-flex__item').attr('waketime', '');
	var year = $('div.personal > div.calendar div.weui-flex__item.month').attr('year');
	var month = $('div.personal > div.calendar div.weui-flex__item.month').attr('month');
	var curMonthDays = new Date(year, month, 0).getDate();
	var fiveweek = false;
	if(curMonthDays / 7 > 4) {
		fiveweek = true;
	}
	var obj = $('div.personal > div.calendar div.weui-flex.week5');
	if(fiveweek) {
		if(obj.hasClass('hidden')) {
			obj.removeClass('hidden');
		}
		for(var i = 29; i <= 31; i++) {
			obj.find('div.item'+i + ' span').text('');
		}
		for(var i = 29; i <= curMonthDays; i++) {
			obj.find('div.item'+i + ' span').text(i);
		}
	} else {
		if(!obj.hasClass('hidden')) {
			obj.addClass('hidden');
		}
	}
	getJson("/Activity/Morning/monthlist?t=" + Math.random(), 'POST', {year: year, month: month}, function(info, url) {
		if(info.list) {
			for(var i in info.list) {
				var item = $('div.personal > div.calendar div.weui-flex.week div.weui-flex__item.item'+i);
				if(info.list[i].success == 1) {
					item.attr('waketime', info.list[i].waketime);
					item.attr('days', info.list[i].days);
					item.addClass('success');
				} else {
					item.attr('total', info.list[i].total);
					item.addClass('fail');
				}
			}	
		}
	}, '', '', true);
}
