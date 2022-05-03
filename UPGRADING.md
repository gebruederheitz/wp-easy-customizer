# Upgrading

## From 1.x to 2.0

Instead of instantiating `CustomizerSetting` with its configuration and 
returning an array of those instances from `AbstractCustomizerSettingsHandler::getSettings()`,
you will now have to implement what is now the interface `CustomizerSetting` (or 
extend the  abstract class `BasicCustomizerSetting`) for each setting, and return 
an array of CustomizerSetting instances from `getSettings()`:

### Before

```php
class MySettings extends AbstractCustomizerSettingsHandler
{
    private const fieldKey = 'my-field-key';
    
    public function getSettings(): array
    {
        return [
            new CustomizerSetting(
                self::fieldKey,
                __('Translated field label', 'i18n-namespace'),
                self::getDefault(self::fieldKey),
                'sanitize_text_field',
                'select',
                [
                    OptionEnum::VALUE => 'Label',
                    OptionEnum::OTHER_VALUE => 'Other Label',
                ],
                [self::class, 'isFieldVisible']   
            ),
        ];
    }
    
    protected function getDefault(string $field)
    {
        switch ($field) {
            case self::fieldKey:
                return 'default value';
            default:
                return null;
        }
    }
    
    public static function isFieldVisible(): bool
    {
        return self::isOtherFieldEnabled();
    }
}
```

### After

```php
class MyIndividualSetting extends BasicCustomizerSetting
{
    protected static $key = 'my-field-key';
    protected static $label = 'Translated field label';
    // Optional: i18n namespace, defaults to 'ghwp'
    protected static $labelNamespace = 'i18n-namespace';
    // Optional: default value, defaults to ''
    protected static $default = 'default value';
    // Optional: input type
    protected static $inputType = 'select';
    // Optional: sanitizer callback
    protected static $sanitizer = 'sanitize_text_field';
    // Optional: input or select options
    public function getOptions(): ?array 
    {
        return [
            OptionEnum::VAL_ONE => 'Label',
            OptionEnum::OTHER_VALUE => 'Other Label',
        ];
    }
    // Optional: active callback
    public function getActiveCallback(): ?callable 
    {
        return [OtherOption::class, 'getValue'];
    }
}

class MySettings extends AbstractCustomizerSettingsHandler
{
    public function getSettings(): array
    {
        return [
            new MyIndividualSetting(),
        ];
    }
}
```
