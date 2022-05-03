<?php

namespace Gebruederheitz\Wordpress\Customizer;

use WP_Customize_Control;
use WP_Customize_Media_Control;
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

    /** @var string */
    protected $title = 'Theme Settings';
    /** @var string  */
    protected $i18nNamespace = 'ghwp';
    /** @var WP_Customize_Manager */
    protected $customizerManager;

    public function __construct(
        ?string $title = null,
        ?string $i18nNamespace = null
    ) {
        if ($title !== null) {
            $this->title = $title;
        }
        if ($i18nNamespace !== null) {
            $this->i18nNamespace = $i18nNamespace;
        }
        add_action('customize_register', [$this, 'onCustomizeRegister']);
    }

    public function onCustomizeRegister(
        WP_Customize_Manager $wp_customize
    ): void {
        $this->customizerManager = $wp_customize;
        $this->declutterCustomizer();

        $fields = $this->getFields();

        $this->customizerManager->add_panel(static::$basePanelId, [
            'title' => $this->title,
        ]);

        foreach ($fields as $section_key => $section_content) {
            $this->addSection($section_key, $section_content);
        }
    }

    /**
     * @param string               $key
     * @param null                 $default
     * @param string|callable|null $sanitizer
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

    protected function addSection(string $key, array $content)
    {
        $this->customizerManager->add_section($key, [
            'priority' => 500,
            'theme_supports' => '',
            'title' => __($content['label'], $this->i18nNamespace),
            'panel' => static::$basePanelId,
            'description' => isset($content['description'])
                ? __($content['description'], $this->i18nNamespace)
                : '',
        ]);

        foreach ($content['content'] as $controlKey => $value) {
            $this->addControl($controlKey, $value, $key);
        }
    }

    protected function addControl($key, $value, $section)
    {
        $this->customizerManager->add_setting($key, [
            'default' => is_array($value) ? $value['default'] ?? '' : '',
            'type' => 'theme_mod',
        ]);

        $type = is_array($value) ? ($value['type'] ?: 'text') : 'text';
        $controlComponentClass = $this->getControlClass($type);

        $componentOptions = [
            'label' => __(
                is_array($value) ? $value['label'] : $value,
                $this->i18nNamespace,
            ),
            'section' => $section,
            'settings' => $key,
            'active_callback' => is_array($value)
                ? $value['active_callback'] ?? null
                : null,
        ];

        if ($type === 'media') {
            $componentOptions['mime_type'] = is_array($value)
                ? $value['mime_type'] ?? null
                : null;
        } else {
            $componentOptions['type'] = $type;
            $componentOptions['sanitize_callback'] = is_array($value)
                ? $value['sanitize'] ?? null
                : null;
            $componentOptions['choices'] = is_array($value)
                ? $value['options'] ?? null
                : null;
        }

        $this->customizerManager->add_control(
            new $controlComponentClass(
                $this->customizerManager,
                $key . '_control',
                $componentOptions,
            ),
        );
    }

    protected function getFields(): array
    {
        $fields = [];
        $fields = apply_filters(static::HOOK_GET_SECTIONS, $fields);
        $fields = apply_filters(static::HOOK_GET_FIELDS, $fields);

        return $fields;
    }

    /**
     * @param string $type
     *
     * @return class-string
     */
    protected function getControlClass(string $type): string
    {
        if (class_exists($type)) {
            return $type;
        }
        if ($type === 'media') {
            return WP_Customize_Media_Control::class;
        }
        return WP_Customize_Control::class;
    }

    protected function declutterCustomizer()
    {
        $this->customizerManager->remove_panel('themes');
        $this->customizerManager->remove_section('static_front_page');
        $this->customizerManager->remove_section('custom_css');
        $this->customizerManager->remove_control('custom_logo');
        $this->customizerManager->remove_control('site_icon');
    }
}
