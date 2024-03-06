<?php

namespace Gebruederheitz\Wordpress\Customizer;

use Gebruederheitz\SimpleSingleton\Singleton;
use WP_Customize_Manager;

class CustomizerSettings extends Singleton
{
    public const HOOK_FILTER_DECLUTTER_ITEMS = 'ghwp_customizer_filter_declutter_items';

    protected function __construct()
    {
        parent::__construct();

        add_action('customize_register', [$this, 'onCustomizeRegister']);
    }

    public function onCustomizeRegister(
        WP_Customize_Manager $wp_customize
    ): void {
        $this->declutterCustomizer($wp_customize);
    }

    /**
     * @param null|string|int|mixed $default
     * @param callable-string|callable|null  $sanitizer
     *
     * @return mixed
     */
    public static function getValue(
        string $key,
        $default = null,
        $sanitizer = null
    ) {
        $value = get_theme_mod($key, $default);
        if (isset($sanitizer)) {
            $value = call_user_func($sanitizer, $value);
        }

        return $value;
    }

    protected function declutterCustomizer(
        WP_Customize_Manager $customizerManager
    ): void {
        $declutter = [
            'panels' => ['themes'],
            'sections' => ['static_front_page', 'custom_css'],
            'controls' => ['custom_logo', 'site_icon'],
        ];

        $filtered = apply_filters(
            self::HOOK_FILTER_DECLUTTER_ITEMS,
            $declutter,
        );

        foreach ($filtered['panels'] as $panelSlug) {
            $customizerManager->remove_panel($panelSlug);
        }

        foreach ($filtered['sections'] as $sectionSlug) {
            $customizerManager->remove_section($sectionSlug);
        }

        foreach ($filtered['controls'] as $controlSlug) {
            $customizerManager->remove_control($controlSlug);
        }
    }
}
