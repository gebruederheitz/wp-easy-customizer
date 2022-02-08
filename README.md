# Wordpress Easy Customizer

_A simplified, object-oriented interface for the Wordpress Customizer._

---

## Installation

via composer:
```shell
> composer require gebruederheitz/wp-easy-customizer
```

Make sure you have Composer autoload or an alternative class loader present.

## Usage


```php
# functions.php (or controller class)
use Gebruederheitz\Wordpress\Customizer\CustomizerSettings;
use Gebruederheitz\Wordpress\Customizer\CustomizerSection;

// If your settings handler implement their getters as static methods, you will
// only need to instantiate the whole bunch on the customizer page:
if (is_customize_preview()) {
    // Set up a new customizer panel with the title 'My Settings'
    // It will also 'clean up' the Customizer by removing some panels (see below)
    new CustomizerSettings('My Settings');
    
    // Create a "section", i.e. a sub-panel / menu entry containing individual
    // settings
    new CustomizerSection(
        // A unique ID for the section
        'ghwp_general_settings',
        // The title of the section / panel
        'General Settings',
        // An optional description shown on the top of the open panel
        null,
        // Some settings handlers, more information below
        [
            new CompanyInformation(),
        ]
    );

    new CustomizerSection(
        'ghwp_language_settings',
        'Language Settings',
        'Languages hidden in switcher menu:',
        [
            new LanguageSwitcherLanguages(),
        ]
    );
    
    // You can also create a section without any handlers and then add them 
    // later:
    $consentManagementSection = new CustomizerSection(
        'ghwp_consent_management_settings',
        'Consent Management'
    );
    
    $consentManagementSection->addSettingsHandler(new ConsentManagement);
    $consentManagementSection->addSettingsHandlers(
        [
            new OtherConsentManagement(),
            new ExtendedConsentManagement(),
        ]   
    );

```

### Defining Settings Handlers

Settings handlers must implement `CustomizerSettingsHandlerInterface`. You can 
extend `AbstractCustomizerSettingsHandler` for convenience:

```php
use Gebruederheitz\Wordpress\Customizer\AbstractCustomizerSettingsHandler;

class CompanyInformation extends AbstractCustomizerSettingsHandler
{
    /* Define DB indices where the settings are stored */
    private static $telephoneNo = 'ghwp_company_info_telephone';
    private static $streetAddress = 'ghwp_company_info_street_address';
    private static $postalCode = 'ghwp_company_info_postal_code';
    private static $addressCountry = 'ghwp_company_info_address_country';
    private static $addressLocality = 'ghwp_company_info_address_locality';
    
    /*
     * Implement getSettings(), returning an array of CustomizerSetting objects
     */
    protected function getSettings(): array
    {

        return [
            new CustomizerSetting(
                // field id
                self::$telephoneNo,
                // label
                __('Telephone No', 'ghwp'),
                // default (fallback) value
                self::getDefault(self::$telephoneNo),
                // sanitizer callback
                'sanitize_text_field',
            ),

            new CustomizerSetting(
                self::$streetAddress,
                __('Street Address', 'ghwp'),
                self::getDefault(self::$streetAddress),
                'sanitize_text_field',
            ),

            new CustomizerSetting(
                self::$postalCode,
                __('Postal Code', 'ghwp'),
                self::getDefault(self::$postalCode),
                'sanitize_text_field',
            ),

            new CustomizerSetting(
                self::$addressCountry,
                __('Country', 'ghwp'),
                self::getDefault(self::$addressCountry),
                'sanitize_text_field',
            ),

            new CustomizerSetting(
                self::$addressLocality,
                __('Town / City', 'ghwp'),
                self::getDefault(self::$addressLocality),
                'sanitize_text_field',
            ),
            
            new CustomizerSetting(
                self::$showAddress,
                __('Show address', 'ghwp'),
                self::getDefault(self::$showAddress),
                null,
                // input type
                'checkbox'
            ),

            new CustomizerSetting(
                self::$exampleSelectField,
                __('Support Icon', 'ghwp'),
                self::getDefault(self::$exampleSelectField),
                'sanitize_text_field',
                // select input type with options
                'select', 
                [
                    ExampleSelectValues::FIRST => 'Label for first option',
                    ExampleSelectValues::SECOND => 'Label for the second option',
                ]
            ),
            
            new CustomizerSetting(
                self::$contactPage,
                __('Contact page', 'ghwp'),
                self::getDefault(self::$contactPage),
                null,
                'dropdown-pages',
                null,
                // visibility callback (field is only visible if the function
                // returns true
                [self::class, 'isAddressShown']
            ),
        ];
    }
    
    /**
     * OPTIONAL EXAMPLE
     * 
     * A static method used as a visibility callback
     */
    public static function isAddressShown() {
        return CustomizerSettings::getValue(
            self::$showAddress,
            self::getDefault(self::$showAddress)
        );
    }
    
    /**
     * Use CustomizerSettings::getValue() to retrieve the stored setting.
     * Defining a public, static accessor method like this serves to encapsulate
     * all the logic surrounding the setting inside a handler class. 
     */
    public static function getTelephoneNo(): string
    {
        return CustomizerSettings::getValue(
            self::$telephoneNo,
            self::getDefault(self::$telephoneNo)
        );
    }
    
    /**
     * OPTIONAL EXAMPLE
     * 
     * One possible way to store default values for each individual setting.
     */
    private static function getDefault($key)
    {
        switch ($key) {
            case self::$telephoneNo:
            case self::$streetAddress:
            case self::$postalCode:
            case self::$addressLocality:
                return '';
            case self::$addressCountry:
                return 'DE';
            case self::$showAddress:
                return true;
            default:
                return null;
        }
    }
}
```


