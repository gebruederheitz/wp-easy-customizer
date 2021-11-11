<?php

namespace Gebruederheitz\Wordpress\Customizer;

class CustomizerSection
{
    private $slug = '';

    private $label = '';

    private $description = '';

    public function __construct(
        string $slug,
        string $label,
        string $description = null,
        array $settingsHandlers = null
    ) {
        $this->slug = $slug;
        $this->label = $label;

        if (!empty($description)) {
            $this->description = $description;
        }

        if (is_array($settingsHandlers)) {
            $this->addSettingsHandlers($settingsHandlers);
        }

        add_filter(CustomizerSettings::HOOK_GET_SECTIONS, [$this, 'onGetSections']);
    }

    public function onGetSections(array $sections)
    {
        $sections[$this->slug] = [
            'label' => $this->label,
            'content' => [],
            'description' => $this->description,
        ];

        return $sections;
    }

    public function addSettingsHandler(CustomizerSettingsHandlerInterface $handler)
    {
        $handler->setSection($this->slug);
    }

    /**
     * @param CustomizerSettingsHandlerInterface[] $handlers
     */
    public function addSettingsHandlers(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->addSettingsHandler($handler);
        }
    }
}
