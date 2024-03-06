<?php

namespace Gebruederheitz\Wordpress\Customizer;

/**
 * @deprecated Please read the upgrade guide: You can still use this for
 *             semantic purposes, but all functionality surrounding this class
 *             has been removed.
 */
abstract class AbstractCustomizerSettingsHandler
{
    /** @return array<CustomizerSetting<mixed>> */
    abstract public function getSettings(): array;
}
