<?php

namespace Gebruederheitz\Wordpress\Customizer;

abstract class AbstractCustomizerSettingsHandler implements
    CustomizerSettingsHandlerInterface
{
    protected $settingsSection = '';

    public function __construct()
    {
        add_filter(CustomizerSettings::HOOK_GET_FIELDS, [$this, 'onGetFields']);
    }

    public function onGetFields(array $fields): array
    {
        foreach ($this->getSettings() as $setting) {
            $this->addSetting($fields, $setting);
        }

        return $fields;
    }

    public function setSection(string $sectionSlug)
    {
        $this->settingsSection = $sectionSlug;
    }

    /** @return array<CustomizerSetting> */
    abstract protected function getSettings(): array;

    protected function addSetting(array &$fields, CustomizerSetting $setting)
    {
        $fields[$this->settingsSection]['content'][
            $setting->getKey()
        ] = $setting->getConfig();
    }
}
