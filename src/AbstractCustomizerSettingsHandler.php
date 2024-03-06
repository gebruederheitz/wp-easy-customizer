<?php

namespace Gebruederheitz\Wordpress\Customizer;

use Gebruederheitz\SimpleSingleton\SingletonAble;

/**
 * @deprecated Please read the upgrade guide: You can still use this for
 *             semantic purposes, but all functionality surrounding this class
 *             has been removed.
 */
abstract class AbstractCustomizerSettingsHandler
{
    public static function factory(): self
    {
        return new static();
    }

    final public function __construct()
    {
        $this->init();
    }

    protected function init(): void
    {
    }

    /** @return array<CustomizerSetting<mixed>> */
    abstract public function getSettings(): array;
}
