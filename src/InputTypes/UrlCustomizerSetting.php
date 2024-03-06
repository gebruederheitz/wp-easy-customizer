<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

/**
 * @extends BasicCustomizerSetting<string>
 */
abstract class UrlCustomizerSetting extends BasicCustomizerSetting
{
    protected ?string $inputType = InputType::URL;
    protected ?string $sanitizer = 'sanitize_url';
}
