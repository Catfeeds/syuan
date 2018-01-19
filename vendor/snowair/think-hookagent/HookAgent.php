<?php

namespace Snowair\Think\Behavior;

use Think\Hook;

class HookAgent
{
    protected static $tags=array();

    public function app_init()
    {
        $bags = self::$tags;
        foreach ($bags as $hook => $classes) {
            foreach ($classes as $class) {
                $tags = Hook::get($hook);
                if (class_exists($class)) {
                    if (!array_search( $class, $tags )) {
                        Hook::add($hook,$class);
                    }
                }
            }
        }
    }

    static public function add($tag,$name) {
        $bags = isset(self::$tags[$tag])?self::$tags[$tag]:array();
        if ( !array_search( $name, $bags )) {
            self::$tags[$tag][] =   $name;
        }
    }
}
