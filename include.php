<?php

Class SotbitMultibasketDemo {
    static private $demo = null;

    private static function setDemo() {
        self::$demo = \Bitrix\Main\Loader::includeSharewareModule('sotbit.multibasket');
    }

    public static function getDemo()
    {
        if(self::$demo === false || self::$demo === null)
            self::setDemo();
        return !(self::$demo == 0 || self::$demo == 3);
    }

    public static function returnDemo()
    {
        if(self::$demo === false || self::$demo === null)
            self::setDemo();
        return self::$demo;
    }

    public static function checkInstalledModules(array $arModules) {
        foreach($arModules as $module) {
            if (!\Bitrix\Main\Loader::includeModule($module))
                return false;
        }

        return true;
    }
}

?>