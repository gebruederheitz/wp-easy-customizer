<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

class UrlCustomizerSetting extends BasicCustomizerSetting
{
    protected static $inputType = 'url';
    protected static $sanitizer = 'sanitize_url';
}
