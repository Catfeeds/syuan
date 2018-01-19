-- 2017-06-30 add by ziqiang
ALTER TABLE  `cmf_app_api` ADD  `analog_data` TEXT NOT NULL COMMENT  '模拟数据' AFTER  `version`;
ALTER TABLE  `cmf_app_api` ADD  `oauth` TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  '是否需要登录:1是0否' AFTER  `method`;
-- 2017-07-01 add by ziqiang
ALTER TABLE  `cmf_app_device` ADD  `systype` TINYINT( 1 ) NOT NULL DEFAULT  '1' COMMENT  '操作系统类型:1Android2IOS3Winphone' AFTER  `device_token`;

ALTER TABLE  `cmf_app_device` ADD  `update_ip` VARCHAR( 15 ) NOT NULL COMMENT  '更新IP' AFTER  `create_at` ,ADD  `update_at` DATETIME NOT NULL COMMENT  '更新日期' AFTER  `update_ip`;

ALTER TABLE  `cmf_app_device` ADD  `devwidth` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '设备屏幕宽度' AFTER  `device_token` ,ADD  `devheight` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '设备屏幕高度' AFTER  `devwidth` ,ADD  `devdpi` DECIMAL( 7, 2 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  '设备屏幕DPI' AFTER  `devheight`;

ALTER TABLE `cmf_app_api_log` DROP `from`;

ALTER TABLE  `cmf_app_device` CHANGE  `did`  `did` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT  'id号';

ALTER TABLE  `cmf_app_device` DROP PRIMARY KEY ,ADD PRIMARY KEY (  `did` );

ALTER TABLE  `cmf_app_device` ADD UNIQUE (`app_id` ,`device_token` ,`systype` ,`model`);

-- 2017-07-13 add by ziqiang
CREATE TABLE IF NOT EXISTS `cmf_app_smallapp_session` (
  `sessionid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` smallint(5) NOT NULL COMMENT '应用ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `openid` varchar(32) NOT NULL COMMENT 'openid',
  `unionid` varchar(32) NOT NULL COMMENT '联合ID',
  `session_key` varchar(60) NOT NULL COMMENT '会话密钥',
  `token` char(32) NOT NULL COMMENT 'session',
  `token_expire` int(10) unsigned NOT NULL COMMENT 'token有效期',
  `create_at` datetime NOT NULL COMMENT '创建时间',
  `update_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`sessionid`),
  UNIQUE KEY `token` (`app_id` ,  `token` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='小程序session' AUTO_INCREMENT=1 ;
ALTER TABLE  `cmf_app` ADD  `small_appid` VARCHAR( 32 ) NOT NULL COMMENT  '小程序ID' AFTER  `encryption` ,ADD  `small_appsecret` VARCHAR( 32 ) NOT NULL COMMENT  '小程序密钥' AFTER  `small_appid`;