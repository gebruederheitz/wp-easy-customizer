<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\Customizer;

interface CustomizerSetting
{
    public function getKey(): string;
    public function getDefault();
    public function getLabel(): string;
    public function getInputType(): ?string;
    public function getSanitizer(): ?callable;
    public function getOptions(): ?array;
    public function getActiveCallback(): ?callable;
    public function getConfig(): array;
}
