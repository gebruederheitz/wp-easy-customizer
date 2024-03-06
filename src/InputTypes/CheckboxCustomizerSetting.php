<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

/**
 * @extends BasicCustomizerSetting<bool>
 */
abstract class CheckboxCustomizerSetting extends BasicCustomizerSetting
{
    protected ?string $inputType = InputType::CHECKBOX;
    protected $default = false;
    protected ?string $sanitizer = 'rest_sanitize_boolean';
}
