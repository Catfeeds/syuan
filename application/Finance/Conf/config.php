<?php
return array(
	'FINANCE' => array(
		'CASHOUT_FEE' => 1,//手续费百分比: %1
		'CASHOUT_BANK' => array(
			'ICBC' => '中国工商银行',
			'ABC' => '中国农业银行',
			'BOC' => '中国银行',
			'CCB' => '中国建设银行',
			'POST' => '中国邮政储蓄银行',
			'CMBC' => '中国民生银行',
			'BCOM' => '交通银行',
			'CMB' => '招商银行',
			'SPDB' => '浦发银行',
			'ALIPAY' => '支付宝',
			'WEIXINPAY' => '微信支付',
		),
		'CASHOUT_MOD_STATUS' => array(
			0 => '待审核',
			1 => '审核中',
			2 => '审核通过',
			4 => '审核失败',
		),
		'CASHOUT_PAY_STATUS' => array(
			0 => '-',
			1 => '待转账',
			2 => '已转账',
			3 => '转账失败'
		)
	)
);