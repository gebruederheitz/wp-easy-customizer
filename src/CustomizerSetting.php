<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer;

/**
 * @phpstan-type ValueType string|array|bool|int|mixed
 * @phpstan-template V of string|array|bool|int|mixed = string|array|bool|int|mixed
 */
interface CustomizerSetting
{
    /** @return static */
    public static function get(): CustomizerSetting;

    public function getKey(): string;

    /** @return V */
    public function getDefault();

    public function getLabel(): string;

    /** @return string|class-string */
    public function getInputType(): ?string;

    public function getSanitizer(): ?callable;

    /** @return array<string, string> */
    public function getOptions(): ?array;

    public function getActiveCallback(): ?callable;

    /** @return V */
    public function getValue();
}
