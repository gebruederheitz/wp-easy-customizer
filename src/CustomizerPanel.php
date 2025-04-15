<?php

namespace Gebruederheitz\Wordpress\Customizer;

use WP_Customize_Control;
use WP_Customize_Media_Control;
use WP_Customize_Manager;

/**
 * @phpstan-import-type ValueType from CustomizerSetting
 * @phpstan-import-type Field from CustomizerSetting
 * @phpstan-import-type Fields from CustomizerSection
 * @phpstan-import-type Section from CustomizerSection
 * @phpstan-import-type SectionsBySectionId from CustomizerSection
 *
 */
class CustomizerPanel
{
    /**
     * @hook Filter hook called with an array of customizer fields
     */
    public const HOOK_GET_FIELDS = 'ghwp_customizer_get_fields_';

    /**
     * @hook Filter hook called with an array of customizer sections
     */
    public const HOOK_GET_SECTIONS = 'ghwp_customizer_get_sections_';

    protected const PANEL_ID_PREFIX = 'ghwp_customizer_panel_';

    protected string $panelId;
    protected string $title = 'Theme Settings';
    protected ?WP_Customize_Manager $customizerManager = null;

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
        $this->customizerManager = $wp_customize;

        $fields = $this->getFields();

        $this->customizerManager->add_panel($this->panelId, [
            'title' => $this->title,
        ]);

        foreach ($fields as $section_key => $section_content) {
            $this->registerSection($section_key, $section_content);
        }
    }

    /**
     * @param Section $content
     */
    protected function registerSection(string $key, array $content): void
    {
        $title = is_string($content['label']) ? $content['label'] : '';
        $description =
            isset($content['description']) && is_string($content['description'])
                ? $content['description']
                : '';

        if ($this->customizerManager) {
            $this->customizerManager->add_section($key, [
                'priority' => 500,
                'theme_supports' => '',
                'title' => $title,
                'panel' => $this->panelId,
                'description' => $description,
            ]);

            if (is_array($content['content'])) {
                foreach ($content['content'] as $controlKey => $value) {
                    $this->registerControl($controlKey, $value, $key);
                }
            }
        }
    }

    /**
     * @param ValueType $value
     */
    protected function registerControl(
        string $key,
        $value,
        string $section
    ): void {
        if (!$this->customizerManager) {
            return;
        }

        $this->customizerManager->add_setting($key, [
            'default' => is_array($value) ? $value['default'] ?? '' : '',
            'type' => 'theme_mod',
        ]);

        $type = is_array($value) ? ($value['type'] ?: 'text') : 'text';
        $controlComponentClass = $this->getControlClass($type);

        $componentOptions = [
            'label' => is_array($value) ? $value['label'] : $value,
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

        /** @var WP_Customize_Control $controlComponent */
        $controlComponent = new $controlComponentClass(
            $this->customizerManager,
            $key . '_control',
            $componentOptions,
        );

        $this->customizerManager->add_control($controlComponent);
    }

    /**
     * @return SectionsBySectionId
     */
    protected function getFields(): array
    {
        $fields = [];

        // The sections registered directly with this panel instance do not
        // subscribe to the filter hooks, so their methods are called directly.
        foreach ($this->sections as $section) {
            $section->onGetSections($fields);
            $section->onGetFields($fields);
        }

        $fields = apply_filters(
            static::HOOK_GET_SECTIONS . $this->panelId,
            $fields,
        );
        $fields = apply_filters(
            static::HOOK_GET_FIELDS . $this->panelId,
            $fields,
        );

        return $fields;
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
