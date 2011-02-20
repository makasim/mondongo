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

namespace Mondongo\Mondator;

/**
 * ClassExtension is the base class for class extensions.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class ClassExtension
{
    protected $options         = array();
    protected $requiredOptions = array();

    protected $definitions;

    protected $class;
    protected $configClass;

    protected $newClassExtensions;
    protected $newConfigClasses;

    /**
     * Constructor.
     *
     * @param array $options An array of options.
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        $this->setUp();

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        // required options
        if ($diff = array_diff($this->requiredOptions, array_keys($options))) {
            throw new \RuntimeException(sprintf('%s requires the options: "%s".', get_class($this), implode(', ', $diff)));
        }
    }

    /**
     * Set up the extension.
     *
     * @return void
     */
    protected function setUp()
    {
    }

    /**
     * Add an option.
     *
     * @param string $name         The option name.
     * @param mixed  $defaultValue The default value (optional, null by default).
     *
     * @return void
     */
    protected function addOption($name, $defaultValue = null)
    {
        $this->options[$name] = $defaultValue;
    }

    /**
     * Add options.
     *
     * @param array $options An array with options (name as key and default value as value).
     *
     * @return void
     */
    protected function addOptions(array $options)
    {
        foreach ($options as $name => $defaultValue) {
            $this->addOption($name, $defaultValue);
        }
    }

    /**
     * Add a required option.
     *
     * @param string $name The option name.
     *
     * @return void
     */
    protected function addRequiredOption($name)
    {
        $this->addOption($name);

        $this->requiredOptions[] = $name;
    }

    /**
     * Add required options.
     *
     * @param array $options An array with the name of the required option as value.
     *
     * @return void
     */
    protected function addRequiredOptions(array $options)
    {
        foreach ($options as $name) {
            $this->addRequiredOption($name);
        }
    }

    /**
     * Returns if exists an option.
     *
     * @param string $name The name.
     *
     * @return bool Returns true if the option exists, false otherwise.
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * Set an option.
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the option does not exists.
     */
    public function setOption($name, $value)
    {
        if (!$this->hasOption($name)) {
            throw new \InvalidArgumentException(sprintf('The option "%s" does not exists.', $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * Returns the options.
     *
     * @return array The options.
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Return an option.
     *
     * @param string $name The name.
     *
     * @return mixed The value of the option.
     *
     * @throws \InvalidArgumentException If the options does not exists.
     */
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            throw new \InvalidArgumentException(sprintf('The option "%s" does not exists.', $name));
        }

        return $this->options[$name];
    }

    /**
     * New class extensions process.
     *
     * @param string       $class              The class.
     * @param \ArrayObject $configClass        The config class.
     * @param \ArrayObject $newClassExtensions The new class extensions.
     */
    public function newClassExtensionsProcess($class, \ArrayObject $configClass, \ArrayObject $newClassExtensions)
    {
        $this->newClassExtensions = $newClassExtensions;

        $this->class       = $class;
        $this->configClass = $configClass;

        $this->doNewClassExtensionsProcess();

        $this->newClassExtensions = null;

        $this->class       = null;
        $this->configClass = null;
    }

    /**
     * Do the new class extensions process.
     *
     * Here you can add new class extensions.
     */
    protected function doNewClassExtensionsProcess()
    {
    }

    /**
     * New config classes process.
     *
     * @param string       $class            The class.
     * @param \ArrayObject $configClass      The config class.
     * @param \ArrayObject $newConfigClasses The new config classes.
     */
    public function newConfigClassesProcess($class, \ArrayObject $configClass, \ArrayObject $newConfigClasses)
    {
        $this->newConfigClasses = $newConfigClasses;

        $this->class       = $class;
        $this->configClass = $configClass;

        $this->doNewConfigClassesProcess();

        $this->newConfigClasses = null;

        $this->class       = null;
        $this->configClass = null;
    }

    /**
     * Do the new config classes process.
     *
     * Here you can add new config classes, and change the config classes
     * if it is necessary to build the new config classes.
     */
    protected function doNewConfigClassesProcess()
    {
    }

    /**
     * Process the config class.
     *
     * @param string       $class       The class.
     * @param \ArrayObject $configClass The config class.
     */
    public function configClassProcess($class, \ArrayObject $configClass)
    {
        $this->class       = $class;
        $this->configClass = $configClass;

        $this->doConfigClassProcess();

        $this->class       = null;
        $this->configClass = null;
    }

    /**
     * Do the config class process.
     *
     * Here you can modify the config class.
     */
    protected function doConfigClassProcess()
    {
    }


    /**
     * Process the class.
     *
     * @param string                      $class       The class.
     * @param \ArrayObject                $configClass The config class.
     * @param Mondongo\Mondator\Container $container   The container.
     *
     * @return void
     */
    public function classProcess($class, \ArrayObject $configClass, Container $container)
    {
        $this->class       = $class;
        $this->configClass = $configClass;
        $this->definitions = $container;

        $this->doClassProcess();

        $this->class       = null;
        $this->configClass = null;
        $this->definitions = null;
    }

    /**
     * Do the class process.
     */
    protected function doClassProcess()
    {
    }

    /*
     * Tools.
     */
    protected function createClassExtensionFromArray(array $data)
    {
        if (!isset($data['class'])) {
            throw new \InvalidArgumentException(sprintf('The extension does not have class.'));
        }

        return new $data['class'](isset($data['options']) ? $data['options'] : array());
    }

    protected function getNamespace($class)
    {
        if (false !== $pos = strrpos($class, '\\')) {
            if ('\\' == $class[0]) {
                $class = substr($class, 1);
                $pos   = $pos - 1;
            }

            return substr($class, 0, $pos);
        }

        return null;
    }

    protected function getClassName($class)
    {
        if (false !== $pos = strrpos($class, '\\')) {
            return substr($class, $pos + 1);
        }

        return $class;
    }
}
