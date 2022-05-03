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

For information on migrating from v1.x [see UPGRADING.md](https://github.com/gebruederheitz/wp-easy-customizer/UPGRADING.md)

To start, you'll have to initialize the `CustomerSettings` object, optionally
passing a label for the main panel. Then you can start adding sections to
that main panel.

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


### Defining Settings & Settings Handlers

First off, you will have to define your settings. Each setting is a class 
implementing `CustomizerSetting`. You can extend `BasicCustomizerSetting`
for convenience:

```php
use \Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

class TelephoneNo extends BasicCustomizerSetting 
{
    protected static $key = 'prefix_company_info_telephone';
    protected static $label = 'Telephone No';
}
```

Now you will have to implement a settings handler to register this setting.
Settings handlers must implement `CustomizerSettingsHandlerInterface`. You can 
extend `AbstractCustomizerSettingsHandler` for convenience:

```php
use Gebruederheitz\Wordpress\Customizer\AbstractCustomizerSettingsHandler;

class CompanyInformation extends AbstractCustomizerSettingsHandler
{
    /*
     * Implement getSettings(), returning an array of CustomizerSetting implementations
     */
    protected function getSettings(): array
    {
        return [
            new TelephoneNo(),
        ];
    }
}
```

This is the object you're going to pass to the `CustomizerSection`'s constructor,
as shown above.


### Settings

You can do more with settings & handlers than the basic example above. Here are
some more detailed usages:

```php
use Gebruederheitz\Wordpress\Customizer\AbstractCustomizerSettingsHandler;
use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

/**
 * A setting with an alternative input type and explicit default value
 */
class ShowAddress extends BasicCustomizerSetting
{
    protected static $key = 'prefix_company_info_show_address';
    protected static $label = 'Show address in footer';
    // Optional: i18n namespace, defaults to 'ghwp'. This example results in the
    // label `__('Show address in footer', 'i18n-namespace')`.
    protected static $labelNamespace = 'i18n-namespace';
    // Optional: default value, defaults to ''
    protected static $default = false;
    // Optional: input type
    protected static $inputType = 'checkbox';
}

/**
 * A setting with an alternative sanitizer
 */
class StreetAddress extends BasicCustomizerSetting
{
    protected static $key = 'prefix_company_info_street_address';
    protected static $label = 'Street address';
    protected static $sanitizer = 'sanitize_text_field';
}

/**
 * A setting using a select field
 */
class SupportIcon extends BasicCustomizerSetting
{
    protected static $key = 'prefix_company_info_support_icon';
    protected static $label = 'Support icon to use';
    protected static $sanitizer = 'sanitize_text_field';
    protected static $inputType = 'select';
    
    public function getOptions: ?array
    {
        return [
            ExampleSelectValues::FIRST => 'Label for first option',
            ExampleSelectValues::SECOND => 'Label for the second option',
        ];
    }

/**
 * A setting for selecting a page from the current site, which is only visible
 * if the switch "ShowAddress" is active.
 */
class ContactPage extends BasicCustomizerSetting
{
    protected static $key = 'prefix_company_info_contact_page';
    protected static $label = 'Contact page';
    protected static $inputType = 'dropdown-pages';
   
    public function getActiveCallback() : ?callable
    {
        // Using an anonymous callback 
        return function () {
            return CustomizerSettings::getValue(
                ShowAddress::getKey(),
                ShowAddress::getDefault()
            );
        }
        
        // Using a class method
        return [ShowAddress::class, 'getValue'] // getValue() has a default implementation in BasicCustomizerSetting
    }
}

class CompanyInformation extends AbstractCustomizerSettingsHandler
{
    /*
     * Implement getSettings(), returning an array of CustomizerSetting implementations
     */
    protected function getSettings(): array
    {
        return [
            new StreetAddress(),
            new ShowAddress(),
            new SupportIcon(),
            new ContactPage(),
        ];
    }
    
    /**
     * Use CustomizerSettings::getValue() to retrieve the stored setting.
     * Defining a public, static accessor method like this serves to encapsulate
     * all the logic surrounding the setting inside a handler class.
     *
     * This is the same implementation as in BasicCustomizerSetting::getValue(),
     * so you could also simply use TelephoneNo::getValue() in your templates. 
     */
    public static function getTelephoneNo(): string
    {
        return TelephoneNo::getValue();
    }
}
```


### Customizing which panels are removed

By default, `CustomizerSettings` "cleans up" the Customizer, removing some
panels that are rarely used. You can extend the `CustomizerSettings` class and 
override the `declutterCustomizer()` method to control which of the default 
panels are removed:

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
by returning the FQCN from `CustomizerSetting::getInputType()`:

```php
class MyCustomizeControl extends WP_Customize_Control {
    /* ... */
}

class MyCustomSetting implements CustomizerSetting
{
    /* ... */
    
    protected static $inputType = MyCustomizeControl::class;
}
```

### Using the Separator field

The separator custom field allows inserting static separations between settings
in the shape of a `<hr>`.
Optionally you can specify a label that will render an `<h2>` underneath the
horizontal rule. The easiest way is to use the `SeparatorSetting` class:

```php
use Gebruederheitz\Wordpress\Customizer\InputTypes\SeparatorSetting;

$settings = [
    // A plain horizontal rule with 2em vertical margin
    new SeparatorSetting('some-unique-id-for-this-separator'),
    new TelephoneNo(),
    // With custom margin of 3em
    new SeparatorSetting(
        'sep-with-custom-margin',
        null,
        [
            'margin' => 3,
        ]
    ),
    // with a heading in the default color #08d
    new SeparatorSetting(
        'sep-general-settings',
        __('General Settings', 'namespace')
    ),
    // with heading in a custom color
    new SeparatorSetting(
        'some-unique-id-for-this-separator',
        'Heading',
        [
            'color' => 'hotpink',
        ]       
    ),
    // with heading in a custom color and custom margin
    // hr bottom margin is calc(${customMargin}em + 2em) to compensate for
    // the heading's margin collapsing
    new SeparatorSetting(
        'some-unique-id-for-this-separator',
        'Heading'
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
