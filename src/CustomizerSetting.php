<?php

namespace Gebruederheitz\Wordpress\Customizer;

class CustomizerSetting
{
    /** @var string */
    protected $label;

    /** @var ?string  */
    protected $type;

    /** @var string|callable|null  */
    protected $sanitizer;

    /** @var mixed|null */
    protected $default;

    /** @var ?array */
    protected $options;

    /** @var string|callable|null  */
    protected $activeCallback;

    /** @var string */
    protected $metaFieldName;

    /**
     * CustomizerSetting constructor.
     *
     * @param string $metaFieldName
     * @param        $label
     * @param        $default
     * @param null   $sanitizer
     * @param string $type
     * @param null   $options
     * @param null   $activeCallback
     */
    public function __construct(
        string $metaFieldName,
        $label,
        $default,
        $sanitizer = null,
        $type = 'text',
        $options = null,
        $activeCallback = null
    ) {
        $this->label          = $label;
        $this->type           = $type;
        $this->sanitizer      = $sanitizer;
        $this->default        = $default;
        $this->options        = $options;
        $this->activeCallback = $activeCallback;
        $this->metaFieldName = $metaFieldName;
    }

    /**
     * @return string
     */
    public function getMetaFieldName(): string
    {
        return $this->metaFieldName;
    }

    /**
     * @param string $metaFieldName
     *
     * @return CustomizerSetting
     */
    public function setMetaFieldName(string $metaFieldName): CustomizerSetting
    {
        $this->metaFieldName = $metaFieldName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     *
     * @return CustomizerSetting
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return CustomizerSetting
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSanitizer()
    {
        return $this->sanitizer;
    }

    /**
     * @param mixed $sanitizer
     *
     * @return CustomizerSetting
     */
    public function setSanitizer($sanitizer)
    {
        $this->sanitizer = $sanitizer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return CustomizerSetting
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActiveCallback()
    {
        return $this->activeCallback;
    }

    /**
     * @param mixed $activeCallback
     *
     * @return CustomizerSetting
     */
    public function setActiveCallback($activeCallback)
    {
        $this->activeCallback = $activeCallback;

        return $this;
    }

    public function getConfig(): array
    {
        $result = [
            'label' => $this->label,
            'type' => $this->type,
            'default' => $this->default,
        ];
        if (isset($this->sanitizer)) {
            $result['sanitize'] = $this->sanitizer;
        }
        if (isset($this->activeCallback)) {
            $result['active_callback'] = $this->activeCallback;
        }
        if (isset($this->options)) {
            $result['options'] = $this->options;
        }

        return $result;
    }
}
