<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\CommonCustomizerSetting;
use Gebruederheitz\Wordpress\Customizer\CustomControl\SeparatorCustomControl;
use Gebruederheitz\Wordpress\Customizer\CustomizerSetting;

/**
 * @extends CommonCustomizerSetting<null>
 */
class SeparatorSetting extends CommonCustomizerSetting
{
    public static function get(): CustomizerSetting
    {
        return new static(hash('sha256', (string) time()));
    }

    /**
     * @param ?array<string, string> $options
     */
    public static function factory(
        string $key,
        ?string $label = null,
        ?array $options = null
    ): self {
        return new static($key, $label, $options);
    }

    public string $key;

    /** @var ?array<string, string>  */
    protected ?array $options = null;

    protected ?string $label = null;

    protected $default = null;

    protected ?string $inputType = SeparatorCustomControl::class;

    /**
     * @param ?array<string, string> $options
     */
    final public function __construct(
        string $key,
        ?string $label = null,
        ?array $options = null
    ) {
        $this->key = $key;

        if ($label !== null) {
            $this->label = $label;
        }

        if ($options !== null) {
            $this->options = $options;
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label ?: '';
    }

    public function getOptions(): ?array
    {
        return $this->options ?: null;
    }

    public function getValue()
    {
        return null;
    }
}
