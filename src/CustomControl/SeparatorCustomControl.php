<?php

namespace Gebruederheitz\Wordpress\Customizer\CustomControl;

use WP_Customize_Control;

class SeparatorCustomControl extends WP_Customize_Control
{
    public function render_content()
    {
        $margin = $this->choices['margin'] ?? 2;
        $color = $this->choices['color'] ?? '#08d';

        $hrStyle = "margin: ${margin}em 0";
        if (!empty($this->label)) {
            $hrStyle .= " calc(${margin}em + 2em) 0;";
        }

        echo "<hr style='$hrStyle'>";

        if (!empty($this->label)) {
            echo "<h2 style='font-variant: small-caps; text-align: center; font-size: 1.75em; color: ${color};'>$this->label</h2>";
        }
    }
}
