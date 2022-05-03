<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;
use Gebruederheitz\Wordpress\Customizer\CustomControl\SeparatorCustomControl;

class SeparatorSetting extends BasicCustomizerSetting
{
    public static $key = 'not-really-the-key';
    protected $dynamicKey;
    /** @var null|array */
    protected $dynamicOptions = null;
    /** @var null|string */
    protected $dynamicLabel = null;
    protected static $default = null;
    protected static $inputType = SeparatorCustomControl::class;

    public function __construct(
        string $key,
        ?string $label = null,
        ?array $options = null
    ) {
        $this->dynamicKey = $key;

        if ($label !== null) {
            $this->dynamicLabel = $label;
        }

        if ($options !== null) {
            $this->dynamicOptions = $options;
        }
    }

    public function getKey(): string
    {
        return $this->dynamicKey;
    }

    public function getLabel(): string
    {
        return $this->dynamicLabel ?: '';
    }

    public function getOptions(): ?array
    {
        return $this->dynamicOptions ?: null;
    }

    public static function getValue()
    {
        return null;
    }
}
