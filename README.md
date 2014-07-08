Service Container
=================

Service container is an object that contains definitions of how another objects (components or services) are constructed in an application. It is an implementation of service locator pattern that enables dependency injection.

Requirements
------------

* php >= 5.4.0

Installation
------------

Via composer,

```json
{
    "require": {
        "achsoft/service-locator"
    }
}
```

Usage
-----

```php
use Achsoft\Component\ServiceLocator\Container;

$configs = [...];    // an array of configurations
$sc = new Container($configs);

```


Configurations
--------------

Configuration is an array of indentifier as key and definition as value pairs. The definition can be any type of service parameter, an object or a closure. Always use fully qualified class name in the service definition.


Registering a Component or Service
----------------------------------

To register a component or service, provide a string identifier and a definition. Registering an already registered identifier will throws an exception.

Eager loading example,

```php
$sc->add('mailer', new \Namespace\Mailer());

```

Lazy loading example,

```php
$sc->add('mailer', '\Namespace\Mailer');

```

or using closure,

```php
$sc->add('mailer', function () {
    return new \Namespace\Mailer();
});

```

Note that we use `$sc` parameter to pass the Service Container instance to resolve dependencies.

```php
// The dependency
$sc->add('request', function () {
    return new \Namespace\Request();
});

```

Resolving by constructor injection,

```php
$sc->add('router', function ($sc) {
    return new \Namespace\Router($sc->get('request'));
});

```

This is also valid,

```php
$sc->add('router', function () use ($sc) {
    return new \Namespace\Router($sc->get('request'));
});

```

Resolving by property injection,

```php
$sc->add('router', function ($sc) {
    $router = new \Namespace\Router();
    $router->request = $sc->get('request');
    return $router;
});

```

Works like above,

```php
$sc->add('router', function () use ($sc) {
    $router = new \Namespace\Router();
    $router->request = $sc->get('request');
    return $router;
});

```

Resolving by setter injection,

```php
$sc->add('router', function ($sc) {
    $router = new \Namespace\Router();
    $router->setRequest($sc->get('request'));
    return $router;
});

```

To be safe, I would suggest `function ($sc) {}` form instead of `function () use ($sc)` because the latter one has something to do with variable scoping.

To check whether a component or service had been registered, use `has()` method.


Extending A Registered Component or Service
-------------------------------------------

To extend a registered definition, create a closure that has two parameters to accept the service container instance and the old definition.

Example,

```php
$sc->extend('mailer', function ($sc, $mailer) {
     $security = $sc->get('security');
     $mailer->setFrom($security->getAdminEmail());
     return $mailer;
});

```

Duplicating A Registered Component or Service
---------------------------------------------

To duplicate an existing definition, use `copy()` method.

```php
$sc->copy('mailer', 'new.mailer');

```

`$sc->get('new.mailer')` and `$sc->get('mailer')` creates the same object.

Third argument is optional whether the definition need to be extended. It works like `extend()` method.

```php
$sc->copy('mailer', 'admin.mailer', function ($sl, $mailer) {
     $mailer->setSender('Admin');
     $mailer->setFrom('admin@email');
     return $mailer;
});

```


Modifying or Replacing A Definition
-----------------------------------

To set a component or service to a new definition or replace an existing one, use `set()` method same like registering a definition.

```php
// to replace older definition
$sc->set('mailer', function () {
    return new \Namespace\NewMailer();
});

```


Protecting A Definition
-----------------------

To protect or lock a definition from further modification, use `lock()` method. To unlock a definition, use `unlock()`.

Locked definition can only be extended as a new definition using `copy()` method. It cannot be modified or unregistered until being unlocked.

Use `locked()` method to check whether a component or service definition is locked.


Unregistering or Removing A Definition
--------------------------------------

To remove a definition, use `remove()` method.

