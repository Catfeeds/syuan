SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- 数据库: `cms`
-- 

--
-- 表的结构 `cmf_app`
--

DROP TABLE IF EXISTS `cmf_app`;
CREATE TABLE IF NOT EXISTS `cmf_app` (
  `app_id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL COMMENT 'APP类型: 1.Android 2.IOS 3.WinPhone 4.WebApp',
  `name` varchar(20) NOT NULL COMMENT 'app类型名称',
  `introduce` text NOT NULL COMMENT '应用介绍',
  `encryption` tinyint(1) NOT NULL COMMENT '是否加密1加密0不加密',
  `apilog` tinyint(1) NOT NULL DEFAULT '0' COMMENT '记录api调用日志1记录0不记录',
  `key` varchar(32) NOT NULL COMMENT '加密key',
  `apk_url` varchar(255) NOT NULL COMMENT '应用地址',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1正常0删除',
  `update_at` datetime NOT NULL,
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='客户端表';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_api`
--

DROP TABLE IF EXISTS `cmf_app_api`;
CREATE TABLE IF NOT EXISTS `cmf_app_api` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(10) NOT NULL DEFAULT '0' COMMENT '所属分组',
  `name` varchar(30) NOT NULL COMMENT 'API名称',
  `path` varchar(80) NOT NULL COMMENT '接口uri路径: module/controller/action',
  `method` enum('GET','PUT','POST','DELETE') NOT NULL DEFAULT 'GET' COMMENT '请求方法',
  `introduce` text NOT NULL COMMENT 'API功能描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '接口状态1启用0禁用',
  `warning` varchar(50) NOT NULL DEFAULT '' COMMENT '接口提示',
  `version` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT '接口版本',
  PRIMARY KEY (`id`),
  KEY `path` (`path`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='API接口表';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_api_group`
--

DROP TABLE IF EXISTS `cmf_app_api_group`;
CREATE TABLE IF NOT EXISTS `cmf_app_api_group` (
  `gid` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '分组名称',
  `introduce` text NOT NULL COMMENT '介绍',
  `listorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='API分组表';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_api_log`
--

DROP TABLE IF EXISTS `cmf_app_api_log`;
CREATE TABLE IF NOT EXISTS `cmf_app_api_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `api_path` varchar(50) NOT NULL COMMENT '接口路径',
  `api_version` smallint(5) unsigned NOT NULL COMMENT '接口版本号',
  `data` text COMMENT '请求数据',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `did` char(32) NOT NULL COMMENT '设备ID',
  `app_id` smallint(5) unsigned NOT NULL,
  `version_code` varchar(10) NOT NULL,
  `model` char(12) NOT NULL,
  `language` char(6) NOT NULL,
  `network` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `from` char(12) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action` (`api_path`,`create_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统日志';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_api_params`
--

DROP TABLE IF EXISTS `cmf_app_api_params`;
CREATE TABLE IF NOT EXISTS `cmf_app_api_params` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL COMMENT '关联的API的ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '参数名称',
  `type` varchar(20) NOT NULL COMMENT '参数类型',
  `must` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否必须1必须0不必须',
  `default` varchar(100) NOT NULL COMMENT '字段默认值',
  `introduce` varchar(255) NOT NULL COMMENT '字段说明',
  `listorder` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `api_id` (`api_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='请求接口需求参数说明';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_api_response`
--

DROP TABLE IF EXISTS `cmf_app_api_response`;
CREATE TABLE IF NOT EXISTS `cmf_app_api_response` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL COMMENT '关联的API的ID',
  `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '字段名称',
  `type` varchar(20) NOT NULL COMMENT '字段类型',
  `introduce` varchar(255) NOT NULL COMMENT '字段说明',
  `listorder` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  KEY `api_id` (`api_id`,`parentid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='接口返回参数规则说明';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_device`
--

DROP TABLE IF EXISTS `cmf_app_device`;
CREATE TABLE IF NOT EXISTS `cmf_app_device` (
  `did` char(32) NOT NULL COMMENT 'id号',
  `app_id` smallint(5) unsigned NOT NULL COMMENT '应用ID号',
  `version_code` varchar(10) NOT NULL COMMENT '应用版本号',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登陆用户ID',
  `access_token` varchar(32) NOT NULL COMMENT '用户认证TOKEN',
  `token_expire` int(10) unsigned NOT NULL COMMENT 'TOKEN有效期',
  `device_name` char(24) NOT NULL COMMENT '设备名称',
  `device_token` char(64) NOT NULL COMMENT '设备TOKEN',
  `sysversion` char(12) NOT NULL COMMENT '系统版本号',
  `model` char(30) NOT NULL COMMENT '设备型号',
  `from` char(12) NOT NULL COMMENT '安装来源',
  `language` char(40) NOT NULL COMMENT '系统语言',
  `create_ip` varchar(15) NOT NULL DEFAULT '0' COMMENT '注册IP地址',
  `create_at` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`did`,`app_id`),
  KEY `memberid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户设备管理';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_error_log`
--

DROP TABLE IF EXISTS `cmf_app_error_log`;
CREATE TABLE IF NOT EXISTS `cmf_app_error_log` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `appid` smallint(5) unsigned NOT NULL,
  `did` smallint(5) unsigned NOT NULL,
  `version_code` varchar(10) NOT NULL,
  `error` text NOT NULL,
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='app错误日志';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_push_message`
--

DROP TABLE IF EXISTS `cmf_app_push_message`;
CREATE TABLE IF NOT EXISTS `cmf_app_push_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `moduleid` int(10) unsigned NOT NULL DEFAULT '0',
  `appid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1资讯，2爆料审核通过',
  `contentid` int(10) unsigned NOT NULL DEFAULT '0',
  `content` char(100) NOT NULL,
  `success` int(10) NOT NULL,
  `lastsendid` int(10) unsigned NOT NULL DEFAULT '0',
  `fail` int(10) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0,未发送,1正在发送,2发送完成',
  `memberid` char(100) NOT NULL COMMENT '要发送给的用户，用，号分开，最多10个',
  `bak` char(128) NOT NULL,
  `sendtime` int(10) unsigned NOT NULL,
  `lastupdatetime` int(10) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`status`),
  KEY `type` (`appid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_user_oauth`
--

DROP TABLE IF EXISTS `cmf_app_user_oauth`;
CREATE TABLE IF NOT EXISTS `cmf_app_user_oauth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id号',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次登陆用户ID',
  `did` char(32) NOT NULL COMMENT '设备ID',
  `access_token` char(32) NOT NULL COMMENT '用户认证TOKEN',
  `token_expire` int(10) unsigned NOT NULL COMMENT 'TOKEN有效期',
  PRIMARY KEY (`id`),
  KEY `memberid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户设备管理';

-- --------------------------------------------------------

--
-- 表的结构 `cmf_app_version_upgrade`
--

DROP TABLE IF EXISTS `cmf_app_version_upgrade`;
CREATE TABLE IF NOT EXISTS `cmf_app_version_upgrade` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` smallint(5) unsigned NOT NULL COMMENT '客户的设备ID：安卓、pad、IPhone',
  `version_code` varchar(10) NOT NULL COMMENT '版本标志，1.2',
  `type` tinyint(2) unsigned NOT NULL COMMENT '是否升级1升级0不升级2强制升级',
  `apk_url` varchar(255) NOT NULL COMMENT '升级地址',
  `upgrade_point` varchar(255) NOT NULL COMMENT '升级提示',
  `confirm_title` varchar(30) NOT NULL DEFAULT '立刻升级' COMMENT '确认提示问题',
  `cancel_title` varchar(30) NOT NULL DEFAULT '稍后再说' COMMENT '取消提示文字',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0待发布1已发布-1下线',
  `mark` text NOT NULL COMMENT '升级内容',
  `create_at` datetime NOT NULL,
  `update_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='版本升级信息表';

-- --------------------------------------------------------
--
-- 表的结构 `cmf_ad`
--

DROP TABLE IF EXISTS `cmf_ad`;
CREATE TABLE IF NOT EXISTS `cmf_ad` (
  `ad_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '广告id',
  `ad_name` varchar(255) NOT NULL COMMENT '广告名称',
  `ad_content` text COMMENT '广告内容',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1显示，0不显示',
  PRIMARY KEY (`ad_id`),
  KEY `ad_name` (`ad_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- 表的结构 `cmf_asset`
--

DROP TABLE IF EXISTS `cmf_asset`;
CREATE TABLE IF NOT EXISTS `cmf_asset` (
  `aid` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户 id',
  `key` varchar(50) NOT NULL COMMENT '资源 key',
  `filename` varchar(50) DEFAULT NULL COMMENT '文件名',
  `filesize` int(11) DEFAULT NULL COMMENT '文件大小,单位Byte',
  `filepath` varchar(200) NOT NULL COMMENT '文件路径，相对于 upload 目录，可以为 url',
  `uploadtime` int(11) NOT NULL COMMENT '上传时间',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1：可用，0：删除，不可用',
  `meta` text COMMENT '其它详细信息，JSON格式',
  `suffix` varchar(50) DEFAULT NULL COMMENT '文件后缀名，不包括点',
  `download_times` int(11) NOT NULL DEFAULT '0' COMMENT '下载次数',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='资源表';



--
-- 表的结构 `cmf_auth_access`
--

DROP TABLE IF EXISTS `cmf_auth_access`;
CREATE TABLE IF NOT EXISTS `cmf_auth_access` (
  `role_id` mediumint(8) unsigned NOT NULL COMMENT '角色',
  `rule_name` varchar(255) NOT NULL COMMENT '规则唯一英文标识,全小写',
  `type` varchar(30) DEFAULT NULL COMMENT '权限规则分类，请加应用前缀,如admin_',
  KEY `role_id` (`role_id`),
  KEY `rule_name` (`rule_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限授权表';



--
-- 表的结构 `cmf_auth_rule`
--

DROP TABLE IF EXISTS `cmf_auth_rule`;
CREATE TABLE IF NOT EXISTS `cmf_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` varchar(30) NOT NULL DEFAULT '1' COMMENT '权限规则分类，请加应用前缀,如admin_',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识,全小写',
  `param` varchar(255) DEFAULT NULL COMMENT '额外url参数',
  `title` varchar(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`status`,`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='权限规则表';



--
-- 表的结构 `cmf_comments`
--

DROP TABLE IF EXISTS `cmf_comments`;
CREATE TABLE IF NOT EXISTS `cmf_comments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_table` varchar(100) NOT NULL COMMENT '评论内容所在表，不带表前缀',
  `post_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论内容 id',
  `url` varchar(255) DEFAULT NULL COMMENT '原文地址',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '发表评论的用户id',
  `to_uid` int(11) NOT NULL DEFAULT '0' COMMENT '被评论的用户id',
  `full_name` varchar(50) DEFAULT NULL COMMENT '评论者昵称',
  `email` varchar(255) DEFAULT NULL COMMENT '评论者邮箱',
  `createtime` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '评论时间',
  `content` text NOT NULL COMMENT '评论内容',
  `type` smallint(1) NOT NULL DEFAULT '1' COMMENT '评论类型；1实名评论',
  `parentid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '被回复的评论id',
  `path` varchar(500) DEFAULT NULL,
  `status` smallint(1) NOT NULL DEFAULT '1' COMMENT '状态，1已审核，0未审核',
  PRIMARY KEY (`id`),
  KEY `comment_post_ID` (`post_id`),
  KEY `comment_approved_date_gmt` (`status`),
  KEY `comment_parent` (`parentid`),
  KEY `table_id_status` (`post_table`,`post_id`,`status`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='评论表';



--
-- 表的结构 `cmf_common_action_log`
--

DROP TABLE IF EXISTS `cmf_common_action_log`;
CREATE TABLE IF NOT EXISTS `cmf_common_action_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` bigint(20) DEFAULT '0' COMMENT '用户id',
  `object` varchar(100) DEFAULT NULL COMMENT '访问对象的id,格式：不带前缀的表名+id;如posts1表示xx_posts表里id为1的记录',
  `action` varchar(50) DEFAULT NULL COMMENT '操作名称；格式规定为：应用名+控制器+操作名；也可自己定义格式只要不发生冲突且惟一；',
  `count` int(11) DEFAULT '0' COMMENT '访问次数',
  `last_time` int(11) DEFAULT '0' COMMENT '最后访问的时间戳',
  `ip` varchar(15) DEFAULT NULL COMMENT '访问者最后访问ip',
  PRIMARY KEY (`id`),
  KEY `user_object_action` (`user`,`object`,`action`),
  KEY `user_object_action_ip` (`user`,`object`,`action`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问记录表';



--
-- 表的结构 `cmf_guestbook`
--

DROP TABLE IF EXISTS `cmf_guestbook`;
CREATE TABLE IF NOT EXISTS `cmf_guestbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(50) NOT NULL COMMENT '留言者姓名',
  `email` varchar(100) NOT NULL COMMENT '留言者邮箱',
  `title` varchar(255) DEFAULT NULL COMMENT '留言标题',
  `msg` text NOT NULL COMMENT '留言内容',
  `createtime` datetime NOT NULL COMMENT '留言时间',
  `status` smallint(2) NOT NULL DEFAULT '1' COMMENT '留言状态，1：正常，0：删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='留言表';



--
-- 表的结构 `cmf_links`
--

DROP TABLE IF EXISTS `cmf_links`;
CREATE TABLE IF NOT EXISTS `cmf_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) NOT NULL COMMENT '友情链接地址',
  `link_name` varchar(255) NOT NULL COMMENT '友情链接名称',
  `link_image` varchar(255) DEFAULT NULL COMMENT '友情链接图标',
  `link_target` varchar(25) NOT NULL DEFAULT '_blank' COMMENT '友情链接打开方式',
  `link_description` text NOT NULL COMMENT '友情链接描述',
  `link_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1显示，0不显示',
  `link_rating` int(11) NOT NULL DEFAULT '0' COMMENT '友情链接评级',
  `link_rel` varchar(255) DEFAULT NULL COMMENT '链接与网站的关系',
  `listorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='友情链接表';



--
-- 表的结构 `cmf_menu`
--

DROP TABLE IF EXISTS `cmf_menu`;
CREATE TABLE IF NOT EXISTS `cmf_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` smallint(6) unsigned NOT NULL DEFAULT '0',
  `app` char(20) NOT NULL COMMENT '应用名称app',
  `model` char(20) NOT NULL COMMENT '控制器',
  `action` char(20) NOT NULL COMMENT '操作名称',
  `data` char(50) NOT NULL COMMENT '额外参数',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '菜单类型  1：权限认证+菜单；0：只作为菜单',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态，1显示，0不显示',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `icon` varchar(50) DEFAULT NULL COMMENT '菜单图标',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序ID',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `parentid` (`parentid`),
  KEY `model` (`model`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='后台菜单表';



--
-- 表的结构 `cmf_nav`
--

DROP TABLE IF EXISTS `cmf_nav`;
CREATE TABLE IF NOT EXISTS `cmf_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL COMMENT '导航分类 id',
  `parentid` int(11) NOT NULL COMMENT '导航父 id',
  `label` varchar(255) NOT NULL COMMENT '导航标题',
  `target` varchar(50) DEFAULT NULL COMMENT '打开方式',
  `href` varchar(255) NOT NULL COMMENT '导航链接',
  `icon` varchar(255) NOT NULL COMMENT '导航图标',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1显示，0不显示',
  `listorder` int(6) DEFAULT '0' COMMENT '排序',
  `path` varchar(255) NOT NULL DEFAULT '0' COMMENT '层级关系',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='前台导航表';



--
-- 表的结构 `cmf_nav_cat`
--

DROP TABLE IF EXISTS `cmf_nav_cat`;
CREATE TABLE IF NOT EXISTS `cmf_nav_cat` (
  `navcid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '导航分类名',
  `active` int(1) NOT NULL DEFAULT '1' COMMENT '是否为主菜单，1是，0不是',
  `remark` text COMMENT '备注',
  PRIMARY KEY (`navcid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='前台导航分类表';



--
-- 表的结构 `cmf_oauth_user`
--

DROP TABLE IF EXISTS `cmf_oauth_user`;
CREATE TABLE IF NOT EXISTS `cmf_oauth_user` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `from` varchar(20) NOT NULL COMMENT '用户来源key',
  `name` varchar(30) NOT NULL COMMENT '第三方昵称',
  `head_img` varchar(200) NOT NULL COMMENT '头像',
  `uid` int(20) NOT NULL COMMENT '关联的本站用户id',
  `create_time` datetime NOT NULL COMMENT '绑定时间',
  `last_login_time` datetime NOT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(16) NOT NULL COMMENT '最后登录ip',
  `login_times` int(6) NOT NULL COMMENT '登录次数',
  `status` tinyint(2) NOT NULL,
  `access_token` varchar(512) NOT NULL,
  `expires_date` int(11) NOT NULL COMMENT 'access_token过期时间',
  `openid` varchar(40) NOT NULL COMMENT '第三方用户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='第三方用户表';



--
-- 表的结构 `cmf_options`
--

DROP TABLE IF EXISTS `cmf_options`;
CREATE TABLE IF NOT EXISTS `cmf_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(64) NOT NULL COMMENT '配置名',
  `option_value` longtext NOT NULL COMMENT '配置值',
  `autoload` int(2) NOT NULL DEFAULT '1' COMMENT '是否自动加载',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='全站配置表';



--
-- 表的结构 `cmf_plugins`
--

DROP TABLE IF EXISTS `cmf_plugins`;
CREATE TABLE IF NOT EXISTS `cmf_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `name` varchar(50) NOT NULL COMMENT '插件名，英文',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '插件名称',
  `description` text COMMENT '插件描述',
  `type` tinyint(2) DEFAULT '0' COMMENT '插件类型, 1:网站；8;微信',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态；1开启；',
  `config` text COMMENT '插件配置',
  `hooks` varchar(255) DEFAULT NULL COMMENT '实现的钩子;以“，”分隔',
  `has_admin` tinyint(2) DEFAULT '0' COMMENT '插件是否有后台管理界面',
  `author` varchar(50) DEFAULT '' COMMENT '插件作者',
  `version` varchar(20) DEFAULT '' COMMENT '插件版本号',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '插件安装时间',
  `listorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='插件表';



--
-- 表的结构 `cmf_posts`
--

DROP TABLE IF EXISTS `cmf_posts`;
CREATE TABLE IF NOT EXISTS `cmf_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned DEFAULT '0' COMMENT '发表者id',
  `post_keywords` varchar(150) NOT NULL COMMENT 'seo keywords',
  `post_source` varchar(150) DEFAULT NULL COMMENT '转载文章的来源',
  `post_date` datetime DEFAULT '2000-01-01 00:00:00' COMMENT 'post创建日期，永久不变，一般不显示给用户',
  `post_content` longtext COMMENT 'post内容',
  `post_title` text COMMENT 'post标题',
  `post_excerpt` text COMMENT 'post摘要',
  `post_status` int(2) DEFAULT '1' COMMENT 'post状态，1已审核，0未审核',
  `comment_status` int(2) DEFAULT '1' COMMENT '评论状态，1允许，0不允许',
  `redirect_url` varchar(255) DEFAULT NULL COMMENT '跳转URL地址',
  `post_modified` datetime DEFAULT '2000-01-01 00:00:00' COMMENT 'post更新时间，可在前台修改，显示给用户',
  `post_content_filtered` longtext,
  `post_parent` bigint(20) unsigned DEFAULT '0' COMMENT 'post的父级post id,表示post层级关系',
  `post_type` int(2) DEFAULT NULL,
  `post_mime_type` varchar(100) DEFAULT '',
  `comment_count` bigint(20) DEFAULT '0',
  `smeta` text COMMENT 'post的扩展字段，保存相关扩展属性，如缩略图；格式为json',
  `post_hits` int(11) DEFAULT '0' COMMENT 'post点击数，查看数',
  `post_like` int(11) DEFAULT '0' COMMENT 'post赞数',
  `istop` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶 1置顶； 0不置顶',
  `recommended` tinyint(1) NOT NULL DEFAULT '0' COMMENT '推荐 1推荐 0不推荐',
  PRIMARY KEY (`id`),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`id`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`),
  KEY `post_date` (`post_date`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='Portal文章表';

--
-- 表的结构 `cmf_region`
--

DROP TABLE IF EXISTS `cmf_region`;
CREATE TABLE IF NOT EXISTS `cmf_region` (
  `region_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `name` varchar(120) NOT NULL DEFAULT '' COMMENT '地区名称',
  `alias` varchar(100) NOT NULL COMMENT '别名',
  `type` tinyint(1) NOT NULL DEFAULT '2' COMMENT '地区类型:0国家1省/直辖市2城市3县区',
  `area_code` varchar(10) NOT NULL COMMENT '区号',
  `listorder` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`region_id`),
  KEY `parent_id` (`pid`),
  KEY `region_type` (`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=4044;

--
-- 表的结构 `cmf_role`
--

DROP TABLE IF EXISTS `cmf_role`;
CREATE TABLE IF NOT EXISTS `cmf_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `pid` smallint(6) DEFAULT NULL COMMENT '父角色ID',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` int(3) NOT NULL DEFAULT '0' COMMENT '排序字段',
  PRIMARY KEY (`id`),
  KEY `parentId` (`pid`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='角色表';



--
-- 表的结构 `cmf_role_user`
--

DROP TABLE IF EXISTS `cmf_role_user`;
CREATE TABLE IF NOT EXISTS `cmf_role_user` (
  `role_id` int(11) unsigned DEFAULT '0' COMMENT '角色 id',
  `user_id` int(11) DEFAULT '0' COMMENT '用户id',
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色对应表';



--
-- 表的结构 `cmf_route`
--

DROP TABLE IF EXISTS `cmf_route`;
CREATE TABLE IF NOT EXISTS `cmf_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '路由id',
  `full_url` varchar(255) DEFAULT NULL COMMENT '完整url， 如：portal/list/index?id=1',
  `url` varchar(255) DEFAULT NULL COMMENT '实际显示的url',
  `listorder` int(5) DEFAULT '0' COMMENT '排序，优先级，越小优先级越高',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，1：启用 ;0：不启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='url路由表';



--
-- 表的结构 `cmf_slide`
--

DROP TABLE IF EXISTS `cmf_slide`;
CREATE TABLE IF NOT EXISTS `cmf_slide` (
  `slide_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slide_cid` int(11) NOT NULL COMMENT '幻灯片分类 id',
  `slide_name` varchar(255) NOT NULL COMMENT '幻灯片名称',
  `slide_pic` varchar(255) DEFAULT NULL COMMENT '幻灯片图片',
  `slide_url` varchar(255) DEFAULT NULL COMMENT '幻灯片链接',
  `slide_des` varchar(255) DEFAULT NULL COMMENT '幻灯片描述',
  `slide_content` text COMMENT '幻灯片内容',
  `slide_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1显示，0不显示',
  `listorder` int(10) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`slide_id`),
  KEY `slide_cid` (`slide_cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='幻灯片表';



--
-- 表的结构 `cmf_slide_cat`
--

DROP TABLE IF EXISTS `cmf_slide_cat`;
CREATE TABLE IF NOT EXISTS `cmf_slide_cat` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(255) NOT NULL COMMENT '幻灯片分类',
  `cat_idname` varchar(255) NOT NULL COMMENT '幻灯片分类标识',
  `cat_remark` text COMMENT '分类备注',
  `cat_status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1显示，0不显示',
  PRIMARY KEY (`cid`),
  KEY `cat_idname` (`cat_idname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='幻灯片分类表';



--
-- 表的结构 `cmf_sms_validate`
--

DROP TABLE IF EXISTS `cmf_sms_validate`;
CREATE TABLE IF NOT EXISTS `cmf_sms_validate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL COMMENT '手机号码',
  `type` tinyint(3) unsigned NOT NULL COMMENT '短信类型: 1注册 2找回密码 3:手机号验证',
  `code` char(6) NOT NULL COMMENT '验证码',
  `create_at` datetime NOT NULL COMMENT '发送时间',
  `validate_at` datetime NOT NULL COMMENT '验证时间',
  `client_ip` char(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_ip` (`client_ip`),
  KEY `mobile` (`mobile`,`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='短信库';


--
-- 表的结构 `cmf_sms_tpl`
--

DROP TABLE IF EXISTS `cmf_sms_tpl`;
CREATE TABLE IF NOT EXISTS `cmf_sms_tpl` (
  `tplid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL COMMENT '短信类型: 1注册 2找回密码 3:手机号验证',
  `content` text NOT NULL COMMENT '模板内容',
  `tplcode` varchar(15) NOT NULL COMMENT '对应模板ID',
  `remark` text NOT NULL COMMENT '模板变量说明',
  `create_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`tplid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='短信模板表';


--
-- 表的结构 `cmf_sms_log`
--

DROP TABLE IF EXISTS `cmf_sms_log`;
CREATE TABLE IF NOT EXISTS `cmf_sms_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL COMMENT '短信类型: 1注册 2找回密码 3:手机号验证',
  `mobile` char(11) NOT NULL COMMENT '手机号码',
  `content` text NOT NULL COMMENT '短信内容',
  `create_at` datetime NOT NULL COMMENT '发送时间',
  PRIMARY KEY (`id`),
  KEY `mobile` (`mobile`,`type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='短信发送记录';

--
-- 表的结构 `cmf_terms`
--

DROP TABLE IF EXISTS `cmf_terms`;
CREATE TABLE IF NOT EXISTS `cmf_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `name` varchar(200) DEFAULT NULL COMMENT '分类名称',
  `slug` varchar(200) DEFAULT '',
  `taxonomy` varchar(32) DEFAULT NULL COMMENT '分类类型',
  `description` longtext COMMENT '分类描述',
  `parent` bigint(20) unsigned DEFAULT '0' COMMENT '分类父id',
  `count` bigint(20) DEFAULT '0' COMMENT '分类文章数',
  `path` varchar(500) DEFAULT NULL COMMENT '分类层级关系路径',
  `seo_title` varchar(500) DEFAULT NULL,
  `seo_keywords` varchar(500) DEFAULT NULL,
  `seo_description` varchar(500) DEFAULT NULL,
  `list_tpl` varchar(50) DEFAULT NULL COMMENT '分类列表模板',
  `one_tpl` varchar(50) DEFAULT NULL COMMENT '分类文章页模板',
  `listorder` int(5) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1发布，0不发布',
  PRIMARY KEY (`term_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='Portal 文章分类表';



--
-- 表的结构 `cmf_term_relationships`
--

DROP TABLE IF EXISTS `cmf_term_relationships`;
CREATE TABLE IF NOT EXISTS `cmf_term_relationships` (
  `tid` bigint(20) NOT NULL AUTO_INCREMENT,
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'posts表里文章id',
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分类id',
  `listorder` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` int(2) NOT NULL DEFAULT '1' COMMENT '状态，1发布，0不发布',
  PRIMARY KEY (`tid`),
  KEY `term_taxonomy_id` (`term_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='Portal 文章分类对应表';



--
-- 表的结构 `cmf_users`
--

DROP TABLE IF EXISTS `cmf_users`;
CREATE TABLE IF NOT EXISTS `cmf_users` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` char(15) NOT NULL DEFAULT '' COMMENT '用户名, 不可重复',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `user_email` char(32) NOT NULL DEFAULT '' COMMENT '登录邮箱',
  `user_pass` char(32) NOT NULL DEFAULT '' COMMENT '登录密码',
  `pass_salt` char(6) NOT NULL DEFAULT '' COMMENT '密码加盐',
  `user_nicename` char(15) NOT NULL DEFAULT '' COMMENT '用户昵称可重复',
  `avatar` char(200) NOT NULL DEFAULT '' COMMENT '用户头像，相对于upload/avatar目录',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别；0：保密，1：男；2：女',
  `birthday` date NOT NULL COMMENT '生日',
  `signature` char(255) NOT NULL DEFAULT '' COMMENT '个性签名',
  `user_activation_key` char(60) NOT NULL DEFAULT '' COMMENT '激活码',
  `active_key_expire` int(10) NOT NULL DEFAULT '0' COMMENT '激活码有效期时间戳',
  `fromuid` int(10) NOT NULL DEFAULT '0' COMMENT '推荐人UID',
  `user_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：正常',
  `mobile_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '手机号验证状态:0未验证1已验证',
  `email_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '邮箱验证状态:0未验证1已验证',
  `user_type` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '用户类型，1:admin ;2:会员',
  `last_login_ip` varchar(16) DEFAULT NULL COMMENT '最后登录ip',
  `last_login_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最后登录时间',
  `create_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '注册时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `mobile` (`mobile`),
  KEY `email` (`user_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='用户表';



--
-- 表的结构 `cmf_user_failedlogin`
--

DROP TABLE IF EXISTS `cmf_user_failedlogin`;
CREATE TABLE IF NOT EXISTS `cmf_user_failedlogin` (
  `ip` char(15) NOT NULL DEFAULT '',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `count` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `update_at` datetime NOT NULL,
  PRIMARY KEY (`ip`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录失败用户表';



--
-- 表的结构 `cmf_user_favorites`
--

DROP TABLE IF EXISTS `cmf_user_favorites`;
CREATE TABLE IF NOT EXISTS `cmf_user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) DEFAULT NULL COMMENT '用户 id',
  `title` varchar(255) DEFAULT NULL COMMENT '收藏内容的标题',
  `url` varchar(255) DEFAULT NULL COMMENT '收藏内容的原文地址，不带域名',
  `description` varchar(500) DEFAULT NULL COMMENT '收藏内容的描述',
  `table` varchar(50) DEFAULT NULL COMMENT '收藏实体以前所在表，不带前缀',
  `object_id` int(11) DEFAULT NULL COMMENT '收藏内容原来的主键id',
  `createtime` int(11) DEFAULT NULL COMMENT '收藏时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户收藏表';



--
-- 表的结构 `cmf_wechat_auto_reply`
--

DROP TABLE IF EXISTS `cmf_wechat_auto_reply`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_auto_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '回复类型:1图文2多图文3文本4功能回复',
  `name` varchar(30) NOT NULL COMMENT '回复规则名称',
  `keywords` varchar(300) NOT NULL COMMENT '回复关键词, 用|隔开',
  `content` longtext CHARACTER SET utf8mb4 NOT NULL,
  `expire_start` datetime NOT NULL DEFAULT '2015-12-31 00:00:00' COMMENT '自动回复生效时间',
  `expire_at` datetime NOT NULL DEFAULT '2099-12-31 00:00:00',
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='微信自动回复';


--
-- 表的结构 `cmf_wechat_tplmsg`
--

DROP TABLE IF EXISTS `cmf_wechat_tplmsg`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_tplmsg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) NOT NULL,
  `template_id` varchar(100) NOT NULL COMMENT '模板ID',
  `title` varchar(50) NOT NULL COMMENT '模板标题',
  `primary_industry` varchar(50) NOT NULL COMMENT '模板所属行业的一级行业',
  `deputy_industry` varchar(50) NOT NULL COMMENT '模板所属行业的二级行业',
  `content` text NOT NULL COMMENT '模板内容',
  `example` text NOT NULL COMMENT '模板示例',
  `colors` text NOT NULL COMMENT '对应颜色, serialize序列化存储',
  `update_at` datetime NOT NULL COMMENT '同步时间',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='模板消息';



--
-- 表的结构 `cmf_wechat_fans`
--

DROP TABLE IF EXISTS `cmf_wechat_fans`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_fans` (
  `wechatid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) CHARACTER SET utf8 NOT NULL,
  `openid` varchar(32) CHARACTER SET utf8 NOT NULL,
  `unionid` varchar(32) CHARACTER SET utf8 NOT NULL,
  `from_wechatid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '推荐人微信ID',
  `nickname` varchar(20) NOT NULL,
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `city` varchar(15) CHARACTER SET utf8 NOT NULL,
  `country` varchar(15) CHARACTER SET utf8 NOT NULL,
  `province` varchar(15) CHARACTER SET utf8 NOT NULL,
  `language` varchar(10) CHARACTER SET utf8 NOT NULL,
  `headimgurl` varchar(150) CHARACTER SET utf8 NOT NULL,
  `subscribe` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息',
  `subscribe_time` datetime NOT NULL COMMENT '关注时间',
  `lastaction_time` datetime NOT NULL COMMENT '最后与公众号互动时间',
  `remark` varchar(30) CHARACTER SET utf8 NOT NULL,
  `groupid` int(10) NOT NULL DEFAULT '0' COMMENT '用户所在的分组ID（兼容旧的用户分组接口）',
  `labelids` varchar(32) CHARACTER SET utf8 NOT NULL COMMENT '用户标签ID',
  `create_at` datetime NOT NULL,
  `update_at` datetime NOT NULL,
  PRIMARY KEY (`wechatid`),
  UNIQUE KEY `org_openid` (`original_id`,`openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='粉丝表';



--
-- 表的结构 `cmf_wechat_fans_label`
--

DROP TABLE IF EXISTS `cmf_wechat_fans_label`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_fans_label` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) NOT NULL COMMENT '微信原始ID',
  `labelid` int(10) unsigned NOT NULL COMMENT '标签id，由微信分配',
  `name` varchar(30) NOT NULL COMMENT '标签名',
  `count` mediumint(8) NOT NULL DEFAULT '0' COMMENT '此标签下粉丝数',
  `update_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `og_labelid` (`original_id`,`labelid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='粉丝用户标签';


--
-- 表的结构 `cmf_wechat_fans_label_relation`
--

DROP TABLE IF EXISTS `cmf_wechat_fans_label_relation`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_fans_label_relation` (
  `original_id` varchar(25) NOT NULL,
  `labelid` int(10) unsigned NOT NULL COMMENT '用户标签ID',
  `openid` varchar(32) NOT NULL COMMENT '粉丝OPENID',
  PRIMARY KEY (`original_id`,`labelid`,`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='粉丝标签数据表';

--
-- 表的结构 `cmf_wechat_qrcode`
--
DROP TABLE IF EXISTS `cmf_wechat_qrcode`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_qrcode` (
    `qrid` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `original_id` varchar(25) CHARACTER SET utf8 NOT NULL,
    `openid` varchar(32) CHARACTER SET utf8 NOT NULL,
    `category` varchar(32) NOT NULL COMMENT '二维码用途: recommend: 推荐',
    `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '二维码类型: 1: QR_SCENE为临时, 2: QR_LIMIT_SCENE为永久, 3: QR_LIMIT_STR_SCENE为永久的字符串参数值',
    `sceneid` varchar(60) NOT NULL COMMENT '二维码场景值: 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）字符串类型，长度限制为1到64，仅永久二维码支持',
    `expire` int(10) NOT NULL DEFAULT '0' COMMENT '二维码有效期',
    `url` varchar(255) NOT NULL COMMENT '二维码url地址',
    `data` text NOT NULL COMMENT 'serialize序列化存贮二维码其它相关数据',
    `create_at` datetime NOT NULL,
    `update_at` datetime NOT NULL,
    PRIMARY KEY (`qrid`),
    UNIQUE KEY `original_id` (`original_id`,`sceneid`),
    KEY `org_openid` (`original_id`,`category`,`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='微信二维码表';

--
-- 表的结构 `cmf_wechat_fans_message`
--

DROP TABLE IF EXISTS `cmf_wechat_fans_message`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_fans_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wechatid` int(10) unsigned NOT NULL COMMENT '微信用户ID',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '消息类型:1普通消息2事件消息',
  `message` text NOT NULL COMMENT '接收到的消息',
  `reply` text NOT NULL COMMENT '回复给用户的消息',
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `wechatid_idx` (`wechatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



--
-- 表的结构 `cmf_wechat_menues`
--

DROP TABLE IF EXISTS `cmf_wechat_menues`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_menues` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) NOT NULL,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '菜单分类: 0默认分类',
  `type` varchar(16) NOT NULL COMMENT '菜单按钮类型: click,viewL,scancode_push,scancode_waitmsg,pic_sysphoto,pic_photo_or_album,pic_weixin,location_select,media_id,view_limited',
  `name` varchar(40) NOT NULL,
  `click_key` varchar(128) NOT NULL,
  `url` varchar(200) NOT NULL,
  `media_id` varchar(45) NOT NULL,
  `parentid` smallint(5) unsigned NOT NULL,
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1显示0隐藏',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- 表的结构 `cmf_wechat_specialmenu_class`
--

DROP TABLE IF EXISTS `cmf_wechat_specialmenu_class`;
CREATE TABLE `cmf_wechat_specialmenu_class` (
  `catid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) NOT NULL,
  `name` varchar(100) NOT NULL,
  `group_id` int(11) NOT NULL DEFAULT '-1',
  `sex` int(11) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `client_platform_type` int(11) DEFAULT NULL,
  `menuid` varchar(100) NOT NULL COMMENT '生成菜单ID',
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否生成',
  `create_at` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`catid`),
  KEY `original_id` (`original_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='个性化菜单分类';


--
-- 表的结构 `cmf_wechat_mp`
--

DROP TABLE IF EXISTS `cmf_wechat_mp`;
CREATE TABLE IF NOT EXISTS `cmf_wechat_mp` (
  `mpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `original_id` varchar(25) NOT NULL COMMENT '原始ID',
  `wechat_account` varchar(25) NOT NULL COMMENT '微信号',
  `name` varchar(45) NOT NULL COMMENT '公众号名称',
  `type` tinyint(1) unsigned NOT NULL COMMENT '公众号类型:1认证订阅号, 2未认证订阅号, 3认证符号, 4未认证服务号',
  `avatar` varchar(100) NOT NULL COMMENT '头像',
  `qrcode` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT '二维码',
  `appid` varchar(20) NOT NULL COMMENT '应用ID',
  `appsecret` varchar(32) NOT NULL,
  `token` varchar(32) NOT NULL,
  `aeskey` varchar(45) NOT NULL COMMENT '消息加密密钥由43位字符组成, 消息加密密钥由43位字符组成',
  `encrypt` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '消息加解密方式: 1明文, 2兼容模式, 3安全模式',
  `access_token` varchar(45) NOT NULL COMMENT 'APP通信Token',
  `expires_time` int(10) NOT NULL COMMENT 'access_token过期时间戳',
  `white_ip_list` text NOT NULL COMMENT '微信服务器IP地址',
  `reply_subscribe` text NOT NULL COMMENT '首次关注回复内容',
  `reply_noanswer` text NOT NULL COMMENT '没有匹配回复时回复内容',
  `create_at` datetime NOT NULL COMMENT '创建时间',
  `update_at` datetime NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`mpid`),
  UNIQUE KEY `original_id_UNIQUE` (`original_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='微信公众号数据表';


--
-- 表的结构 `cmf_users_account`
--

DROP TABLE IF EXISTS `cmf_users_account`;
CREATE TABLE `cmf_users_account` (
 `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
 `money` int(11) unsigned NOT NULL COMMENT '可用金额',
 `frozen_money` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '冻结金额, 单位分',
 `gold` int(10) NOT NULL DEFAULT '0' COMMENT '金币(积分)数量',
 PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户余额表';

--
-- 表的结构 `cmf_users_cashout`
--

DROP TABLE IF EXISTS `cmf_users_cashout`;
CREATE TABLE IF NOT EXISTS `cmf_users_cashout` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
    `amount` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '体现总额',
    `fee` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '手续费',
    `bank` varchar(20) NOT NULL COMMENT '银行',
    `account` varchar(60) NOT NULL COMMENT '银行账户',
    `realname` varchar(10) NOT NULL COMMENT '姓名',
    `create_at` datetime NOT NULL COMMENT '创建时间',
    `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '转账状态: 0:-, 1未支付2已支付',
    `pay_uid` int(10) NOT NULL DEFAULT '0' COMMENT '打卡人员ID',
    `pay_msg` varchar(200) NOT NULL COMMENT '支付备注',
    `pay_at` datetime NOT NULL COMMENT '打款时间',
    `mod_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态: 0待审核1审核中2审核通过4审核未通过',
    `mod_msg` varchar(200) NOT NULL COMMENT '审核结果',
    `mod_uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核人员ID',
    `remark` varchar(200) NOT NULL COMMENT '管理备注',
    `mod_at` datetime NOT NULL COMMENT '审核时间',
    `err_msg` varchar(255) NOT NULL COMMENT '错误信息',
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='用户体现记录表';

--
-- 表的结构 `cmf_payment_config`
--

DROP TABLE IF EXISTS `cmf_payment_config`;
CREATE TABLE `cmf_payment_config` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `type` varchar(32) NOT NULL COMMENT '支付类型:wechat 微信支付, alipay: 支付宝支付',
 `name` varchar(16) NOT NULL COMMENT '名称',
 `config` text NOT NULL COMMENT '配置项目, serialize序列化存储',
 `enable` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1启用0禁用',
 `remark` text NOT NULL COMMENT '支付说明',
 `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1可见0隐藏',
 PRIMARY KEY (`id`),
 UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付方式表';

--
-- 表的结构 `cmf_payment_charge`
--

DROP TABLE IF EXISTS `cmf_payment_charge`;
CREATE TABLE IF NOT EXISTS `cmf_payment_charge` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '日志信息',
    `type` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '在线充值方式',
    `total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '充值金额,单位为分',
    `real_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '实际充值金额,单位为分',
    `trade_no` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '订单编号',
    `subject` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商品',
    `detail` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '详情',
    `pay_sn` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付订单编号',
    `from` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT  '订单来源',
    `orderid` VARCHAR(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '本站订单ID',
    `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0未支付1已支付2失败',
    `create_at` datetime NOT NULL COMMENT '创建时间',
    `paid_at` datetime NOT NULL COMMENT '支付时间',
    `log` text COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `trade_no` (`trade_no`),
    KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='在线充值表';

--
-- 表的结构 `cmf_users_finance`
--

DROP TABLE IF EXISTS `cmf_users_finance`;
CREATE TABLE `cmf_users_finance` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
 `money` int(11) unsigned NOT NULL COMMENT '金额',
 `type` tinyint(3) unsigned NOT NULL COMMENT '财务类型:1消费,2充值',
 `category` varchar(50) NOT NULL COMMENT '消费和充值方式',
 `accountbefore` int(11) unsigned NOT NULL COMMENT '记录产生前余额',
 `accountafter` int(11) unsigned NOT NULL COMMENT '记录产生后余额',
 `create_at` datetime NOT NULL COMMENT '创建时间',
 `remark` varchar(255) NOT NULL COMMENT '备注',
 `orderid` int(10) unsigned NOT NULL COMMENT '对应订单ID',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='财务流水表';


--
-- 表的结构 `cmf_users_gold_log`
--

CREATE TABLE IF NOT EXISTS `cmf_users_gold_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `gold` mediumint(8) unsigned NOT NULL COMMENT '积分/金币记录',
  `type` tinyint(3) unsigned NOT NULL COMMENT '积分类型:1奖励,2消耗',
  `category` varchar(32) NOT NULL COMMENT '类型',
  `source` varchar(50) NOT NULL COMMENT '奖励/消耗方式',
  `sourceid` varchar(20) NOT NULL COMMENT '单号ID',
  `goldbefore` int(10) unsigned NOT NULL COMMENT '记录产生前余额',
  `goldafter` int(10) unsigned NOT NULL COMMENT '记录产生后余额',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分/金币流水表';

--
-- 表的结构 `cmf_activity_join`
--

DROP TABLE IF EXISTS `cmf_activity_join`;
CREATE TABLE IF NOT EXISTS `cmf_activity_join` (
  `jid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `activity` varchar(30) NOT NULL DEFAULT '0' COMMENT '活动类型',
  `create_at` datetime NOT NULL,
  PRIMARY KEY (`jid`),
  UNIQUE KEY `uid` (`uid`,`activity`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='用户参与活动表';

--
-- 表的结构 `cmf_activity_morning_sign`
--

DROP TABLE IF EXISTS `cmf_activity_morning_sign`;
CREATE TABLE IF NOT EXISTS `cmf_activity_morning_sign` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT NULL COMMENT '用户ID',
  `openid` varchar(32) NOT NULL COMMENT '微信openid',
  `success` tinyint(1) NOT NULL DEFAULT '0' COMMENT '起床成功:1成功0失败',
  `sign_at` date NOT NULL COMMENT '挑战日期',
  `sign_at_time` datetime NOT NULL COMMENT '挑战具体时间',
  `getup_at` bigint(14) NOT NULL COMMENT '起床时间',
  `partner_trade_no` varchar(32) NOT NULL COMMENT '商户付款订单号',
  `payment_no` varchar(32) NOT NULL COMMENT '微信订单号',
  `payment_time` datetime NOT NULL COMMENT '企业付款成功时间',
  `amount` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '支付金额',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '付款状态: 1成功0失败',
  `err_code` varchar(30) NOT NULL COMMENT '付款错误编码',
  `err_code_des` varchar(200) NOT NULL COMMENT '错误代码描述',
  `days` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续打卡成功天数',
  PRIMARY KEY (`id`),
  KEY `dateat` (`sign_at`,`success`,`getup_at`),
  KEY `uid` (`uid`),
  KEY `openid` (`openid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='早起活动参加表';

--
-- 表的结构 `cmf_activity_morning_stat`
--

DROP TABLE IF EXISTS `cmf_activity_morning_stat`;
CREATE TABLE IF NOT EXISTS `cmf_activity_morning_stat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sign_at` date NOT NULL COMMENT '参与日期',
  `sign_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '参与人数',
  `success_num` int(10) NOT NULL DEFAULT '0' COMMENT '早起人数',
  `pay_account` smallint(5) NOT NULL DEFAULT '0' COMMENT '支付金额:单位分',
  `create_at` datetime NOT NULL COMMENT '统计时间',
  PRIMARY KEY (`id`),
  KEY `sign_date` (`sign_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='每日早起统计表';

-- --------------------------------------------------------

--
-- 转存表中的数据 `cmf_auth_rule`
--
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(1, 'Admin', 'admin_url', 'admin/content/default', NULL, '内容管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(2, 'Api', 'admin_url', 'api/guestbookadmin/index', NULL, '所有留言', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(3, 'Api', 'admin_url', 'api/guestbookadmin/delete', NULL, '删除网站留言', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(4, 'Comment', 'admin_url', 'comment/commentadmin/index', NULL, '评论管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(5, 'Comment', 'admin_url', 'comment/commentadmin/delete', NULL, '删除评论', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(6, 'Comment', 'admin_url', 'comment/commentadmin/check', NULL, '评论审核', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(7, 'Portal', 'admin_url', 'portal/adminpost/index', NULL, '文章管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(8, 'Portal', 'admin_url', 'portal/adminpost/listorders', NULL, '文章排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(9, 'Portal', 'admin_url', 'portal/adminpost/top', NULL, '文章置顶', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(10, 'Portal', 'admin_url', 'portal/adminpost/recommend', NULL, '文章推荐', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(11, 'Portal', 'admin_url', 'portal/adminpost/move', NULL, '批量移动', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(12, 'Portal', 'admin_url', 'portal/adminpost/check', NULL, '文章审核', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(13, 'Portal', 'admin_url', 'portal/adminpost/delete', NULL, '删除文章', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(14, 'Portal', 'admin_url', 'portal/adminpost/edit', NULL, '编辑文章', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(15, 'Portal', 'admin_url', 'portal/adminpost/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(16, 'Portal', 'admin_url', 'portal/adminpost/add', NULL, '添加文章', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(17, 'Portal', 'admin_url', 'portal/adminpost/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(18, 'Portal', 'admin_url', 'portal/adminterm/index', NULL, '分类管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(19, 'Portal', 'admin_url', 'portal/adminterm/listorders', NULL, '文章分类排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(20, 'Portal', 'admin_url', 'portal/adminterm/delete', NULL, '删除分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(21, 'Portal', 'admin_url', 'portal/adminterm/edit', NULL, '编辑分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(22, 'Portal', 'admin_url', 'portal/adminterm/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(23, 'Portal', 'admin_url', 'portal/adminterm/add', NULL, '添加分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(24, 'Portal', 'admin_url', 'portal/adminterm/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(25, 'Portal', 'admin_url', 'portal/adminpage/index', NULL, '页面管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(26, 'Portal', 'admin_url', 'portal/adminpage/listorders', NULL, '页面排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(27, 'Portal', 'admin_url', 'portal/adminpage/delete', NULL, '删除页面', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(28, 'Portal', 'admin_url', 'portal/adminpage/edit', NULL, '编辑页面', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(29, 'Portal', 'admin_url', 'portal/adminpage/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(30, 'Portal', 'admin_url', 'portal/adminpage/add', NULL, '添加页面', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(31, 'Portal', 'admin_url', 'portal/adminpage/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(32, 'Admin', 'admin_url', 'admin/recycle/default', NULL, '回收站', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(33, 'Portal', 'admin_url', 'portal/adminpost/recyclebin', NULL, '文章回收', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(34, 'Portal', 'admin_url', 'portal/adminpost/restore', NULL, '文章还原', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(35, 'Portal', 'admin_url', 'portal/adminpost/clean', NULL, '彻底删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(36, 'Portal', 'admin_url', 'portal/adminpage/recyclebin', NULL, '页面回收', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(37, 'Portal', 'admin_url', 'portal/adminpage/clean', NULL, '彻底删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(38, 'Portal', 'admin_url', 'portal/adminpage/restore', NULL, '页面还原', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(39, 'Admin', 'admin_url', 'admin/extension/default', NULL, '扩展工具', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(40, 'Admin', 'admin_url', 'admin/backup/default', NULL, '备份管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(41, 'Admin', 'admin_url', 'admin/backup/restore', NULL, '数据还原', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(42, 'Admin', 'admin_url', 'admin/backup/index', NULL, '数据备份', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(43, 'Admin', 'admin_url', 'admin/backup/index_post', NULL, '提交数据备份', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(44, 'Admin', 'admin_url', 'admin/backup/download', NULL, '下载备份', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(45, 'Admin', 'admin_url', 'admin/backup/del_backup', NULL, '删除备份', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(46, 'Admin', 'admin_url', 'admin/backup/import', NULL, '数据备份导入', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(47, 'Admin', 'admin_url', 'admin/plugin/index', NULL, '插件管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(48, 'Admin', 'admin_url', 'admin/plugin/toggle', NULL, '插件启用切换', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(49, 'Admin', 'admin_url', 'admin/plugin/setting', NULL, '插件设置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(50, 'Admin', 'admin_url', 'admin/plugin/setting_post', NULL, '插件设置提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(51, 'Admin', 'admin_url', 'admin/plugin/install', NULL, '插件安装', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(52, 'Admin', 'admin_url', 'admin/plugin/uninstall', NULL, '插件卸载', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(53, 'Admin', 'admin_url', 'admin/slide/default', NULL, '幻灯片', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(54, 'Admin', 'admin_url', 'admin/slide/index', NULL, '幻灯片管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(55, 'Admin', 'admin_url', 'admin/slide/listorders', NULL, '幻灯片排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(56, 'Admin', 'admin_url', 'admin/slide/toggle', NULL, '幻灯片显示切换', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(57, 'Admin', 'admin_url', 'admin/slide/delete', NULL, '删除幻灯片', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(58, 'Admin', 'admin_url', 'admin/slide/edit', NULL, '编辑幻灯片', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(59, 'Admin', 'admin_url', 'admin/slide/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(60, 'Admin', 'admin_url', 'admin/slide/add', NULL, '添加幻灯片', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(61, 'Admin', 'admin_url', 'admin/slide/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(62, 'Admin', 'admin_url', 'admin/slidecat/index', NULL, '幻灯片分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(63, 'Admin', 'admin_url', 'admin/slidecat/delete', NULL, '删除分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(64, 'Admin', 'admin_url', 'admin/slidecat/edit', NULL, '编辑分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(65, 'Admin', 'admin_url', 'admin/slidecat/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(66, 'Admin', 'admin_url', 'admin/slidecat/add', NULL, '添加分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(67, 'Admin', 'admin_url', 'admin/slidecat/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(68, 'Admin', 'admin_url', 'admin/ad/index', NULL, '网站广告', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(69, 'Admin', 'admin_url', 'admin/ad/toggle', NULL, '广告显示切换', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(70, 'Admin', 'admin_url', 'admin/ad/delete', NULL, '删除广告', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(71, 'Admin', 'admin_url', 'admin/ad/edit', NULL, '编辑广告', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(72, 'Admin', 'admin_url', 'admin/ad/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(73, 'Admin', 'admin_url', 'admin/ad/add', NULL, '添加广告', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(74, 'Admin', 'admin_url', 'admin/ad/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(75, 'Admin', 'admin_url', 'admin/link/index', NULL, '友情链接', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(76, 'Admin', 'admin_url', 'admin/link/listorders', NULL, '友情链接排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(77, 'Admin', 'admin_url', 'admin/link/toggle', NULL, '友链显示切换', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(78, 'Admin', 'admin_url', 'admin/link/delete', NULL, '删除友情链接', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(79, 'Admin', 'admin_url', 'admin/link/edit', NULL, '编辑友情链接', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(80, 'Admin', 'admin_url', 'admin/link/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(81, 'Admin', 'admin_url', 'admin/link/add', NULL, '添加友情链接', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(82, 'Admin', 'admin_url', 'admin/link/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(83, 'Api', 'admin_url', 'api/oauthadmin/setting', NULL, '第三方登陆', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(84, 'Api', 'admin_url', 'api/oauthadmin/setting_post', NULL, '提交设置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(85, 'Admin', 'admin_url', 'admin/menu/default', NULL, '菜单管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(86, 'Admin', 'admin_url', 'admin/navcat/default1', NULL, '前台菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(87, 'Admin', 'admin_url', 'admin/nav/index', NULL, '菜单管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(88, 'Admin', 'admin_url', 'admin/nav/listorders', NULL, '前台导航排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(89, 'Admin', 'admin_url', 'admin/nav/delete', NULL, '删除菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(90, 'Admin', 'admin_url', 'admin/nav/edit', NULL, '编辑菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(91, 'Admin', 'admin_url', 'admin/nav/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(92, 'Admin', 'admin_url', 'admin/nav/add', NULL, '添加菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(93, 'Admin', 'admin_url', 'admin/nav/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(94, 'Admin', 'admin_url', 'admin/navcat/index', NULL, '菜单分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(95, 'Admin', 'admin_url', 'admin/navcat/delete', NULL, '删除分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(96, 'Admin', 'admin_url', 'admin/navcat/edit', NULL, '编辑分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(97, 'Admin', 'admin_url', 'admin/navcat/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(98, 'Admin', 'admin_url', 'admin/navcat/add', NULL, '添加分类', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(99, 'Admin', 'admin_url', 'admin/navcat/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(100, 'Admin', 'admin_url', 'admin/menu/index', NULL, '后台菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(101, 'Admin', 'admin_url', 'admin/menu/add', NULL, '添加菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(102, 'Admin', 'admin_url', 'admin/menu/add_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(103, 'Admin', 'admin_url', 'admin/menu/listorders', NULL, '后台菜单排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(104, 'Admin', 'admin_url', 'admin/menu/export_menu', NULL, '菜单备份', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(105, 'Admin', 'admin_url', 'admin/menu/edit', NULL, '编辑菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(106, 'Admin', 'admin_url', 'admin/menu/edit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(107, 'Admin', 'admin_url', 'admin/menu/delete', NULL, '删除菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(108, 'Admin', 'admin_url', 'admin/menu/lists', NULL, '所有菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(109, 'Admin', 'admin_url', 'admin/setting/default', NULL, '设置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(110, 'Admin', 'admin_url', 'admin/setting/userdefault', NULL, '个人信息', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(111, 'Admin', 'admin_url', 'admin/user/userinfo', NULL, '修改信息', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(112, 'Admin', 'admin_url', 'admin/user/userinfo_post', NULL, '修改信息提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(113, 'Admin', 'admin_url', 'admin/setting/password', NULL, '修改密码', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(114, 'Admin', 'admin_url', 'admin/setting/password_post', NULL, '提交修改', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(115, 'Admin', 'admin_url', 'admin/setting/site', NULL, '网站信息', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(116, 'Admin', 'admin_url', 'admin/setting/site_post', NULL, '提交修改', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(117, 'Admin', 'admin_url', 'admin/route/index', NULL, '路由列表', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(118, 'Admin', 'admin_url', 'admin/route/add', NULL, '路由添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(119, 'Admin', 'admin_url', 'admin/route/add_post', NULL, '路由添加提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(120, 'Admin', 'admin_url', 'admin/route/edit', NULL, '路由编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(121, 'Admin', 'admin_url', 'admin/route/edit_post', NULL, '路由编辑提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(122, 'Admin', 'admin_url', 'admin/route/delete', NULL, '路由删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(123, 'Admin', 'admin_url', 'admin/route/ban', NULL, '路由禁止', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(124, 'Admin', 'admin_url', 'admin/route/open', NULL, '路由启用', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(125, 'Admin', 'admin_url', 'admin/route/listorders', NULL, '路由排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(126, 'Admin', 'admin_url', 'admin/mailer/default', NULL, '邮箱配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(127, 'Admin', 'admin_url', 'admin/mailer/index', NULL, 'SMTP配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(128, 'Admin', 'admin_url', 'admin/mailer/index_post', NULL, '提交配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(129, 'Admin', 'admin_url', 'admin/mailer/active', NULL, '邮件模板', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(130, 'Admin', 'admin_url', 'admin/mailer/active_post', NULL, '提交模板', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(131, 'Admin', 'admin_url', 'admin/setting/clearcache', NULL, '清除缓存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(132, 'User', 'admin_url', 'user/indexadmin/default', NULL, '用户管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(133, 'User', 'admin_url', 'user/indexadmin/default1', NULL, '用户组', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(134, 'User', 'admin_url', 'user/indexadmin/index', NULL, '本站用户', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(135, 'User', 'admin_url', 'user/indexadmin/ban', NULL, '拉黑会员', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(136, 'User', 'admin_url', 'user/indexadmin/cancelban', NULL, '启用会员', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(137, 'User', 'admin_url', 'user/oauthadmin/index', NULL, '第三方用户', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(138, 'User', 'admin_url', 'user/oauthadmin/delete', NULL, '第三方用户解绑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(139, 'User', 'admin_url', 'user/indexadmin/default3', NULL, '管理组', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(140, 'Admin', 'admin_url', 'admin/rbac/index', NULL, '角色管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(141, 'Admin', 'admin_url', 'admin/rbac/member', NULL, '成员管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(142, 'Admin', 'admin_url', 'admin/rbac/authorize', NULL, '权限设置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(143, 'Admin', 'admin_url', 'admin/rbac/authorize_post', NULL, '提交设置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(144, 'Admin', 'admin_url', 'admin/rbac/roleedit', NULL, '编辑角色', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(145, 'Admin', 'admin_url', 'admin/rbac/roleedit_post', NULL, '提交编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(146, 'Admin', 'admin_url', 'admin/rbac/roledelete', NULL, '删除角色', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(147, 'Admin', 'admin_url', 'admin/rbac/roleadd', NULL, '添加角色', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(148, 'Admin', 'admin_url', 'admin/rbac/roleadd_post', NULL, '提交添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(149, 'Admin', 'admin_url', 'admin/user/index', NULL, '管理员', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(150, 'Admin', 'admin_url', 'admin/user/delete', NULL, '删除管理员', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(151, 'Admin', 'admin_url', 'admin/user/edit', NULL, '管理员编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(152, 'Admin', 'admin_url', 'admin/user/edit_post', NULL, '编辑提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(153, 'Admin', 'admin_url', 'admin/user/add', NULL, '管理员添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(154, 'Admin', 'admin_url', 'admin/user/add_post', NULL, '添加提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(155, 'Admin', 'admin_url', 'admin/plugin/update', NULL, '插件更新', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(156, 'Admin', 'admin_url', 'admin/storage/index', NULL, '文件存储', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(157, 'Admin', 'admin_url', 'admin/storage/setting_post', NULL, '文件存储设置提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(158, 'Admin', 'admin_url', 'admin/slide/ban', NULL, '禁用幻灯片', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(159, 'Admin', 'admin_url', 'admin/slide/cancelban', NULL, '启用幻灯片', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(160, 'Admin', 'admin_url', 'admin/user/ban', NULL, '禁用管理员', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(161, 'Admin', 'admin_url', 'admin/user/cancelban', NULL, '启用管理员', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(162, 'Wechat', 'admin_url', 'wechat/adminindex/index', NULL, '', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(163, 'Wechat', 'admin_url', 'wechat/adminmp/index', NULL, '公众号管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(164, 'Wechat', 'admin_url', 'wechat/adminmp/add', NULL, '添加公众号', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(165, 'Wechat', 'admin_url', 'wechat/adminmp/add_post', NULL, '添加公众号提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(166, 'Wechat', 'admin_url', 'wechat/adminmp/edit', NULL, '修改公众号', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(167, 'Wechat', 'admin_url', 'wechat/adminmp/edit_post', NULL, '修改公众号提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(168, 'Wechat', 'admin_url', 'wechat/adminmp/delete', NULL, '删除公众号', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(169, 'Wechat', 'admin_url', 'wechat/adminmp/default', NULL, '微信公众号', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(170, 'Admin', 'admin_url', 'admin/wechat/default', NULL, '微信公众号', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(171, 'Wechat', 'admin_url', 'wechat/wechat/default', NULL, '微信', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(172, 'Wechat', 'admin_url', 'wechat/adminmessage/index', NULL, '自动回复', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(173, 'Wechat', 'admin_url', 'wechat/adminmessage/add', NULL, '自动回复添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(174, 'Wechat', 'admin_url', 'wechat/adminmessage/add_post', NULL, '自动回复添加提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(175, 'Wechat', 'admin_url', 'wechat/adminmessage/edit', NULL, '自动回复编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(176, 'Wechat', 'admin_url', 'wechat/adminmessage/edit_post', NULL, '自动回复编辑提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(177, 'Wechat', 'admin_url', 'wechat/adminmessage/delete', NULL, '自动回复删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(178, 'Portal', 'admin_url', 'portal/adminpost/wechat', NULL, '公众号图文信息添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(179, 'Wechat', 'admin_url', 'wechat/adminmenu/index', NULL, '自定义菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(180, 'Wechat', 'admin_url', 'wechat/adminmenu/add', NULL, '添加自定义菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(181, 'Wechat', 'admin_url', 'wechat/adminmenu/add_post', NULL, '添加自定义菜单提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(182, 'Wechat', 'admin_url', 'wechat/adminmenu/edit', NULL, '修改自定义菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(183, 'Wechat', 'admin_url', 'wechat/adminmenu/edit_post', NULL, '修改自定义菜单提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(184, 'Wechat', 'admin_url', 'wechat/adminmenu/delete', NULL, '自定义菜单删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(185, 'Wechat', 'admin_url', 'wechat/adminmenu/listorders', NULL, '自定义菜单排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(186, 'Wechat', 'admin_url', 'wechat/adminmenu/makemenu', NULL, '生成微信菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(187, 'Wechat', 'admin_url', 'wechat/adminmenu/delmenu', NULL, '删除微信菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(188, 'Wechat', 'admin_url', 'wechat/adminfans/index', NULL, '粉丝管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(189, 'Wechat', 'admin_url', 'wechat/adminfans/edit', NULL, '粉丝信息编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(190, 'Wechat', 'admin_url', 'wechat/adminfans/edit_post', NULL, '粉丝信息编辑提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(191, 'Wechat', 'admin_url', 'wechat/adminfans/delete', NULL, '删除粉丝', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(192, 'Wechat', 'admin_url', 'wechat/adminfans/sync', NULL, '同步粉丝信息', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(193, 'Wechat', 'admin_url', 'wechat/adminfanslabel/index', NULL, '用户标签', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(194, 'Wechat', 'admin_url', 'wechat/adminfanslabel/add', NULL, '添加标签', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(195, 'Wechat', 'admin_url', 'wechat/adminfanslabel/add_post', NULL, '添加标签提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(196, 'Wechat', 'admin_url', 'wechat/adminfanslabel/edit', NULL, '修改标签', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(197, 'Wechat', 'admin_url', 'wechat/adminfanslabel/edit_post', NULL, '修改标签提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(198, 'Wechat', 'admin_url', 'wechat/adminfanslabel/delete', NULL, '删除标签', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(199, 'Wechat', 'admin_url', 'wechat/adminfanslabel/sync', NULL, '同步标签', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(200, 'Wechat', 'admin_url', 'wechat/adminfans/default', NULL, '用户管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(201, 'Wechat', 'admin_url', 'wechat/adminfans/refresh', NULL, '刷新粉丝接口信息', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(202, 'Wechat', 'admin_url', 'wechat/adminfanslabel/syncuserlabel', NULL, '同步标签用户', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(203, 'Admin', 'admin_url', 'admin/sms/setting', NULL, '接口配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(204, 'Admin', 'admin_url', 'admin/sms/setting_post', NULL, '提交配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(205, 'Admin', 'admin_url', 'admin/sms/template', NULL, '短信模板', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(206, 'Admin', 'admin_url', 'admin/sms/template_post', NULL, '提交短信模板', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(207, 'Admin', 'admin_url', 'admin/sms/validate_list', NULL, '验证记录', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(208, 'Admin', 'admin_url', 'admin/sms/log_list', NULL, '发送记录', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(209, 'User', 'admin_url', 'user/indexadmin/password', NULL, '修改密码', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(210, 'Wechat', 'admin_url', 'wechat/adminmessage/subscribe', NULL, '关注回复设置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(211, 'Wechat', 'admin_url', 'wechat/adminmessage/noanswer', NULL, '回答不上了配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(212, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/class_index', NULL, '个性化菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(213, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/class_add', NULL, '个性化规则添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(214, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/class_add_post', NULL, '提交个性化规则添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(215, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/class_edit', NULL, '个性化规则编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(216, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/class_edit_post', NULL, '提交个性化规则编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(217, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/class_delete', NULL, '个性化规则删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(218, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_index', NULL, '个性化菜单列表', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(219, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_add', NULL, '个性化菜单添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(220, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_add_post', NULL, '提交个性化菜单添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(221, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_edit', NULL, '个性化菜单编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(222, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_edit_post', NULL, '提交个性化菜单编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(223, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_listorders', NULL, '个性化菜单排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(224, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/menu_delete', NULL, '个性化菜单删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(225, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/makemenu', NULL, '微信个性化菜单生成', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(226, 'Wechat', 'admin_url', 'wechat/adminspecialmenu/delmenu', NULL, '微信个性化菜单删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(227, 'Wechat', 'admin_url', 'wechat/admintplmsg/index', NULL, '模板消息', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(228, 'Wechat', 'admin_url', 'wechat/admintplmsg/sync', NULL, '模板消息同步', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(229, 'Wechat', 'admin_url', 'wechat/admintplmsg/setcolor', NULL, '设置颜色', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(230, 'Wechat', 'admin_url', 'wechat/admintplmsg/setcolor_post', NULL, '提交设置颜色', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(231, 'Wechat', 'admin_url', 'wechat/admintplmsg/delete', NULL, '模板消息删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(232, 'Finance', 'admin_url', 'finance/adminconfig/index', NULL, '在线支付', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(233, 'Finance', 'admin_url', 'finance/adminconfig/setting', NULL, '支付方式配置', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(234, 'Finance', 'admin_url', 'finance/adminconfig/setting_post', NULL, '支付方式配置提交', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(235, 'Finance', 'admin_url', 'finance/adminconfig/enable', NULL, '支付方式启用', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(236, 'Finance', 'admin_url', 'finance/adminconfig/disable', NULL, '支付方式禁用', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(237, 'Finance', 'admin_url', 'finance/adminfinance/users_index', NULL, '用户财务流水', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(238, 'Admin', 'admin_url', 'admin/sms/default', NULL, '短信', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(239, 'Finance', 'admin_url', 'finance/adminfinance/default', NULL, '财务', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(240, 'Wechat', 'admin_url', 'wechat/wechat/menu', NULL, '菜单', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(241, 'Admin', 'admin_url', 'admin/api/index', NULL, 'API列表', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(242, 'Admin', 'admin_url', 'admin/api/add', NULL, '添加API', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(243, 'Admin', 'admin_url', 'admin/api/add_post', NULL, '添加保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(244, 'Admin', 'admin_url', 'admin/api/edit', NULL, '编辑API', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(245, 'Admin', 'admin_url', 'admin/api/edit_post', NULL, '编辑保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(246, 'Admin', 'admin_url', 'admin/api/del', NULL, '删除API', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(247, 'Admin', 'admin_url', 'admin/api/params', NULL, 'API参数', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(248, 'Admin', 'admin_url', 'admin/api/params_add', NULL, '添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(249, 'Admin', 'admin_url', 'admin/api/params_add_post', NULL, '添加保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(250, 'Admin', 'admin_url', 'admin/api/params_edit', NULL, '编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(251, 'Admin', 'admin_url', 'admin/api/params_edit_post', NULL, '编辑保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(252, 'Admin', 'admin_url', 'admin/api/params_del', NULL, '删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(253, 'Admin', 'admin_url', 'admin/api/response', NULL, '响应数据', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(254, 'Admin', 'admin_url', 'admin/api/response_add', NULL, '添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(255, 'Admin', 'admin_url', 'admin/api/response_add_post', NULL, '添加保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(256, 'Admin', 'admin_url', 'admin/api/response_edit', NULL, '编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(257, 'Admin', 'admin_url', 'admin/api/response_edit_post', NULL, '编辑保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(258, 'Admin', 'admin_url', 'admin/api/response_del', NULL, '删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(259, 'Admin', 'admin_url', 'admin/app/index', NULL, 'APP列表', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(260, 'Admin', 'admin_url', 'admin/app/add', NULL, '添加APP', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(261, 'Admin', 'admin_url', 'admin/app/add_post', NULL, '添加保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(262, 'Admin', 'admin_url', 'admin/app/edit', NULL, '修改', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(263, 'Admin', 'admin_url', 'admin/app/edit_post', NULL, '修改保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(264, 'Admin', 'admin_url', 'admin/app/del', NULL, '删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(265, 'Admin', 'admin_url', 'admin/app/upgrade', NULL, 'APP版本管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(266, 'Admin', 'admin_url', 'admin/app/uprade_add', NULL, '添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(267, 'Admin', 'admin_url', 'admin/app/upgrade_add_post', NULL, '添加保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(268, 'Admin', 'admin_url', 'admin/app/upgrade_edit', NULL, '编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(269, 'Admin', 'admin_url', 'admin/app/upgrade_edit_post', NULL, '编辑保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(270, 'Admin', 'admin_url', 'admin/app/upgrade_del', NULL, '删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(271, 'Admin', 'admin_url', 'admin/app/default', NULL, 'APP&amp;API', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(272, 'Admin', 'admin_url', 'admin/api/default', NULL, 'API管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(273, 'Admin', 'admin_url', 'admin/app/main', NULL, 'APP管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(274, 'Admin', 'admin_url', 'admin/app/upgrade_add', NULL, '添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(275, 'Admin', 'admin_url', 'admin/apigroup/index', NULL, '分组管理', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(276, 'Admin', 'admin_url', 'admin/apigroup/listorder', NULL, '排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(277, 'Admin', 'admin_url', 'admin/apigroup/add', NULL, '添加', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(278, 'Admin', 'admin_url', 'admin/apigroup/add_post', NULL, '添加保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(279, 'Admin', 'admin_url', 'admin/apigroup/edit', NULL, '编辑', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(280, 'Admin', 'admin_url', 'admin/apigroup/edit_post', NULL, '编辑保存', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(281, 'Admin', 'admin_url', 'admin/apigroup/del', NULL, '删除', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(282, 'Admin', 'admin_url', 'admin/api/params_listorder', NULL, '排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(283, 'Admin', 'admin_url', 'admin/api/response_listorder', NULL, '排序', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(284, 'Admin', 'admin_url', 'admin/log/index', NULL, '日志', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(285, 'Admin', 'admin_url', 'admin/log/api', NULL, 'API日志', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(286, 'Admin', 'admin_url', 'admin/log/api_info', NULL, '详情', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(287, 'Admin', 'admin_url', 'admin/log/app_error', NULL, 'APP日志', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(288, 'Admin', 'admin_url', 'admin/log/app_error_info', NULL, '详情', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(289, 'Admin', 'admin_url', 'admin/device/index', NULL, '安装设备', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(290, 'Admin', 'admin_url', 'admin/device/info', NULL, '详情', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(291, 'Activity', 'admin_url', 'activity/adminmorning/index', NULL, '打卡列表', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(292, 'Activity', 'admin_url', 'activity/adminmorning/stat', NULL, '每日统计', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(293, 'Activity', 'admin_url', 'activity/adminmorning/default', NULL, '早起打卡', 1, '');
INSERT INTO `cmf_auth_rule` (`id`, `module`, `type`, `name`, `param`, `title`, `status`, `condition`) VALUES(294, 'Activity', 'admin_url', 'activity/adminmorning/config', NULL, '配置', 1, '');
-- ---------------------------------------------------------------------------------------------------
--
-- 转存表中的数据 `cmf_menu`
--
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(1, 0, 'Admin', 'Content', 'default', '', 0, 1, '内容管理', 'th', '', 30);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(2, 1, 'Api', 'Guestbookadmin', 'index', '', 1, 1, '所有留言', '', '', 5);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(3, 2, 'Api', 'Guestbookadmin', 'delete', '', 1, 0, '删除网站留言', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(4, 1, 'Comment', 'Commentadmin', 'index', '', 1, 1, '评论管理', '', '', 3);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(5, 4, 'Comment', 'Commentadmin', 'delete', '', 1, 0, '删除评论', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(6, 4, 'Comment', 'Commentadmin', 'check', '', 1, 0, '评论审核', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(7, 1, 'Portal', 'AdminPost', 'index', '', 1, 1, '文章管理', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(8, 7, 'Portal', 'AdminPost', 'listorders', '', 1, 0, '文章排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(9, 7, 'Portal', 'AdminPost', 'top', '', 1, 0, '文章置顶', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(10, 7, 'Portal', 'AdminPost', 'recommend', '', 1, 0, '文章推荐', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(11, 7, 'Portal', 'AdminPost', 'move', '', 1, 0, '批量移动', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(12, 7, 'Portal', 'AdminPost', 'check', '', 1, 0, '文章审核', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(13, 7, 'Portal', 'AdminPost', 'delete', '', 1, 0, '删除文章', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(14, 7, 'Portal', 'AdminPost', 'edit', '', 1, 0, '编辑文章', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(15, 14, 'Portal', 'AdminPost', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(16, 7, 'Portal', 'AdminPost', 'add', '', 1, 0, '添加文章', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(17, 16, 'Portal', 'AdminPost', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(18, 1, 'Portal', 'AdminTerm', 'index', '', 0, 1, '分类管理', '', '', 2);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(19, 18, 'Portal', 'AdminTerm', 'listorders', '', 1, 0, '文章分类排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(20, 18, 'Portal', 'AdminTerm', 'delete', '', 1, 0, '删除分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(21, 18, 'Portal', 'AdminTerm', 'edit', '', 1, 0, '编辑分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(22, 21, 'Portal', 'AdminTerm', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(23, 18, 'Portal', 'AdminTerm', 'add', '', 1, 0, '添加分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(24, 23, 'Portal', 'AdminTerm', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(25, 1, 'Portal', 'AdminPage', 'index', '', 1, 1, '页面管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(26, 25, 'Portal', 'AdminPage', 'listorders', '', 1, 0, '页面排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(27, 25, 'Portal', 'AdminPage', 'delete', '', 1, 0, '删除页面', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(28, 25, 'Portal', 'AdminPage', 'edit', '', 1, 0, '编辑页面', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(29, 28, 'Portal', 'AdminPage', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(30, 25, 'Portal', 'AdminPage', 'add', '', 1, 0, '添加页面', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(31, 30, 'Portal', 'AdminPage', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(32, 1, 'Admin', 'Recycle', 'default', '', 1, 1, '回收站', '', '', 4);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(33, 32, 'Portal', 'AdminPost', 'recyclebin', '', 1, 1, '文章回收', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(34, 33, 'Portal', 'AdminPost', 'restore', '', 1, 0, '文章还原', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(35, 33, 'Portal', 'AdminPost', 'clean', '', 1, 0, '彻底删除', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(36, 32, 'Portal', 'AdminPage', 'recyclebin', '', 1, 1, '页面回收', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(37, 36, 'Portal', 'AdminPage', 'clean', '', 1, 0, '彻底删除', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(38, 36, 'Portal', 'AdminPage', 'restore', '', 1, 0, '页面还原', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(39, 0, 'Admin', 'Extension', 'default', '', 0, 1, '扩展工具', 'cloud', '', 40);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(40, 39, 'Admin', 'Backup', 'default', '', 1, 1, '备份管理', '', '', 4);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(41, 40, 'Admin', 'Backup', 'restore', '', 1, 1, '数据还原', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(42, 40, 'Admin', 'Backup', 'index', '', 1, 1, '数据备份', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(43, 42, 'Admin', 'Backup', 'index_post', '', 1, 0, '提交数据备份', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(44, 40, 'Admin', 'Backup', 'download', '', 1, 0, '下载备份', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(45, 40, 'Admin', 'Backup', 'del_backup', '', 1, 0, '删除备份', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(46, 40, 'Admin', 'Backup', 'import', '', 1, 0, '数据备份导入', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(47, 39, 'Admin', 'Plugin', 'index', '', 1, 1, '插件管理', '', '', 3);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(48, 47, 'Admin', 'Plugin', 'toggle', '', 1, 0, '插件启用切换', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(49, 47, 'Admin', 'Plugin', 'setting', '', 1, 0, '插件设置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(50, 49, 'Admin', 'Plugin', 'setting_post', '', 1, 0, '插件设置提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(51, 47, 'Admin', 'Plugin', 'install', '', 1, 0, '插件安装', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(52, 47, 'Admin', 'Plugin', 'uninstall', '', 1, 0, '插件卸载', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(53, 1, 'Admin', 'Slide', 'default', '', 1, 1, '幻灯片', '', '', 8);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(54, 53, 'Admin', 'Slide', 'index', '', 1, 1, '幻灯片管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(55, 54, 'Admin', 'Slide', 'listorders', '', 1, 0, '幻灯片排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(56, 54, 'Admin', 'Slide', 'toggle', '', 1, 0, '幻灯片显示切换', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(57, 54, 'Admin', 'Slide', 'delete', '', 1, 0, '删除幻灯片', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(58, 54, 'Admin', 'Slide', 'edit', '', 1, 0, '编辑幻灯片', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(59, 58, 'Admin', 'Slide', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(60, 54, 'Admin', 'Slide', 'add', '', 1, 0, '添加幻灯片', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(61, 60, 'Admin', 'Slide', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(62, 53, 'Admin', 'Slidecat', 'index', '', 1, 1, '幻灯片分类', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(63, 62, 'Admin', 'Slidecat', 'delete', '', 1, 0, '删除分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(64, 62, 'Admin', 'Slidecat', 'edit', '', 1, 0, '编辑分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(65, 64, 'Admin', 'Slidecat', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(66, 62, 'Admin', 'Slidecat', 'add', '', 1, 0, '添加分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(67, 66, 'Admin', 'Slidecat', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(68, 1, 'Admin', 'Ad', 'index', '', 1, 1, '网站广告', '', '', 7);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(69, 68, 'Admin', 'Ad', 'toggle', '', 1, 0, '广告显示切换', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(70, 68, 'Admin', 'Ad', 'delete', '', 1, 0, '删除广告', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(71, 68, 'Admin', 'Ad', 'edit', '', 1, 0, '编辑广告', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(72, 71, 'Admin', 'Ad', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(73, 68, 'Admin', 'Ad', 'add', '', 1, 0, '添加广告', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(74, 73, 'Admin', 'Ad', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(75, 1, 'Admin', 'Link', 'index', '', 0, 1, '友情链接', '', '', 6);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(76, 75, 'Admin', 'Link', 'listorders', '', 1, 0, '友情链接排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(77, 75, 'Admin', 'Link', 'toggle', '', 1, 0, '友链显示切换', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(78, 75, 'Admin', 'Link', 'delete', '', 1, 0, '删除友情链接', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(79, 75, 'Admin', 'Link', 'edit', '', 1, 0, '编辑友情链接', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(80, 79, 'Admin', 'Link', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(81, 75, 'Admin', 'Link', 'add', '', 1, 0, '添加友情链接', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(82, 81, 'Admin', 'Link', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(83, 39, 'Api', 'Oauthadmin', 'setting', '', 1, 1, '第三方登陆', 'leaf', '', 2);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(84, 83, 'Api', 'Oauthadmin', 'setting_post', '', 1, 0, '提交设置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(85, 109, 'Admin', 'Menu', 'default', '', 1, 0, '菜单管理', 'list', '', 20);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(86, 109, 'Admin', 'Navcat', 'default1', '', 1, 1, '前台菜单', '', '', 2);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(87, 86, 'Admin', 'Nav', 'index', '', 1, 1, '菜单管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(88, 87, 'Admin', 'Nav', 'listorders', '', 1, 0, '前台导航排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(89, 87, 'Admin', 'Nav', 'delete', '', 1, 0, '删除菜单', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(90, 87, 'Admin', 'Nav', 'edit', '', 1, 0, '编辑菜单', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(91, 90, 'Admin', 'Nav', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(92, 87, 'Admin', 'Nav', 'add', '', 1, 0, '添加菜单', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(93, 92, 'Admin', 'Nav', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(94, 86, 'Admin', 'Navcat', 'index', '', 1, 1, '菜单分类', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(95, 94, 'Admin', 'Navcat', 'delete', '', 1, 0, '删除分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(96, 94, 'Admin', 'Navcat', 'edit', '', 1, 0, '编辑分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(97, 96, 'Admin', 'Navcat', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(98, 94, 'Admin', 'Navcat', 'add', '', 1, 0, '添加分类', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(99, 98, 'Admin', 'Navcat', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(100, 109, 'Admin', 'Menu', 'index', '', 1, 1, '后台菜单', '', '', 3);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(101, 100, 'Admin', 'Menu', 'add', '', 1, 0, '添加菜单', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(102, 101, 'Admin', 'Menu', 'add_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(103, 100, 'Admin', 'Menu', 'listorders', '', 1, 0, '后台菜单排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(104, 100, 'Admin', 'Menu', 'export_menu', '', 1, 0, '菜单备份', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(105, 100, 'Admin', 'Menu', 'edit', '', 1, 0, '编辑菜单', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(106, 105, 'Admin', 'Menu', 'edit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(107, 100, 'Admin', 'Menu', 'delete', '', 1, 0, '删除菜单', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(108, 100, 'Admin', 'Menu', 'lists', '', 1, 0, '所有菜单', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(109, 0, 'Admin', 'Setting', 'default', '', 0, 1, '设置', 'cogs', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(110, 109, 'Admin', 'Setting', 'userdefault', '', 0, 1, '个人信息', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(111, 110, 'Admin', 'User', 'userinfo', '', 1, 1, '修改信息', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(112, 111, 'Admin', 'User', 'userinfo_post', '', 1, 0, '修改信息提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(113, 110, 'Admin', 'Setting', 'password', '', 1, 1, '修改密码', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(114, 113, 'Admin', 'Setting', 'password_post', '', 1, 0, '提交修改', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(115, 109, 'Admin', 'Setting', 'site', '', 1, 1, '网站信息', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(116, 115, 'Admin', 'Setting', 'site_post', '', 1, 0, '提交修改', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(117, 115, 'Admin', 'Route', 'index', '', 1, 0, '路由列表', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(118, 115, 'Admin', 'Route', 'add', '', 1, 0, '路由添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(119, 118, 'Admin', 'Route', 'add_post', '', 1, 0, '路由添加提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(120, 115, 'Admin', 'Route', 'edit', '', 1, 0, '路由编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(121, 120, 'Admin', 'Route', 'edit_post', '', 1, 0, '路由编辑提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(122, 115, 'Admin', 'Route', 'delete', '', 1, 0, '路由删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(123, 115, 'Admin', 'Route', 'ban', '', 1, 0, '路由禁止', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(124, 115, 'Admin', 'Route', 'open', '', 1, 0, '路由启用', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(125, 115, 'Admin', 'Route', 'listorders', '', 1, 0, '路由排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(126, 39, 'Admin', 'Mailer', 'default', '', 1, 1, '邮箱配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(127, 126, 'Admin', 'Mailer', 'index', '', 1, 1, 'SMTP配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(128, 127, 'Admin', 'Mailer', 'index_post', '', 1, 0, '提交配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(129, 126, 'Admin', 'Mailer', 'active', '', 1, 1, '邮件模板', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(130, 129, 'Admin', 'Mailer', 'active_post', '', 1, 0, '提交模板', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(131, 109, 'Admin', 'Setting', 'clearcache', '', 1, 1, '清除缓存', '', '', 10);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(132, 0, 'User', 'Indexadmin', 'default', '', 1, 1, '用户管理', 'group', '', 10);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(133, 132, 'User', 'Indexadmin', 'default1', '', 1, 1, '用户组', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(134, 133, 'User', 'Indexadmin', 'index', '', 1, 1, '本站用户', 'leaf', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(135, 134, 'User', 'Indexadmin', 'ban', '', 1, 0, '拉黑会员', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(136, 134, 'User', 'Indexadmin', 'cancelban', '', 1, 0, '启用会员', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(137, 133, 'User', 'Oauthadmin', 'index', '', 1, 1, '第三方用户', 'leaf', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(138, 137, 'User', 'Oauthadmin', 'delete', '', 1, 0, '第三方用户解绑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(139, 132, 'User', 'Indexadmin', 'default3', '', 1, 1, '管理组', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(140, 139, 'Admin', 'Rbac', 'index', '', 1, 1, '角色管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(141, 140, 'Admin', 'Rbac', 'member', '', 1, 0, '成员管理', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(142, 140, 'Admin', 'Rbac', 'authorize', '', 1, 0, '权限设置', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(143, 142, 'Admin', 'Rbac', 'authorize_post', '', 1, 0, '提交设置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(144, 140, 'Admin', 'Rbac', 'roleedit', '', 1, 0, '编辑角色', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(145, 144, 'Admin', 'Rbac', 'roleedit_post', '', 1, 0, '提交编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(146, 140, 'Admin', 'Rbac', 'roledelete', '', 1, 1, '删除角色', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(147, 140, 'Admin', 'Rbac', 'roleadd', '', 1, 1, '添加角色', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(148, 147, 'Admin', 'Rbac', 'roleadd_post', '', 1, 0, '提交添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(149, 139, 'Admin', 'User', 'index', '', 1, 1, '管理员', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(150, 149, 'Admin', 'User', 'delete', '', 1, 0, '删除管理员', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(151, 149, 'Admin', 'User', 'edit', '', 1, 0, '管理员编辑', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(152, 151, 'Admin', 'User', 'edit_post', '', 1, 0, '编辑提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(153, 149, 'Admin', 'User', 'add', '', 1, 0, '管理员添加', '', '', 1000);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(154, 153, 'Admin', 'User', 'add_post', '', 1, 0, '添加提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(155, 47, 'Admin', 'Plugin', 'update', '', 1, 0, '插件更新', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(156, 39, 'Admin', 'Storage', 'index', '', 1, 1, '文件存储', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(157, 156, 'Admin', 'Storage', 'setting_post', '', 1, 0, '文件存储设置提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(158, 54, 'Admin', 'Slide', 'ban', '', 1, 0, '禁用幻灯片', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(159, 54, 'Admin', 'Slide', 'cancelban', '', 1, 0, '启用幻灯片', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(160, 149, 'Admin', 'User', 'ban', '', 1, 0, '禁用管理员', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(161, 149, 'Admin', 'User', 'cancelban', '', 1, 0, '启用管理员', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(163, 169, 'Wechat', 'AdminMp', 'index', '', 1, 1, '公众号管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(164, 163, 'Wechat', 'AdminMp', 'add', '', 1, 0, '添加公众号', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(165, 164, 'Wechat', 'AdminMp', 'add_post', '', 1, 0, '添加公众号提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(166, 163, 'Wechat', 'AdminMp', 'edit', '', 1, 0, '修改公众号', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(167, 166, 'Wechat', 'AdminMp', 'edit_post', '', 1, 0, '修改公众号提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(168, 163, 'Wechat', 'AdminMp', 'delete', '', 1, 0, '删除公众号', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(169, 0, 'Wechat', 'Wechat', 'default', '', 0, 1, '微信', 'wechat', '', 60);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(170, 169, 'Wechat', 'AdminMessage', 'index', '', 1, 1, '自动回复', '', '', 3);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(171, 170, 'Wechat', 'AdminMessage', 'add', '', 1, 0, '自动回复添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(172, 171, 'Wechat', 'AdminMessage', 'add_post', '', 1, 0, '自动回复添加提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(173, 170, 'Wechat', 'AdminMessage', 'edit', '', 1, 0, '自动回复编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(174, 173, 'Wechat', 'AdminMessage', 'edit_post', '', 1, 0, '自动回复编辑提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(175, 170, 'Wechat', 'AdminMessage', 'delete', '', 1, 0, '自动回复删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(176, 170, 'Portal', 'AdminPost', 'wechat', '', 1, 0, '公众号图文信息添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(177, 238, 'Wechat', 'AdminMenu', 'index', '', 1, 1, '自定义菜单', 'bars', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(178, 177, 'Wechat', 'AdminMenu', 'add', '', 1, 0, '添加自定义菜单', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(179, 178, 'Wechat', 'AdminMenu', 'add_post', '', 1, 0, '添加自定义菜单提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(180, 177, 'Wechat', 'AdminMenu', 'edit', '', 1, 0, '修改自定义菜单', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(181, 180, 'Wechat', 'AdminMenu', 'edit_post', '', 1, 0, '修改自定义菜单提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(182, 177, 'Wechat', 'AdminMenu', 'delete', '', 1, 0, '自定义菜单删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(183, 177, 'Wechat', 'AdminMenu', 'listorders', '', 1, 0, '自定义菜单排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(184, 177, 'Wechat', 'AdminMenu', 'makemenu', '', 1, 0, '生成微信菜单', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(185, 177, 'Wechat', 'AdminMenu', 'delmenu', '', 1, 0, '删除微信菜单', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(186, 198, 'Wechat', 'AdminFans', 'index', '', 1, 1, '粉丝管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(187, 198, 'Wechat', 'AdminFans', 'edit', '', 1, 0, '粉丝信息编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(188, 187, 'Wechat', 'AdminFans', 'edit_post', '', 1, 0, '粉丝信息编辑提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(189, 186, 'Wechat', 'AdminFans', 'delete', '', 1, 0, '删除粉丝', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(190, 186, 'Wechat', 'AdminFans', 'sync', '', 1, 0, '同步粉丝信息', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(191, 198, 'Wechat', 'AdminFanslabel', 'index', '', 1, 1, '用户标签', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(192, 191, 'Wechat', 'AdminFanslabel', 'add', '', 1, 0, '添加标签', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(193, 192, 'Wechat', 'AdminFanslabel', 'add_post', '', 1, 0, '添加标签提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(194, 191, 'Wechat', 'AdminFanslabel', 'edit', '', 1, 0, '修改标签', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(195, 194, 'Wechat', 'AdminFanslabel', 'edit_post', '', 1, 0, '修改标签提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(196, 191, 'Wechat', 'AdminFanslabel', 'delete', '', 1, 0, '删除标签', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(197, 191, 'Wechat', 'AdminFanslabel', 'sync', '', 1, 0, '同步标签', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(198, 169, 'Wechat', 'AdminFans', 'default', '', 1, 1, '用户管理', '', '', 2);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(199, 186, 'Wechat', 'AdminFans', 'refresh', '', 1, 0, '刷新粉丝接口信息', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(200, 191, 'Wechat', 'AdminFanslabel', 'syncuserlabel', '', 1, 0, '同步标签用户', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(201, 236, 'Admin', 'Sms', 'setting', '', 1, 1, '接口配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(202, 201, 'Admin', 'Sms', 'setting_post', '', 1, 0, '提交配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(203, 236, 'Admin', 'Sms', 'template', '', 1, 1, '短信模板', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(204, 203, 'Admin', 'Sms', 'template_post', '', 1, 1, '提交短信模板', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(205, 236, 'Admin', 'Sms', 'validate_list', '', 1, 1, '验证记录', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(206, 236, 'Admin', 'Sms', 'log_list', '', 1, 1, '发送记录', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(207, 134, 'User', 'Indexadmin', 'password', '', 1, 0, '修改密码', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(208, 170, 'Wechat', 'AdminMessage', 'subscribe', '', 1, 0, '关注回复设置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(209, 170, 'Wechat', 'AdminMessage', 'noanswer', '', 1, 0, '回答不上了配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(210, 238, 'Wechat', 'AdminSpecialmenu', 'class_index', '', 1, 1, '个性化菜单', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(211, 210, 'Wechat', 'AdminSpecialmenu', 'class_add', '', 1, 0, '个性化规则添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(212, 211, 'Wechat', 'AdminSpecialmenu', 'class_add_post', '', 1, 0, '提交个性化规则添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(213, 210, 'Wechat', 'AdminSpecialmenu', 'class_edit', '', 1, 0, '个性化规则编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(214, 213, 'Wechat', 'AdminSpecialmenu', 'class_edit_post', '', 1, 0, '提交个性化规则编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(215, 210, 'Wechat', 'AdminSpecialmenu', 'class_delete', '', 1, 0, '个性化规则删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(216, 210, 'Wechat', 'AdminSpecialmenu', 'menu_index', '', 1, 0, '个性化菜单列表', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(217, 210, 'Wechat', 'AdminSpecialmenu', 'menu_add', '', 1, 0, '个性化菜单添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(218, 217, 'Wechat', 'AdminSpecialmenu', 'menu_add_post', '', 1, 0, '提交个性化菜单添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(219, 210, 'Wechat', 'AdminSpecialmenu', 'menu_edit', '', 1, 0, '个性化菜单编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(220, 219, 'Wechat', 'AdminSpecialmenu', 'menu_edit_post', '', 1, 0, '提交个性化菜单编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(221, 210, 'Wechat', 'AdminSpecialmenu', 'menu_listorders', '', 1, 0, '个性化菜单排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(222, 210, 'Wechat', 'AdminSpecialmenu', 'menu_delete', '', 1, 0, '个性化菜单删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(223, 210, 'Wechat', 'AdminSpecialmenu', 'makemenu', '', 1, 0, '微信个性化菜单生成', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(224, 210, 'Wechat', 'AdminSpecialmenu', 'delmenu', '', 1, 0, '微信个性化菜单删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(225, 169, 'Wechat', 'AdminTplmsg', 'index', '', 1, 1, '模板消息', '', '', 4);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(226, 225, 'Wechat', 'AdminTplmsg', 'sync', '', 1, 0, '模板消息同步', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(227, 225, 'Wechat', 'AdminTplmsg', 'setcolor', '', 1, 0, '设置颜色', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(228, 227, 'Wechat', 'AdminTplmsg', 'setcolor_post', '', 1, 0, '提交设置颜色', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(229, 225, 'Wechat', 'AdminTplmsg', 'delete', '', 1, 0, '模板消息删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(230, 237, 'Finance', 'AdminConfig', 'index', '', 1, 1, '在线支付', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(231, 230, 'Finance', 'AdminConfig', 'setting', '', 1, 0, '支付方式配置', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(232, 231, 'Finance', 'AdminConfig', 'setting_post', '', 1, 0, '支付方式配置提交', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(233, 230, 'Finance', 'AdminConfig', 'enable', '', 1, 0, '支付方式启用', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(234, 230, 'Finance', 'AdminConfig', 'disable', '', 1, 0, '支付方式禁用', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(235, 237, 'Finance', 'AdminFinance', 'users_index', '', 1, 1, '用户财务流水', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(236, 39, 'Admin', 'Sms', 'default', '', 1, 1, '短信', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(237, 0, 'Finance', 'AdminFinance', 'default', '', 1, 1, '财务', 'cny', '', 35);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(238, 169, 'Wechat', 'Wechat', 'menu', '', 1, 1, '菜单', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(239, 270, 'Admin', 'Api', 'index', '', 1, 1, 'API列表', '', '', 2);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(240, 270, 'Admin', 'Api', 'add', '', 1, 0, '添加API', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(241, 240, 'Admin', 'Api', 'add_post', '', 1, 0, '添加保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(242, 239, 'Admin', 'Api', 'edit', '', 1, 0, '编辑API', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(243, 242, 'Admin', 'Api', 'edit_post', '', 1, 0, '编辑保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(244, 239, 'Admin', 'Api', 'del', '', 1, 0, '删除API', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(245, 270, 'Admin', 'Api', 'params', '', 1, 0, 'API参数', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(246, 245, 'Admin', 'Api', 'params_add', '', 1, 0, '添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(247, 246, 'Admin', 'Api', 'params_add_post', '', 1, 0, '添加保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(248, 245, 'Admin', 'Api', 'params_edit', '', 1, 0, '编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(249, 248, 'Admin', 'Api', 'params_edit_post', '', 1, 0, '编辑保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(250, 245, 'Admin', 'Api', 'params_del', '', 1, 0, '删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(251, 270, 'Admin', 'Api', 'response', '', 1, 0, '响应数据', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(252, 251, 'Admin', 'Api', 'response_add', '', 1, 0, '添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(253, 252, 'Admin', 'Api', 'response_add_post', '', 1, 0, '添加保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(254, 251, 'Admin', 'Api', 'response_edit', '', 1, 0, '编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(255, 254, 'Admin', 'Api', 'response_edit_post', '', 1, 0, '编辑保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(256, 251, 'Admin', 'Api', 'response_del', '', 1, 0, '删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(257, 271, 'Admin', 'App', 'index', '', 1, 1, 'APP列表', '', '', 4);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(258, 271, 'Admin', 'App', 'add', '', 1, 0, '添加APP', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(259, 258, 'Admin', 'App', 'add_post', '', 1, 0, '添加保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(260, 271, 'Admin', 'App', 'edit', '', 1, 0, '修改', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(261, 260, 'Admin', 'App', 'edit_post', '', 1, 0, '修改保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(262, 271, 'Admin', 'App', 'del', '', 1, 0, '删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(263, 271, 'Admin', 'App', 'upgrade', '', 1, 1, 'APP版本管理', '', '', 6);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(264, 263, 'Admin', 'App', 'upgrade_add', '', 1, 0, '添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(265, 264, 'Admin', 'App', 'upgrade_add_post', '', 1, 0, '添加保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(266, 263, 'Admin', 'App', 'upgrade_edit', '', 1, 0, '编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(267, 266, 'Admin', 'App', 'upgrade_edit_post', '', 1, 0, '编辑保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(268, 263, 'Admin', 'App', 'upgrade_del', '', 1, 0, '删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(269, 0, 'Admin', 'App', 'default', '', 1, 1, 'APP&amp;API', 'magnet', '', 80);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(270, 269, 'Admin', 'Api', 'default', '', 1, 1, 'API管理', '', '', 4);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(271, 269, 'Admin', 'App', 'main', '', 1, 1, 'APP管理', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(272, 270, 'Admin', 'Apigroup', 'index', '', 1, 1, '分组管理', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(273, 272, 'Admin', 'Apigroup', 'listorder', '', 1, 0, '排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(274, 272, 'Admin', 'Apigroup', 'add', '', 1, 0, '添加', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(275, 274, 'Admin', 'Apigroup', 'add_post', '', 1, 0, '添加保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(276, 272, 'Admin', 'Apigroup', 'edit', '', 1, 0, '编辑', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(277, 276, 'Admin', 'Apigroup', 'edit_post', '', 1, 0, '编辑保存', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(278, 272, 'Admin', 'Apigroup', 'del', '', 1, 0, '删除', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(279, 245, 'Admin', 'Api', 'params_listorder', '', 1, 0, '排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(280, 251, 'Admin', 'Api', 'response_listorder', '', 1, 0, '排序', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(281, 269, 'Admin', 'Log', 'index', '', 1, 1, '日志', '', '', 8);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(282, 281, 'Admin', 'Log', 'api', '', 1, 1, 'API日志', '', '', 1);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(283, 282, 'Admin', 'Log', 'api_info', '', 1, 0, '详情', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(284, 281, 'Admin', 'Log', 'app_error', '', 1, 1, 'APP日志', '', '', 2);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(285, 284, 'Admin', 'Log', 'app_error_info', '', 1, 0, '详情', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(286, 271, 'Admin', 'Device', 'index', '', 1, 1, '安装设备', '', '', 9);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(287, 286, 'Admin', 'Device', 'info', '', 1, 0, '详情', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(288, 290, 'Activity', 'AdminMorning', 'index', '', 1, 1, '打卡列表', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(289, 290, 'Activity', 'AdminMorning', 'stat', '', 1, 1, '每日统计', '', '', 0);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(290, 169, 'Activity', 'AdminMorning', 'default', '', 1, 1, '早起打卡', '', '', 21);
INSERT INTO `cmf_menu` (`id`, `parentid`, `app`, `model`, `action`, `data`, `type`, `status`, `name`, `icon`, `remark`, `listorder`) VALUES(291, 290, 'Activity', 'AdminMorning', 'config', '', 1, 1, '配置', '', '', 0);
-- --------------------------------------------------------

-- 
-- 导出表中的数据 `cmf_nav`
-- 
INSERT INTO `cmf_nav` VALUES (1, 1, 0, '首页', '', 'home', '', 1, 0, '0-1');
INSERT INTO `cmf_nav` VALUES (2, 1, 0, '列表演示', '', 'a:2:{s:6:"action";s:17:"Portal/List/index";s:5:"param";a:1:{s:2:"id";s:1:"1";}}', '', 1, 0, '0-2');
INSERT INTO `cmf_nav` VALUES (3, 1, 0, '瀑布流', '', 'a:2:{s:6:"action";s:17:"Portal/List/index";s:5:"param";a:1:{s:2:"id";s:1:"2";}}', '', 1, 0, '0-3');

--
-- 转存表中的数据 `cmf_role`
--
INSERT INTO `cmf_role` (`id`, `name`, `pid`, `status`, `remark`, `create_time`, `update_time`, `listorder`) VALUES(1, '超级管理员', 0, 1, '拥有网站最高管理员权限！', 1329633709, 1329633709, 0);

--
-- 转存表中的数据 `cmf_role_user`
--
INSERT INTO `cmf_role_user` (`role_id`, `user_id`) VALUES(1, 1);

-- 
-- 导出表中的数据 `cmf_nav_cat`
-- 
INSERT INTO `cmf_nav_cat` VALUES (1, '主导航', 1, '主导航');


-- 
-- 导出表中的数据 `cmf_payment_config`
-- 
INSERT INTO `cmf_payment_config` (`id`, `type`, `name`, `config`, `enable`, `remark`, `status`) VALUES(1, 'wechat', '微信【公众号支付】', 'N;', 0, '', 1);
INSERT INTO `cmf_payment_config` (`id`, `type`, `name`, `config`, `enable`, `remark`, `status`) VALUES(2, 'alipay-pc', '支付宝【PC即时到账】', 'a:0:{}', 0, '', 0);
INSERT INTO `cmf_payment_config` (`id`, `type`, `name`, `config`, `enable`, `remark`, `status`) VALUES(3, 'alipay-wap', '支付宝【手机网站支付】', 'a:0:{}', 0, '', 0);
INSERT INTO `cmf_payment_config` (`id`, `type`, `name`, `config`, `enable`, `remark`, `status`) VALUES(4, 'gift', '系统赠送', 'a:0:{}', 0, '财务流水ORDERID为充值管理员ID', 0);
