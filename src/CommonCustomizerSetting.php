<?php

namespace Gebruederheitz\Wordpress\Customizer;

/**
 * @template ValueType
 * @implements CustomizerSetting<ValueType>
 */
abstract class CommonCustomizerSetting implements CustomizerSetting
{
    /** @var ValueType */
    protected $default = '';

    protected ?string $inputType = null;

    /** @var ?callable-string  */
    protected ?string $sanitizer = null;

    abstract public function getKey(): string;

    abstract public function getLabel(): string;

    /**
     * @return ValueType
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function getInputType(): ?string
    {
        return $this->inputType;
    }

    public function getSanitizer(): ?callable
    {
        return $this->sanitizer;
    }

    /**
     * @return ?array<string, string>
     */
    public function getOptions(): ?array
    {
        return null;
    }

    public function getActiveCallback(): ?callable
    {
        return null;
    }

    /**
     * @return ValueType
     */
    public function getValue()
    {
        return CustomizerSettings::getValue(
            $this->getKey(),
            $this->getDefault(),
            $this->getSanitizer(),
        );
    }
}
