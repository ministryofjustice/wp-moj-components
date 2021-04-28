# WP MoJ Components
A plugin to manage commonly used functionality across the MoJ portfolio of WordPress properties.

**Why?**
 1. Simple and efficient maintenance approach
 2. Enforces a baseline standard
 3. Centralised repo for common website functions
 4. Time-saving development method due to 'one fix affects all'
 5. Increased website load performance  

Components within this plugin attempt to create a baseline standard that all MoJ WP properties can benefit from. With ease of component authoring and maintenance it is the preferred method for global code inclusion.

### Current components

1. **Introduce**: *creates a Dashboard widget containing contact information so stakeholders can get support*  

2. **Introduce _(popup)_**: *provides a popup banner to relay administrative information to stakeholders* 

3. **Multisite**: *adds support for multisite based environments* 

4. **Multisite _(domain tracker)_**: *a non destructive custom domain tracker, provided primarily for the WASM tool.* 

5. **Security**: *applies scanning functionality and various security patches*
 
6. **Security _(rest-api)_**: *prevents access to specified REST API routes*

7. **Sitemap**: *provides auto-generated sitemap functionality and shortcodes*

8. **Users**: *introduces user-switch capability for admins plus monitors inactive users for GDPR*

9. **Versions**: *grants access to WordPress version over API and provides information relating to installed plugin and theme versions*

------

### How to install

Require the plugin in your composer.json file and always use the latest version:

`"ministryofjustice/wp-moj-components": "*"`

*Load the plugin in the mu-plugins directory when you run `composer update `.*

### How it works
Inside the directory named 'component' are sub-directories that represent individual functionality. These are called '**components**'.

Each component is stand-alone and contains its own assets and code. Components load automatically using the `composer` PSR4 autoloader.

Other conventions include:

- Asset directory can contain: 
  - `css/`
  - `fonts/`
  - `images/`
  - `js/`
- Components can store sub-classes in a directory called `sub/` for auto-loading

You have access to a `Helper` class within the component directory that provides solutions to paths and offers an easy way to keep inter-common code and functions organised.

You can access this class and it's methods from any component with the following function call:

`Helper()->method()`  

## Adding a component
### Prerequisite 
Please bear in mind that adding a component will affect every site that uses this plugin.

To ensure best fit and before you begin; can you agree with the following statements?

1. The component I am writing should be used by all websites
2. The component will benefit all our users and stakeholders
3. The component helps solve a general business requirement


### Setup
Create your component using the following structure.

*Feature.php is the main class in the component namespace that handles all the work*
- `component/Feature`
- `component/Feature/Feature.php`


- *an asset directory for use by this component only*
- `component/Feature/assets`
- `component/Feature/assets/css`
- `component/Feature/assets/fonts`
- `component/Feature/assets/images`
- `component/Feature/assets/js`


- *an autoload sub-directory for assistive functionality*
- `component/Feature/sub`
- `component/Feature/sub/Class.php`


### Example: basic component class
```php
<?php

namespace MOJComponents\Feature;

/**
 * A great description about what this feature does
 */
class FeatureClass
{
    public  function __construct()
    {
        $this->actions();
    }

    private function actions()
    {
        // hooks in here
    }

    public function method()
    {
        // do some work
    }

    public function another_method()
    {
        // do some other work
    }
}

