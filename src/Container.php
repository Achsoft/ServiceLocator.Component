<?php

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file.
 * Redistributions of files must retain the above copyright notice.
 * 
 * @copyright (c) 2014, Achmad F. Ibrahim
 * @link https://github.com/Achsoft
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

namespace Achsoft\Component\ServiceLocator;

use Achsoft\Component\ServiceLocator\Exception\InvalidDefinitionException;
use Achsoft\Component\ServiceLocator\Exception\InvalidIdentifierException;
use Achsoft\Component\ServiceLocator\Exception\NotFoundException;
use Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException;

/**
 * Service Locator class.
 * 
 * @author Achmad F. Ibrahim <acfatah@gmail.com>
 * @package Achsoft\Component\ServiceLocator
 * @version 0.1.2
 * @since 0.1.0
 */
class Container
{
    /**
     * Variable to store names of locked component or service.
     * 
     * @var array
     * @since 0.1.0
     */
    private $locked = [];
    
    /**
     * Variable to store registered components or services.
     * 
     * @var array
     * @since 0.1.0
     */
    private $registry = [];
    
    /**
     * Constructor.
     * 
     * Construct configurations from an array.
     * 
     * @param array $config An array of configurations
     * @since 0.1.0
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                $this->modify($key, $value);
            }
        }
    }
    
    /**
     * Extend a current registered definition.
     * 
     * To extend a definition, define a closure with two parameters to pass the
     * service locator and the definition instances.
     * 
     * Only closure type definition can be extended. Example,
     * 
     * ```php
     * $sl->extend('mailer', function ($sl, $mailer) {
     *     $security = $sl->resolve('security');
     *     $mailer->setFrom($security->getAdminEmail());
     *     return $mailer;
     * })
     *
     * ```
     * 
     * @param string $id Component or service identifier
     * @param \Closure $newDefinition New component or service definition
     * @throws \Achsoft\Component\ServiceLocator\Exception\NotFoundException
     *     if the identifier is not registered
     * @throws \Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException 
     *     if the identifier is locked
     * @throws \Achsoft\Component\ServiceLocator\Exception\InvalidDefinitionException
     *     if the closure does not have exactly two parameters
     * @since 0.1.0
     */
    public function extend($id, \Closure $newDefinition)
    {
        // throw exception if not registered
        if (!isset($this->registry[$id])) {
            $message = 'Identifier %s is not registered.';
            throw new NotFoundException(sprintf($message, $id));
        }
        
        // throw exception if locked
        if ($this->locked($id)) {
            $message = 'Identifier %s is locked.';
            throw new ProtectedDefinitionException(sprintf($message, $id));
        }
        
        $reflectionMethod = new \ReflectionMethod($newDefinition, '__invoke');
        $parameterCount = $reflectionMethod->getNumberOfParameters();
        
        if (!$parameterCount == 2) {
            $message = 'To modify a definition, anonymous function or closure'
                . ' is required to have exactly two parameters to accept'
                . ' service locator instance and the old component or service'
                . ' instance.';
            throw new InvalidDefinitionException($message);
        }
        
        // convert string class name to closure
        if (is_string($this->registry[$id]) && class_exists($this->registry[$id])) {
            $class = '\\' . ltrim($this->registry[$id], '\\');
            $this->registry[$id] = function ($sl) use ($class) {
                return new $class;
            };
        }
        
        // wrap object as a closure
        if (is_object($this->registry[$id]) && !$this->registry[$id] instanceOf \Closure ) {
            $object = $this->registry[$id];
            $this->registry[$id] = function ($sl) use ($object) {
                return $object;
            };
        }
        
        $oldDefinition = $this->registry[$id];
        $extended = function ($sl) use ($oldDefinition, $newDefinition) {
            return $newDefinition($sl, $oldDefinition($sl));
        };
        
        $this->modify($id, $extended);
    }
    
    /**
     * Check whether a component or service had been registered.
     * 
     * @param string $id Component or service identifier
     * @return boolean Whether the identifier is registered
     * @since 0.1.1
     */
    public function has($id)
    {
        return array_key_exists($id, $this->registry);
    }
    
    /**
     * Lock or protect a component or service definition from being modified.
     * 
     * @param string $id Component or service identifier
     * @since 0.1.0
     */
    public function lock($id)
    {
        if (!isset($this->registry[$id])) {
            $message = 'Identifier %s is not registered.';
            throw new NotFoundException(sprintf($message, $id));
        }
        
        $this->locked[] = $id;
    }
    
    /**
     * Check whether a component or service definition is locked.
     * 
     * @param string $id Component or service identifier
     * @return boolean whether the component or service is locked
     * @since 0.1
     */
    public function locked($id)
    {
        return array_search($id, $this->locked) !== false;
    }

    /**
     * Set a component or service identifier to a new definition or replace an
     * existing one.
     * 
     * @param string $id Component or service identifier
     * @param mixed $value Component or service definition
     * @throws \Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException 
     *     if the identifier is locked
     * @since 0.1.0
     */
    public function modify($id, $value)
    {
        if ($this->locked($id)) {
            $message = 'Identifier %s is locked.';
            throw new ProtectedDefinitionException(sprintf($message, $id));
        }
        
        $this->registry[$id] = $value;
    }
    