### Customizing which panels are removed

You can extend the `CustomizerSettings` class and override the `declutterCustomizer()`
method to control, which of the default panels are removed:

```php
class MyCustomizerSettings extends \Gebruederheitz\Wordpress\Customizer\CustomizerSettings
{
    protected function declutterCustomizer(WP_Customize_Manager $wp_customize)
    {
        $wp_customize->remove_panel( 'themes' );
        $wp_customize->remove_section( 'static_front_page' );
        $wp_customize->remove_section( 'custom_css' );
        $wp_customize->remove_control( 'custom_logo' );
        $wp_customize->remove_control( 'site_icon' );
    }
}
```


### Using Custom Controls

You can use custom controls if they extend the default `WP_Customize_Control`
by passing the FQCN as the type for `CustomizerSetting`:

```php
class MyCustomizeControl extends WP_Customize_Control {
    /* ... */
}

$customSetting = new CustomizerSetting(
    $fieldId,
    $label,
    null
    null,
    MyCustomizeControl::class, 
);
```

### Using the Separator field

The separator custom field allows inserting static separations between settings
in the shape of a `<hr>`.
Optionally you can specify a label that will render an `<h2>` underneath the
horizontal rule.

```php
use Gebruederheitz\Wordpress\Customizer\CustomControl\SeparatorCustomControl;

$settings = [
    // A plain horizontal rule with 2em vertical margin
    new CustomizerSetting(
        'some-unique-id-for-this-separator',
        null,
        null
        null,
        SeparatorCustomControl::class, 
    ),
    // With custom margin of 3em
    new CustomizerSetting(
        'sep-with-custom-margin',
        null,
        null
        null,
        SeparatorCustomControl::class,
        [
            'margin' => 3,
        ]
    ),
    // with a heading in the default color #08d
    new CustomizerSetting(
        'sep-general-settings',
        __('General Settings', 'namespace'),
        null
        null,
        SeparatorCustomControl::class, 
    ),
    // with heading in a custom color
    new CustomizerSetting(
        'some-unique-id-for-this-separator',
        null,
        null
        null,
        SeparatorCustomControl::class, 
        [
            'color' => 'hotpink',
        ]       
    ),
    // with heading in a custom color and custom margin
    // hr bottom margin is calc(${customMargin}em + 2em) to compensate for
    // the heading's margin collapsing
    new CustomizerSetting(
        'some-unique-id-for-this-separator',
        null,
        null
        null,
        SeparatorCustomControl::class, 
        [
            'color' => 'hotpink',
            'margin' => 1,
        ]       
    ),
];
```


## Development

### Dependencies

- PHP >= 7.4
- [Composer 2.x](https://getcomposer.org)
- [NVM](https://github.com/nvm-sh/nvm) and nodeJS LTS (v16.x)
- Nice to have: GNU Make (or drop-in alternative)
