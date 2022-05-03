<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

class CheckboxCustomizerSetting extends BasicCustomizerSetting
{
    protected static $inputType = 'checkbox';
    protected static $default = false;
}
