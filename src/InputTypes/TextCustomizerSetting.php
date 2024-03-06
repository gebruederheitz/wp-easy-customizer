<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

/**
 * @extends BasicCustomizerSetting<string>
 */
abstract class TextCustomizerSetting extends BasicCustomizerSetting
{
    protected ?string $sanitizer = 'sanitize_text_field';
}
