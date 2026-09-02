<?php

namespace Gebruederheitz\Wordpress\Customizer\Tests;

use Gebruederheitz\Wordpress\Customizer\CustomizerSection;
use Gebruederheitz\Wordpress\Customizer\InputTypes\SeparatorSetting;
use PHPUnit\Framework\TestCase;

class CustomizerSectionTest extends TestCase
{
    public function testConstructorAndGettersHoldValues(): void
    {
        $section = new CustomizerSection('my-slug', 'My Label', 'My description');

        $this->assertSame('my-slug', $section->getSlug());
        $this->assertSame('My Label', $section->getLabel());
        $this->assertSame('My description', $section->getDescription());
        $this->assertSame([], $section->getSettings());
    }

    public function testFactoryAcceptsExplicitNullsForNullableParams(): void
    {
        // $description and $settings are nullable parameters (fixed for
        // PHP 8.4's implicit-nullable deprecation) - passing null
        // explicitly must still work.
        $section = CustomizerSection::factory('slug', 'Label', null, null);

        $this->assertSame('', $section->getDescription());
        $this->assertSame([], $section->getSettings());
    }

    public function testFluentSettersReturnSelf(): void
    {
        $section = new CustomizerSection('slug', 'Label');

        $this->assertSame($section, $section->setPriority(5));
        $this->assertSame($section, $section->setPanel('some_panel_id'));
    }

    public function testAddSettingsAccumulates(): void
    {
        $section = new CustomizerSection('slug', 'Label');
        $setting = SeparatorSetting::factory('separator-key');

        $result = $section->addSettings($setting);

        $this->assertSame($section, $result);
        $this->assertCount(1, $section->getSettings());
        $this->assertSame($setting, $section->getSettings()[0]);
    }
}
