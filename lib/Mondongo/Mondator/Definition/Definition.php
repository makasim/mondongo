<?php

/*
 * Copyright 2010 Pablo Díez Pascual <pablodip@gmail.com>
 *
 * This file is part of Mondongo.
 *
 * Mondongo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Mondongo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Mondongo. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mondongo\Mondator\Definition;

/**
 * Represents a definition of a class.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Definition
{
    protected $namespace;

    protected $className;

    protected $parentClass;

    protected $interfaces = array();

    protected $isAbstract = false;

    protected $properties = array();

    protected $methods = array();

    protected $PHPDoc;

    /**
     * Constructor.
     *
     * @param string $className The class name.
     *
     * @return void
     */
    public function __construct($className)
    {
        $this->setClassName($className);
    }

    /**
     * Set the namespace.
     *
     * @param string $namespace The namespace.
     *
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Returns the namespace.
     *
     * @return string The namespace.
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the class name.
     *
     * @param string $className The class name.
     *
     * @return void
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * Returns the class name.
     *
     * @return string The class name.
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns the class name with namespace.
     *
     * @return string The class name with namespace.
     */
    public function getFullClass()
    {
        return ($this->namespace ? $this->namespace.'\\' : '').$this->className;
    }

    /**
     * Set the parent class.
     *
     * @param string $parentClass The parent class.
     *
     * @return void
     */
    public function setParentClass($parentClass)
    {
        $this->parentClass = $parentClass;
    }

    /**
     * Returns the parent class.
     *
     * @return string The parent class.
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * Add an interface.
     *
     * @param string $interface The interface.
     *
     * @return void
     */
    public function addInterface($interface)
    {
        $this->interfaces[] = $interface;
    }

    /**
     * Set the interfaces.
     *
     * @param array $interfaces The interfaces.
     *
     * @return void
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = array();
        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }
    }

    /**
     * Returns the interfaces.
     *
     * @return array The interfaces.
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * Set if it is abstract.
     *
     * @param bool $isAbstract If the class is abstract.
     *
     * @return void
     */
    public function setIsAbstract($isAbstract)
    {
        $this->isAbstract = (bool) $isAbstract;
    }

    /**
     * Returns if the class is abstract.
     *
     * @return bool If the class is abstract.
     */
    public function getIsAbstract()
    {
        return $this->isAbstract;
    }

    /**
     * Add a property.
     *
     * @param Mondongo\Mondator\Definition\Property $property The property.
     *
     * @return void
     */
    public function addProperty(Property $property)
    {
        $this->properties[] = $property;
    }

    /**
     * Set the properties.
     *
     * @param array $properties An array of properties.
     *
     * @return void
     */
    public function setProperties(array $properties)
    {
        $this->properties = array();
        foreach ($properties as $name => $property) {
            $this->addProperty($property);
        }
    }

    /**
     * Returns the properties.
     *
     * @return array The properties.
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Add a method.
     *
     * @param Mondongo\Mondator\Definition\Method $method The method.
     *
     * @return void
     */
    public function addMethod(Method $method)
    {
        $this->methods[] = $method;
    }

    /**
     * Set the methods.
     *
     * @param array $methods An array of methods.
     *
     * @return void
     */
    public function setMethods(array $methods)
    {
        $this->methods = array();
        foreach ($methods as $name => $method) {
            $this->addMethod($method);
        }
    }

    /**
     * Returns the methods.
     *
     * @return array The methods.
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Set the PHPDoc.
     *
     * @param string|null $PHPDoc The PHPDoc.
     *
     * @return void
     */
    public function setPHPDoc($PHPDoc)
    {
        $this->PHPDoc = $PHPDoc;
    }

    /**
     * Returns the PHPDoc.
     *
     * @return string|null The PHPDoc.
     */
    public function getPHPDoc()
    {
        return $this->PHPDoc;
    }
}
