<?php

namespace Gebruederheitz\Wordpress\Customizer;

/**
 * @phpstan-import-type ValueType from CustomizerSetting
 */
class CustomizerSection
{
    protected string $slug = '';

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
        }

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getLabel(): string
    {
        return $this->label ?? '';
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    /**
     * @return CustomizerSetting[]
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param CustomizerSection[] $sections
     * @return CustomizerSection[]
     */
    public function onGetSections(array $sections): array
    {
        return array_merge($sections, [$this]);
    }

    /**
     * @param CustomizerSetting<ValueType> ...$settings
     */
    public function addSettings(CustomizerSetting ...$settings): self
    {
        array_push($this->settings, ...$settings);

        return $this;
    }
}
