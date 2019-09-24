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

1. **Versions**:
*grants access to WordPress version over API and is intended to provide installed plugin and theme versions in the near future*

2. **Introduce**:
*creates a Dashboard widget with contact information for the JotW team* 

------

### How to install

Require the plugin in your composer.json file and always use the latest version:

`"ministryofjustice/wp-moj-components": "*"`

*Should load the plugin in the mu-plugins directory when you run `composer update `.*

### How it works
Inside the directory named 'components' are sub-directories that represent individual functionality. These are called '**components**'.

Each component is stand-alone and contains it's own assets and code. Components are loaded automatically by a system loader if they the component directory name matches the class.php within.

Other conventions include:

- Asset directory can contain: 
  - `css/`
  - `fonts/`
  - `images/`
  - `js/`
- Components can store sub-classes in a directory called `sub/` for auto-loading

You have access to a `Helper` class within the components directory that provides solutions to paths and offers an easy way to keep inter-common code and functions organised.

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

namespace component;

/**
 * A great description about what this feature does
 */
class Feature
{
    public  function __construct()
    {
        $this->add_actions();
    }

    private function add_actions()
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

