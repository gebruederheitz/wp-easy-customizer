<?php

namespace Gebruederheitz\Wordpress\Customizer;

/**
 * @phpstan-import-type ValueType from CustomizerSetting
 * @phpstan-type Sections array<string, array<string, mixed>>
 */
class CustomizerSection
{
    private string $slug = '';

    private string $label = '';

    private string $description = '';

    /** @var array<CustomizerSetting<ValueType>> $settings */
    private array $settings = [];

    /**
     * @param ?array<CustomizerSetting<ValueType>> $settings
     */
    public static function factory(
        string $slug,
        string $label,
        string $description = null,
        array $settings = null
    ): CustomizerSection {
        return new static($slug, $label, $description, $settings);
    }

    /**
     * @param ?array<CustomizerSetting<ValueType>> $settings
     */
    final public function __construct(
        string $slug,
        string $label,
        string $description = null,
        array $settings = null
    ) {
        $this->slug = $slug;
        $this->label = $label;

        if (!empty($description)) {
            $this->description = $description;
        }

        if (is_array($settings)) {
            $this->addSettings(...$settings);
        }
    }

    /**
     * @param CustomizerPanel|string $panel
     */
    public function setPanel($panel): self
    {
        $panelId = null;
        if (is_string($panel)) {
            $panelId = $panel;
        } elseif (is_a($panel, CustomizerPanel::class, false)) {
            $panelId = $panel->getId();
        }

        if ($panelId) {
            add_filter(CustomizerPanel::HOOK_GET_SECTIONS . $panelId, [
                $this,
                'onGetSections',
            ]);

            add_filter(CustomizerPanel::HOOK_GET_FIELDS . $panelId, [
                $this,
                'onGetFields',
            ]);
        }

        return $this;
    }

    /**
     * @param Sections $sections
     * @return Sections
     */
    public function onGetSections(array $sections): array
    {
        $sections[$this->slug] = [
            'label' => $this->label,
            'content' => [],
            'description' => $this->description,
        ];

        return $sections;
    }

    /**
     * @param Sections $sections
     * @return Sections
     */
    public function onGetFields(array $sections): array
    {
        $this->registerSettings($sections);

        return $sections;
    }

    /**
     * @param CustomizerSetting<ValueType> ...$settings
     */
    public function addSettings(CustomizerSetting ...$settings): self
    {
        array_push($this->settings, ...$settings);

        return $this;
    }

    /**
     * @param Sections $fields
     */
    public function registerSettings(array &$fields): void
    {
        foreach ($this->settings as $setting) {
            $fields[$this->slug]['content'][
                $setting->getKey()
            ] = $setting->getConfig();
        }
    }
}
