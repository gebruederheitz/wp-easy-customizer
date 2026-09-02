<?php

namespace Gebruederheitz\Wordpress\Customizer\Tests;

use Gebruederheitz\Wordpress\Customizer\InputTypes\SeparatorSetting;
use PHPUnit\Framework\TestCase;

class SeparatorSettingTest extends TestCase
{
    public function testGetValueFallsBackToThemeModDefault(): void
    {
        // getValue() goes through CustomizerSettings::getValue(), which
        // calls the (stubbed) get_theme_mod() with the setting's default.
        $setting = SeparatorSetting::factory('sep-key');

        $this->assertNull($setting->getValue());
        $this->assertSame('sep-key', $setting->getKey());
    }
}
