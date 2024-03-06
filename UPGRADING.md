# Upgrading

## From 2.x to 3.0

Support for PHP 7.3 has been removed.

### CustomizerSettings Singleton and CustomizerPanel

`CustomizerSettings` has been turned into a utility singleton, that will not 
usually have to use anymore. Its previous main functionality has been replaced 
by `CustomizerPanel`, allowing you to create multiple top-level panels instead
of just the one. Here's a quick before & after:

```php
use Gebruederheitz\Wordpress\Customizer\CustomizerSection;

/* BEFORE --------------------------------------------------------------------*/
/* ==========================================================================*/

use Gebruederheitz\Wordpress\Customizer\CustomizerSettings;

// Initialize the customizer handling, optionally with a namespace for i18n (__())
new CustomizerSettings('My Settings', 'my-gettext-namespace');
// Create sections
new CustomizerSection('my-section', 'My Section')

/* AFTER --------------------------------------------------------------------*/
/* ==========================================================================*/

use Gebruederheitz\Wordpress\Customizer\CustomizerPanel;

// Initialize the customizer handling & create a first panel
$panel = new CustomizerPanel('my_main_panel', 'My Settings');

// Use one of the three methods to add sections to that panel, providing
// translated labels (see section about i18n below)
CustomizerSection::factory(
    'my-section', 
    __('My Section', 'my-gettext-namespace')
 )->setPanel($panel); 

// or 
$panelId = $panel->getId();
$section->setPanel($panelId);

// or
$panel->addSections($section)
```


### SettingsHandlers are no more

Because `CustomizerSettingsHandler` and `AbstractCustomizerSettingsHandler` were
pitiful crutches compensating for imperfect architecture, their functionality 
has been removed. Setting are now added directly to sections.

You may continue to use `AbstractCustomizerSettingsHandler` for semantic 
purposes (logically grouping settings, providing value getters), but it has been
marked deprecated and the interface has been removed.

```php
// Before
/* ==========================================================================*/
class MySettingsHandler {
     public function getSettings(): array {
        return [new MySetting(), new AnotherSetting()];
    }
}
class MyOtherSettingsHandler {
     public function getSettings(): array {
        return [new ThirdSetting];
    }
}
new CustomizerSection($slug, $label, $description, [
    new MySettingsHandler(),
    new MyOtherSettingsHandler(),
]);


// After
/* ==========================================================================*/
class MyOtherSettingsHandler {
     public function getSettings(): array {
        // More about settings further down
        return [ThirdSetting::get()];
    }
}
new CustomizerSection($slug, $label, $description, [
    MySetting::get(),
    AnotherSetting::get(),
    ...(new MyOtherSettingsHandler())->getSettings(),
]);
```


### Settings look a bit different

The static class attributes for key & label in `BasicCustomizerSettings` have
been replaced with abstract getters, and all static class attributes & functions
are removed in favour of dynamic equivalents. 
They are now singletons, enabling us to remove some the previous hacky 
workarounds. They are instantiated and retrieved using the static method `get()`.

```php
use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;
use Gebruederheitz\Wordpress\Customizer\CustomizerSection;

/* BEFORE --------------------------------------------------------------------*/
/* ==========================================================================*/

class MyFullyCustomSetting extends BasicCustomizerSetting {
    protected static $key = 'my-key';
    protected static $label = 'A field label';
    protected static $labelNamespace = 'my-gettext-namespace';
    protected static $default = false;
    protected static $inputType = 'checkbox';
    protected static $sanitizer = 'rest_sanitize_boolean';
}

// Retrieving the value
$value = MyFullyCustomSetting::getValue();

// Adding to a settings handler
class MySettingsHandler {
    public function getSettings(): array 
    {
        return [
            new MyFullyCustomSetting(),
            /* ... more settings ... */       
        ]   
    }
}
])

/* AFTER --------------------------------------------------------------------*/
/* ==========================================================================*/

class MyFullyCustomSetting extends BasicCustomizerSetting {
    // As the getters for key and label are abstract methods in BasicCustomizerSetting,
    // your IDE will auto-generate stubs for you. PHP will also warn you should
    // you forget to set one of these essential properties.
    public function getKey(): string {
        return 'my-key';
    }
    
    public function getLabel(): string {
        // Another advantage: You can use expressions, allowing you to translate
        // the label reliably.
        return __('A field label', 'my-gettext-namespace');
    }
    
    // All i18n-related functionality has been removed
    // ~~protected static $labelNamespace = 'my-gettext-namespace';~~
    
    protected $default = false;
    protected ?string $inputType = 'checkbox';
    protected ?string $sanitizer = 'rest_sanitize_boolean';
    
    // Alternative: you can override getSanitizer() if a callable-string is not
    // an option 
    public  function getSanitizer(): ?callable
    {
        return fn($v) => intval($v) > 0 ? intval($v) : 0;
    } 
}

// Retrieving the value
$value = MyFullyCustomSetting::get()->getValue();

// Adding to a section (or settings handler, see above)
new CustomizerSection($slug, $label, $description, [
    MyFullyCustomSetting::get(),
    /* ... more settings ... */
])
```


### Decluttering has moved to a filter hook

If you have customized the decluttering functionality as previously described in
the docs, there is now a simpler and slightly more versatile alternative
[as described in the documentation](https://github.com/gebruederheitz/wp-easy-customizer/blob/main/README.md#customizing-which-panels-are-removed). The previous method will fail to work
because `CustomizerSettings` is now a singleton instantiated by any panel object
and thus can not be usefully extended.


### i18n namespaces are gone

Because calling the gettext functions (`__(), _x(), _e()`) with runtime 
variables – as this library has previously done – makes little to no sense, any
built-in i18n functionality has been removed. As is visible in the before & 
after above, you will have to provide translated labels and descriptions
yourself – that way the gettext parser will know which strings to add to which
namespace when creating translations.


### SeparatorSetting parameters need to be provided differently

As the `SeparatorSetting` is also a `BasicCustomizerSetting` and therefore is a
singleton with only a protected constructor, you will need to pass its 
parameters using the public method `configure()`: 
`SeparatorSetting::get()->configure('id', __('Label', 'ns'), $options)`.




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
