<?php

namespace Gebruederheitz\Wordpress\Customizer;

use WP_Customize_Control;
use WP_Customize_Manager;

class CustomizerSettings
{
    /**
     * @hook Filter hook called with an array of customizer fields
     */
    public const HOOK_GET_FIELDS = 'ghwp_customizer_get_fields';

    /**
     * @hook Filter hook called with an array of customizer sections
     */
    public const HOOK_GET_SECTIONS = 'ghwp_customizer_get_sections';

    protected static $basePanelId = 'ghwp_customizer_base_panel';

    protected $title = 'Theme Settings';

    public function __construct(string $title = null)
    {
        $this->title = $title;
        add_action('customize_register', [$this, 'onCustomizeRegister']);
    }

    public function onCustomizeRegister(WP_Customize_Manager $wp_customize): void
    {
        $this->declutterCustomizer($wp_customize);

        $fields = $this->getFields();

        $wp_customize->add_panel(static::$basePanelId, ['title' => $this->title]);

        foreach ($fields as $section_key => $section_content) {
            $wp_customize->add_section( $section_key, array(
                'priority'       => 500,
                'theme_supports' => '',
                'title'          => __( $section_content['label'], 'ghwp' ),
                'panel'          => static::$basePanelId,
                'description'  => isset($section_content['description'])
                    ? __($section_content['description'], 'ghwp')
                    : '',
            ) );

            foreach ($section_content['content'] as $key => $value) {
                $wp_customize->add_setting($key, [
                    'default' => is_array($value) ? $value['default'] ?? '' : '',
                    'type' => 'theme_mod'
                ]);

                $type = is_array($value) ? $value['type'] : 'text';
                $controlComponentClass = class_exists($type) ? $type : WP_Customize_Control::class;
                $wp_customize->add_control(
                    new $controlComponentClass(
                        $wp_customize,
                        $key . '_control',
                        [
                            'label' => __(is_array($value) ? $value['label'] : $value, 'ghwp'),
                            'section' => $section_key,
                            'settings' => $key,
                            'type' => $type,
                            'sanitize_callback' => is_array($value) ? $value['sanitize'] ?? null : null,
                            'choices' => is_array($value) ? $value['options'] ?? null : null,
                            'active_callback' => is_array($value) ? $value['active_callback'] ?? null : null,
                        ]
                    )
                );
            }
        }
    }

    /**
     * @param string               $key
     * @param null                 $default
     * @param string|callable|null $sanitizer
     *
     * @return mixed
     */
    public static function getValue(string $key, $default = null, $sanitizer = null)
    {
        $value = get_theme_mod($key, $default);
        if (isset($sanitizer)) {
            $value = call_user_func($sanitizer, $value);
        }

        return $value;
    }

    protected function getFields(): array
    {
        $fields = [];
        $fields = apply_filters(static::HOOK_GET_SECTIONS, $fields);
        $fields = apply_filters(static::HOOK_GET_FIELDS, $fields);

        return $fields;
    }

    protected function declutterCustomizer(WP_Customize_Manager $wp_customize)
    {
        $wp_customize->remove_panel( 'themes' );
        $wp_customize->remove_section( 'static_front_page' );
        $wp_customize->remove_section( 'custom_css' );
        $wp_customize->remove_control( 'custom_logo' );
        $wp_customize->remove_control( 'site_icon' );
    }
}
