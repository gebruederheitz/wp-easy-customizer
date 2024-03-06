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

For information on migrating from v1.x or v2.x [see UPGRADING.md](https://github.com/gebruederheitz/wp-easy-customizer/blob/main/UPGRADING.md)

You will need at least one `CustomizerPanel` object to start with, optionally
passing a label (which defaults to "Theme Settings"). Then you can start adding 
sections to your new panel.

```php
# functions.php (or controller class)
use Gebruederheitz\Wordpress\Customizer\CustomizerPanel;

// If your settings handler implement their getters as static methods, you will
// only need to instantiate the whole bunch on the customizer page:
if (is_customize_preview()) {
    // Set up a new customizer panel with the title 'My Settings'
    // It will also 'clean up' the Customizer by removing some panels (see below)
    // The ID is automatically prefixed with 'ghwp_customizer_panel_'.
    $panel = new CustomizerPanel('my_main_panel', 'My Settings');
```

### Adding Sections to a panel

A section is sub-panel or sub-menu which contains the actual settings. There are 
three basic ways to add sections to a panel. Which one you use is mostly a 
matter of taste and code organisation. The main difference is that sections
added via the filter hook (variants (a) & (c), using `$panel->addNewSection()` or
`$section->setPanel()`) are added to the rendered panel _after_ the ones 
directly added (variant (b), using `$panel->addSections()`). So if you require
a specific order of sections, you'll need to make certain these are registered
through the same mechanism.

#### (a) Directly from the panel using the automatic filter hook

```php
use Gebruederheitz\Wordpress\Customizer\CustomizerPanel;

$panel = new CustomizerPanel('my_main_panel')
$panel->addNewSection(
    // A unique ID for the section
    'my_general_settings',
    // The title of the section / panel
    'General Settings',
    // An optional description shown on the top of the open panel
    null,
    // Some settings, more information below
    [
        CompanyInformation::get(),
    ]
)->addNewSection(/* This method can be chained */);
```

This allows for a very compact Customizer setup and is particularly useful if 
you only have a handful of fairly standard settings. The disadvantage is that 
you can not use any custom section classes, as `addNewSection()` will always
instantiate a new vanilla `CustomizerSection`. And since we don't actually 
receive the created section object, we have to add all settings right away.


#### (b) Instance for instance - directly with objects

```php
use Gebruederheitz\Wordpress\Customizer\CustomizerPanel;
use Gebruederheitz\Wordpress\Customizer\CustomizerSection;

$panel = new CustomizerPanel('my_main_panel')
$panel->addsSections(
    new CustomizerSection(
        'my_general_settings',
        'General Settings',
        null,
        [ CompanyInformation::get() ]
    ),
    // We could add more sections here if we wanted
);
```


#### (c) Instance for instance – indirectly via automatic hooks

```php
use Gebruederheitz\Wordpress\Customizer\CustomizerSection;

$panelId = $panel->getId();

$section = new CustomizerSection(
    'ghwp_general_settings',
    'General Settings',
    null,
    [ CompanyInformation::get() ]
);

// We can associate the section with a panel in two ways:
// Using the actual panel instance directly...
$section->setPanel($panel);
// ...or using its ID.
$section->setPanel($panelId);
```

In all these examples, we've added our settings right when constructing the 
sections. If we need some advanced logic, they can be added separately:

```php
// You can also create a section without any handlers and then add them 
// later:
$consentManagementSection = new CustomizerSection(
    'ghwp_consent_management_settings',
    'Consent Management'
);

// Associate with the panel one way...
$consentManagementSection->setPanel($panel);
// ...or another
$panel->addSections($consentManagementSection);
    
// Add hanlders retroactively
$consentManagementSection->addSettings(ConsentManagementEnabled::get());
$consentManagementSection->addSettings(
    OtherConsentManagementSetting::get(),
    ExtendedConsentManagementSetting::get(),
);

```


### Defining Settings and adding them to a section

Now you will have to define your settings. Each setting is a class implementing
`CustomizerSetting`. You can extend `BasicCustomizerSetting` for convenience:

```php
use \Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

class TelephoneNo extends BasicCustomizerSetting 
{
    // These two methods are abstract in BasicCustomizerSetting, so your IDE 
    // will conveniently create stubs for you
    public function getKey(): string
    {
        // A unique key for the option's database entry
        return 'prefix_company_info_telephone';
    }
    
    public function getLabel(): string
    {
        // The input label shown to the user
        return 'Telephone No';
    }
}
```
 

BasicCustomizerSettings are singleton objects, so instead of constructing them
you retrieve an instance with the static `get()` method:


```php
$section = new CustomizerSection(
    $slug,
    $label,
    $description,
    [
        TelephoneNo::get(),
        /* ... more settings, if you like ... */
    ]
);

$section->addSettings(TelephoneNo::get(), OtherSetting::get());
```

#### Retrieving the value

```php
# Somewhere in your code, like templates, action handlers, controllers, hook
# callbacks etc.
use My\TelephoneNo;

$phoneNumber = TelephoneNo::get()->getValue();
```



### Settings: Advanced

You can do more with settings than the basic example above. Here are some more 
detailed usages:

```php
use Gebruederheitz\Wordpress\Customizer\BasicCustomizerSetting;

/**
 * A setting with an alternative input type and explicit default value. This 
 * example would of course be simpler to implement using the 
 * CheckboxCustomizerSetting, as described below.
 */
class ShowAddress extends BasicCustomizerSetting
{
    public function getKey() {
        return 'prefix_company_info_show_address';
    }
    
    public function getLabel() {
        return 'Show address in footer';
    }
    
    // Optional: default value, defaults to ''
    protected $default = false;
    
    // Optional: input type
    protected ?string $inputType = 'checkbox';
}

/**
 * A setting with an alternative sanitizer
 */
class StreetAddress extends BasicCustomizerSetting
{
    public function getKey() {
        return 'prefix_company_info_street_address';
    }
    
    public function getLabel() {
        return 'Street address';
    }
    
    // A "callable-string" for a function that will receive the raw value and
    // return a sanitized value.
    protected ?string $sanitizer = 'sanitize_text_field';
}

/**
 * A setting using a select field
 */
class SupportIcon extends BasicCustomizerSetting
{
    /* ... key and label */
    
    protected ?string $sanitizer = 'sanitize_text_field';
    
    protected ?string $inputType = 'select';
    
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
    // ... key and label ...
    
    protected static $inputType = 'dropdown-pages';
   
    public function getActiveCallback() : ?callable
    {
        // Using an anonymous callback 
        return function () {
            $showAddress = ShowAddress::get();
            return CustomizerSettings::getValue(
                $showAddress->getKey(),
                $showAddress->getDefault()
            );
        }
        
        // Using a class method
        return [ShowAddress::get(), 'getValue'] // getValue() has a default implementation in BasicCustomizerSetting
    }
}
```

Some of these examples can lead to a lot of repetition. A URL input is always
going to have the type `url` and should ideally always have a sanitizer
`sanitize_url`. For these cases, some specialized input classes are available
at `Gebruederheitz\Wordpress\Customizer\InputTypes`:

```php
class MyCheckbox extends \Gebruederheitz\Wordpress\Customizer\InputTypes\CheckboxCustomizerSetting
{
    public function getKey(): string {
        return 'my-checkbox';
    }
    
    public function getLabel(): string {
        return 'My Checkbox';
    }
    
    /* The values below are already set on CheckboxCustomizerSetting: */
    // protected ?string $inputType = 'checkbox';
    // protected $default = false;
}

class MyUrlField extends \Gebruederheitz\Wordpress\Customizer\InputTypes\UrlCustomizerSetting
{
    public function getKey(): string {
        return 'my-url-field';
    }
    
    public function getLabel(): string {
        return 'My URL';
    }
    
    /* Already set: */
    // protected ?string $inputType = 'url';
    // protected ?string $sanitizer = 'sanitize_url';
}

class MyTextField extends \Gebruederheitz\Wordpress\Customizer\InputTypes\TextCustomizerSetting
{
    public function getKey(): string {
        return 'my-text';
    }
        
    public function getLabel(): string {
        return 'My Text';
    }
    
    /* Already set: */
    // protected ?string $sanitizer = 'sanitize_text_field';
}
```


### Customizing which panels are removed

By default, `CustomizerSettings` "cleans up" the Customizer, removing some
panels that are rarely used. You can use a filter hook to  control which of the 
default panels are removed:

```php
use Gebruederheitz\Wordpress\Customizer\CustomizerSettings;

add_filter(
    CustomizerSettings::HOOK_FILTER_DECLUTTER_ITEMS, 
    function ($items) {
        unset($items['panels']['themes']);
        unset($items['sections']['static_front_page']);
        unset($items['sections']['custom_css']);
        unset($items['controls']['custom_logo']);
        unset($items['controls']['site_icon']);
        
        return $items;
        
        // To not "declutter" at all simply
        return [];
    }
);
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
    
    protected ?string $inputType = MyCustomizeControl::class;
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
    SeparatorSetting::factory('some-unique-id-for-this-separator'),
    TelephoneNo::get(),
    // With custom margin of 3em
    SeparatorSetting::factory(
        'sep-with-custom-margin',
        null,
        [
            'margin' => 3,
        ]
    ),
    // with a heading in the default color #08d
    SeparatorSetting::factory(
        'sep-general-settings',
        __('General Settings', 'namespace')
    ),
    // with heading in a custom color
    SeparatorSetting::factory(
        'some-unique-id-for-this-separator',
        'Heading',
        [
            'color' => 'hotpink',
        ]       
    ),
    // with heading in a custom color and custom margin
    // hr bottom margin is calc(${customMargin}em + 2em) to compensate for
    // the heading's margin collapsing
    SeparatorSetting::factory(
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

- [asdf](https://asdf-vm.com/guide/getting-started.html) tool version manager
- nodeJS LTS (v18.x) via asdf
- PHP >= 7.4 (via asdf)
- [Composer 2.x](https://getcomposer.org) (via asdf)
- Nice to have: GNU Make (or drop-in alternative)
