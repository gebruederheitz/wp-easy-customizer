<?php

namespace Gebruederheitz\Wordpress\Customizer;

use WP_Customize_Control;
use WP_Customize_Media_Control;
use WP_Customize_Manager;

/**
 * @phpstan-import-type ValueType from CustomizerSetting
 *
 */
class CustomizerPanel
{
    /**
     * @hook Filter hook called with an array of customizer sections
     */
    public const HOOK_GET_SECTIONS = 'ghwp_customizer_get_sections_';

    protected const PANEL_ID_PREFIX = 'ghwp_customizer_panel_';

    protected string $panelId;
    protected string $title = 'Theme Settings';

    /** @var array<CustomizerSection> */
    protected array $sections = [];

    public function __construct(string $id, ?string $title = null)
    {
        $this->panelId = self::PANEL_ID_PREFIX . sanitize_key($id);

        if ($title !== null) {
            $this->title = $title;
        }

        // Make certain the CustomizerSettings singleton is instantiated in case
        // it has not been manually instantiated by the user, thus ensuring that
        // the hook for declutterCustomizer is always attached.
        // It seems like a good place, as every usage of this library will
        // always require at least one panel instance.
        CustomizerSettings::getInstance();

        add_action('customize_register', [$this, 'onCustomizeRegister']);
    }

    /**
     * Proxy for constructing a new CustomizerSection object with the panel's ID
     * automatically assigned.
     *
     * @param ?array<CustomizerSetting<ValueType>> $settings
     */
    public function addNewSection(
        string $slug,
        string $label,
        string $description = null,
        array $settings = null
    ): self {
        CustomizerSection::factory(
            $slug,
            $label,
            $description,
            $settings,
        )->setPanel($this);

        return $this;
    }

    public function addSections(CustomizerSection ...$sections): self
    {
        array_push($this->sections, ...$sections);

        return $this;
    }

    public function getId(): string
    {
        return $this->panelId;
    }

    public function onCustomizeRegister(
        WP_Customize_Manager $wp_customize
    ): void {
        $customizerManager = $wp_customize;
        $customizerManager->add_panel($this->panelId, [
            'title' => $this->title,
        ]);

        $sections = apply_filters(
            static::HOOK_GET_SECTIONS . $this->panelId,
            $this->sections,
        );

        foreach ($sections as $section) {
            $this->registerSection($customizerManager, $section);
        }
    }

    protected function registerSection(
        WP_Customize_Manager $customizerManager,
        CustomizerSection $section
    ): void {
        $customizerManager->add_section($section->getSlug(), [
            'priority' => 500,
            'theme_supports' => '',
            'title' => $section->getLabel(),
            'panel' => $this->panelId,
            'description' => $section->getDescription(),
        ]);

        foreach ($section->getSettings() as $setting) {
            $this->registerControl($setting, $section, $customizerManager);
        }
    }

    protected function registerControl(
        CustomizerSetting $setting,
        CustomizerSection $section,
        WP_Customize_Manager $customizerManager
    ): void {
        // @phpstan-ignore-next-line (default can be a boolean or an array)
        $customizerManager->add_setting($setting->getKey(), [
            'default' => $setting->getDefault() ?: '',
            'type' => 'theme_mod',
        ]);

        $type = $setting->getInputType() ?: 'text';
        $controlComponentClass = $this->getControlClass($type);

        $componentOptions = [
            'label' => $setting->getLabel() ?: '',
            'section' => $section->getSlug(),
            'settings' => $setting->getKey(),
            'active_callback' => $setting->getActiveCallback() ?: null,
        ];

        if (
            $type === 'media' &&
            is_a($setting, MediaCustomizerSetting::class, false)
        ) {
            $componentOptions['mime_type'] = $setting->getMimeType();
        } else {
            $componentOptions['type'] = $type;
            $componentOptions['sanitize_callback'] =
                $setting->getSanitizer() ?: null;
            $componentOptions['choices'] = $setting->getOptions() ?: null;
        }

        /** @var WP_Customize_Control $controlComponent */
        $controlComponent = new $controlComponentClass(
            $customizerManager,
            $setting->getKey() . '_control',
            $componentOptions,
        );

        $customizerManager->add_control($controlComponent);
    }

    /**
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
}
