<?php

namespace Gebruederheitz\Wordpress\Customizer\Tests;

use Gebruederheitz\Wordpress\Customizer\CustomizerPanel;
use PHPUnit\Framework\TestCase;

class CustomizerPanelTest extends TestCase
{
    public function testConstructorSetsPrefixedId(): void
    {
        $panel = new CustomizerPanel('my_panel', 'My Panel');

        $this->assertSame('ghwp_customizer_panel_my_panel', $panel->getId());
    }

    public function testAddNewSectionIsFluentAndAcceptsExplicitNulls(): void
    {
        $panel = new CustomizerPanel('another_panel');

        // $description and $settings are nullable parameters (fixed for
        // PHP 8.4's implicit-nullable deprecation) - passing null
        // explicitly must still work.
        $result = $panel->addNewSection('slug', 'Label', null, null);

        $this->assertSame($panel, $result);
    }
}
