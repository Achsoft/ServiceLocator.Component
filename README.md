Service Container
=================

Service container is an object that contains definitions of how another objects (components or services) are constructed in an application. It is an implementation of service locator pattern that enables dependency injection.

### Service Locator

> The service locator pattern is a design pattern used in software development to encapsulate the processes involved in obtaining a service with a strong abstraction layer. This pattern uses a central registry known as the "service locator", which on request returns the information necessary to perform a certain task.
> -- [wikipedia](http://en.wikipedia.org/wiki/Dependency_injection)

### Dependency Injection

> Dependency injection is a software design pattern that implements inversion of control and allows a program design to follow the dependency inversion principle.
> -- [wikipedia](http://en.wikipedia.org/wiki/Dependency_injection)


### Related Projects

* [Symfony Dependency Injection Component](https://github.com/symfony/DependencyInjection)
* [Pimple](https://github.com/fabpot/Pimple)
* [PHP-DI](https://github.com/mnapoli/PHP-DI)
* [Dice](https://github.com/TomBZombie/Dice)
* [Orno](https://github.com/orno/di)
* [Auraphp](https://github.com/auraphp/Aura.Di)


### Further Readings

* [Wikipedia.org: Dependency Injection](http://en.wikipedia.org/wiki/Dependency_injection)
* [Martinfowler.com: Inversion of Control Containers and the Dependency Injection pattern](http://www.martinfowler.com/articles/injection.html)
* [Stackoverflow.com: Dependency Injection vs Service Locator](http://stackoverflow.com/questions/1557781/whats-the-difference-between-the-dependency-injection-and-service-locator-patte)
* [Ralpschindler.com: DI, DiC, & Service Locator Redux](http://ralphschindler.com/2012/10/10/di-dic-service-locator-redux)
* [Codeproject.com: DI vs IoC](http://www.codeproject.com/Articles/592372/Dependency-Injection-DI-vs-Inversion-of-Control-IO)
* [Fabien.potencier.org: What Is Dependency Injection](http://fabien.potencier.org/article/11/what-is-dependency-injection)


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
$sc->register('mailer', new \Namespace\Mailer());

```

Lazy loading example,

```php
$sc->register('mailer', '\Namespace\Mailer');

```

or using closure,

```php
$sc->register('mailer', function () {
    return new \Namespace\Mailer();
});

```

Note that we use `$sc` parameter to pass the Service Container instance to resolve dependencies.

```php
// The dependency
$sc->register('request', function () {
    return new \Namespace\Request();
});

```

Resolving by constructor injection,

```php
$sc->register('router', function ($sc) {
    return new \Namespace\Router($sc->resolve('request'));
});

```

This is also valid,

```php
$sc->register('router', function () use ($sc) {
    return new \Namespace\Router($sc->resolve('request'));
});

```

Resolving by property injection,

```php
$sc->register('router', function ($sc) {
    $router = new \Namespace\Router();
    $router->request = $sc->resolve('request');
    return $router;
});

```

Works like above,

```php
$sc->register('router', function () use ($sc) {
    $router = new \Namespace\Router();
    $router->request = $sc->resolve('request');
    return $router;
});

```

Resolving by setter injection,

```php
$sc->register('router', function ($sc) {
    $router = new \Namespace\Router();
    $router->setRequest($sc->resolve('request'));
    return $router;
});

```

To be safe, I would suggest `function ($sc) {}` form instead of `function () use ($sc)` because the latter one has something to do with variable scoping.

To check whether a component or service had been registered, use `registered()` method.


Extending A Registered Component or Service
-------------------------------------------

To extend a registered definition, create a closure that has two parameters to accept the service container instance and the old definition.

Example,

```php
$sc->extend('mailer', function ($sc, $mailer) {
     $security = $sc->resolve('security');
     $mailer->setFrom($security->getAdminEmail());
     return $mailer;
});

```

Duplicating A Registered Component or Service
---------------------------------------------

To duplicate an existing definition, use `registerAs()` method.

```php
$sc->registerAs('new.mailer', 'mailer');

```

`$sc->resolve('new.mailer')` and `$sc->resolve('mailer')` creates the same instance.

Third argument is optional whether the definition need to be extended. It works like `extend()` method.

```php
$sc->registerAs('admin.mailer', 'mailer', function ($sl, $mailer) {
     $mailer->setSender('Admin');
     $mailer->setFrom('admin@email');
     return $mailer;
});

```


Modifying or Replacing A Definition
-----------------------------------

To set a component or service to a new definition or replace an existing one, use `modify()` method same like registering a definition.

```php
// to replace older definition
$sc->modify('mailer', function () {
    return new \Namespace\NewMailer();
});

```


Protecting A Definition
-----------------------

To protect or lock a definition from further modification, use `lock()` method. To unlock a definition, use `unlock()`.

Locked definition can only be extended as a new definition using `registerAs()` method. It cannot be modified or unregistered until being unlocked.

Use `locked()` method to check whether a component or service definition is locked.


Unregistering or Removing A Definition
--------------------------------------

To remove a definition, use `unregister()` method.

