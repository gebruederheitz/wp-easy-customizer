<?php

namespace Gebruederheitz\Wordpress\Customizer;

interface MediaCustomizerSetting extends CustomizerSetting
{
    /** @return 'media' */
    public function getInputType(): string;
    public function getMimeType(): string;
}
