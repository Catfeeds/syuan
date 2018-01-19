说明
========

ThinkPHP的扩展在使用时,往往需要增加行为配置即一些应用配置. 这需要用户手动处理.

HookAgent的作用就是为有此类需要的扩展包提供一个注入行为的统一入口, 使用HookAgent开发的扩展包,
最终用户只需要在 Common/Conf/tags.php 增加一个行为,即可:

```
return array(
 'app_init'=>array(
     'Snowair\Think\Behavior\HookAgent',
 ),
)
```

开发
======

如果你的thinkphp composer包需要注入行为, 只需要创建类似这样的文件:

```
<?php
// include.php
if (class_exists( 'Snowair\Think\Behavior\HookAgent' )) {
    \Snowair\Think\Behavior\HookAgent::add('app_begin','Snowair\\Dotenv\\DotEnv');
}
```

然后在包的composer.json文件中加入require和autoload设置:

```
{
  "name": "xxx/xxx",
  "require": {
    "snowair/think-hookagent": ">=0.1"
  },
  "autoload": {
    "files": ["include.php"]
  }
}
```

* 注意: 你只能借助HookAgent从 `app_begin` 往后开始注入行为. 你无法通过HookAgent向`app_init`注入行为.
