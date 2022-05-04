<?php

namespace Gebruederheitz\Wordpress\Customizer;

abstract class BasicCustomizerSetting implements CustomizerSetting
{
    /** @var string */
    protected static $key;

    /** @var string */
    protected static $label;

    /** @var string */
    protected static $labelNamespace = 'ghwp';

    protected static $default = '';

    /** @var null|string */
    protected static $inputType = null;

    /** @var null|string */
    protected static $sanitizer = null;

    public function getKey(): string
    {
        return static::$key;
    }

    public function getLabel(): string
    {
        return __(static::$label, static::$labelNamespace);
    }

    public static function _getDefault()
    {
        return static::$default;
    }

    public function getDefault()
    {
        return static::_getDefault();
    }

    public function getInputType(): ?string
    {
        return static::$inputType;
    }

    public function getSanitizer(): ?callable
    {
        return static::$sanitizer;
    }

    public function getOptions(): ?array
    {
        return null;
    }

    public function getActiveCallback(): ?callable
    {
        return null;
    }

    public function getConfig(): array
    {
        $result = [
            'label' => $this->getLabel(),
            'type' => $this->getInputType(),
            'default' => $this->getDefault(),
        ];
        if (static::$sanitizer !== null) {
            $result['sanitize'] = $this->getSanitizer();
        }
        if ($this->getActiveCallback() !== null) {
            $result['active_callback'] = $this->getActiveCallback();
        }
        if ($this->getOptions() !== null) {
            $result['options'] = $this->getOptions();
        }

        return $result;
    }

    public static function getValue()
    {
        return CustomizerSettings::getValue(static::$key, static::_getDefault());
    }
}
