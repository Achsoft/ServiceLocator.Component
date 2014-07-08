## 0.2.0 (2014.07.08) - BC Breaks

* `Container::registered()` is removed
* Implements *ContainerInterface* from *container-interop/container-interop*
  package
* Rename `Container::modify()` to `Container::set()`
* Rename `Container::register()` to `Container::add()`
* Rename `Container::registerAs()` to `Container::copy()`
* 

## 0.1.4 (2014.07.07)

* Fixed to throw an exception if extending a closure definition which returns
  null value

## 0.1.3 (2014.07.07)

* closes #6 Wrap the object with a closure so it does not produce error

## 0.1.2 (2014.07.06)

* fix #5 Object has to be cloned to remove its reference

## 0.1.1 (2014.06.26)

* Add `Container::has` method equivalent to `Container::registered`
* Rebase git repo.

## 0.1.0 (2014.06.23)

* Initial development
