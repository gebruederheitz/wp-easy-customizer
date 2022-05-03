<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

class TextCustomizerSetting extends BasicCustomizerSetting
{
    protected static $sanitizer = 'sanitize_text_field';
}
