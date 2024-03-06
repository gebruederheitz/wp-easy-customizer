<?php

namespace Gebruederheitz\Wordpress\Customizer;

use Gebruederheitz\SimpleSingleton\SingletonAble;

/**
 * @template ValueType
 * @extends CommonCustomizerSetting<ValueType>
 * @implements CustomizerSetting<ValueType>
 */
abstract class BasicCustomizerSetting extends CommonCustomizerSetting implements
    CustomizerSetting
{
    use SingletonAble;

    public static function get(): self
    {
        return self::getInstance();
    }

    /**
     * @return ValueType
     */
    public static function value()
    {
        return self::getInstance()->getValue();
    }
}