    /**
     * Register a component or service definition.
     * 
     * Component or service definition can be either:
     * * A mixed type of variable
     * * An object or a string class name
     * * A closure or an anonymous function
     * 
     * An object example,
     * 
     * ```php
     * // eager loading
     * $sc->register('identifier', new \Namespace\Mailer());
     *
     * ```
     * 
     * A class name example,
     * 
     * ```php
     * // lazy loading
     * $sc->register('identifier', '\Namespace\Mailer');
     * 
     * ```
     * 
     * Example of definition without dependency,
     * 
     * ```php
     * $sc->register('identifier', function() {
     *     return new \Namespace\Mailer();
     * });
     * 
     * ```
     * 
     * Example of definition with dependencies,
     * 
     * ```php
     * $sc->register('request', function(){
     *     return new \Namespace\Request();
     * });
     * 
     * ...
     * 
     * $sc->register('router', function ($sl) {
     *     return new \Namespace\Router($sc->resolve('request'));
     * });
     * 
     * ```
     * 
     * @param string $id Component or service indentifier
     * @param mixed $definition Component or service definition
     * @throws \Achsoft\Component\ServiceLocator\Exception\InvalidIdentifierException
     *     if the identifier is already registered
     * @throws \Achsoft\Component\ServiceLocator\Exception\InvalidDefinitionException
     *     if given closure have more than one parameter
     * @since 0.1.0
     */
    public function register($id, $definition)
    {
        if ($this->has($id)) {
            $message = 'Identifier %s is already registered.';
            throw new InvalidIdentifierException(sprintf($message, $id));
        }
        
        if ($definition instanceof \Closure) {
            $reflectionMethod = new \ReflectionMethod($definition, '__invoke');
            $parameterCount = $reflectionMethod->getNumberOfParameters();
        
            if ($parameterCount > 1) {
                $message = 'Anonymous function or closure type definition are'
                    . ' required to have zero or one parameter to accept'
                    . ' service locator instance.';
                throw new InvalidDefinitionException($message);
            }
            
            if ($parameterCount == 0) {
                $newDefinition = function ($sl) use ($definition) {
                    return $definition();
                };
                $definition = $newDefinition;
            }
        }
        
        $this->modify($id, $definition);
    }
    
    /**
     * Register or duplicate existing definition as a new one.
     * 
     * ```php
     * $sc->registerAs('admin.mailer', 'mailer', function ($sl, $mailer) {
     *     $mailer->setSender('Admin');
     *     $mailer->setFrom('admin@email');
     *     return $mailer;
     * });
     * 
     * ```
     * @param string $newId New component or service identifer
     * @param string $id Component or service identifier
     * @param \Closure $newDefinition Extend the definition
     * @throws \Achsoft\Component\ServiceLocator\Exception\NotFoundException
     *     if the identifier is not registered
     * @since 0.1.2
     */
    public function registerAs($newId, $id, \Closure $newDefinition = null)
    {
        if (!$this->has($id)) {
            $message = 'Identifier %s is not registered.';
            throw new NotFoundException(sprintf($message, $id));
        }
        
        // object has to be cloned to remove its reference
        is_object($this->registry[$id])
            ? $this->register($newId, clone $this->registry[$id])
            : $this->register($newId, $this->registry[$id]);
        
        if (isset($newDefinition)) {
            $this->extend($newId, $newDefinition);
        }
    }
    
    /**
     * Check whether a component or service had been registered.
     * 
     * @param string $id Component or service identifier
     * @return boolean Whether the identifier is registered
     * @since 0.1.0
     * @deprecated Use \Achsoft\Component\ServiceLocator\Container::has() instead.
     *     Will be removed.
     */
    public function registered($id)
    {
        return $this->has($id);
    }
    
    /**
     * Resolve a component or service definition.
     * 
     * @param string $name Component or service identifier
     * @return mixed Resolved component or service instance
     * @throws \Achsoft\Component\ServiceLocator\Exception\NotFoundException
     *     if the identifier is not registered if the identifier is not registered
     * @since 0.1.0
     */
    public function resolve($id)
    {
        if (!isset($this->registry[$id])) {
            $message = 'Identifier "%s" is not registered.';
            throw new NotFoundException(sprintf($message, $id));
        }
        
        if ($this->registry[$id] instanceof \Closure) {
            return $this->registry[$id]($this);
        }
        
        if (is_string($this->registry[$id]) && class_exists($this->registry[$id])) {
            $class = '\\' . ltrim($this->registry[$id], '\\');
            return new $class;
        }
        
        return $this->registry[$id];
    }
    
    /**
     * Unlock a locked component or service definition.
     * 
     * @param string $name Component or service identifier
     * @throws \Achsoft\Component\ServiceLocator\Exception\NotFoundException
     *     if the identifier is not registered
     * @since 0.1.0
     */
    public function unlock($id)
    {
        if (!isset($this->registry[$id])) {
            $message = 'Identifier "%s" is not registered.';
            throw new NotFoundException(sprintf($message, $id));
        }
        
        $key = array_search($id, $this->locked);
        if ($key !== false) {
            unset($this->locked[$key]);
        }
    }
    
    /**
     * Unregister a component or service definition.
     * 
     * @param string $name Component or service identifier
     * @throws \Achsoft\Component\ServiceLocator\Exception\ProtectedDefinitionException
     *     if the identifier is locked
     * @since 0.1.0
     */
    public function unregister($id)
    {
        if ($this->locked($id)) {
            $message = 'Identifier %s is locked.';
            throw new ProtectedDefinitionException(sprintf($message, $id));
        }
        
        unset($this->registry[$id]);
    }
}
