<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer\InputTypes;

use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;
use Gebruederheitz\Wordpress\Customizer\CustomControl\SeparatorCustomControl;

/**
 * @extends BasicCustomizerSetting<null>
 */
abstract class SeparatorSetting extends BasicCustomizerSetting
{
    public string $key;

    /** @var ?array<string, mixed>  */
    protected ?array $options = null;

    protected ?string $label = null;

    protected $default = null;

    protected ?string $inputType = SeparatorCustomControl::class;

    /**
     * @param ?array<string, mixed> $options
     */
    public function configure(
        string $key,
        ?string $label = null,
        ?array $options = null
    ): self {
        $this->key = $key;

        if ($label !== null) {
            $this->label = $label;
        }

        if ($options !== null) {
            $this->options = $options;
        }

        return $this;
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
