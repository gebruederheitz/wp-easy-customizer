<?php

namespace Gebruederheitz\Wordpress\Customizer;

use Gebruederheitz\SimpleSingleton\SingletonAble;

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
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $result = [
            'label' => $this->getLabel(),
            'type' => $this->getInputType(),
            'default' => $this->getDefault(),
        ];

        if ($this->getSanitizer() !== null) {
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
