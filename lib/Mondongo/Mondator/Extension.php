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
 * Extension is the base class for extensions.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
abstract class Extension
{
    protected $options = array();

    protected $container;
    protected $className;
    protected $configClass;

    protected $definitions;
    protected $outputs;

    /**
     * Constructor.
     *
     * @param array $options An array of options.
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
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
     * Process the extension.
     *
     * @param Mondongo\Mondator\Container $container   The container.
     * @param string                      $className   The class name.
     * @param \ArrayObject                $configClass The class data.
     *
     * @return void
     */
    public function process(Container $container, $className, \ArrayObject $configClass)
    {
        $this->container   = $container;
        $this->className   = $className;
        $this->configClass = $configClass;

        $this->definitions = $container->getDefinitions();
        $this->outputs     = $container->getOutputs();

        $this->doProcess();

        $this->container   = null;
        $this->className   = null;
        $this->configClass = null;

        $this->definitions = null;
        $this->outputs     = null;
    }

    abstract protected function doProcess();

    /*
     * Tools.
     */
    protected function processExtensionsFromArray(array $extensions)
    {
        foreach ($extensions as $key => $data) {
            if (!isset($data['class'])) {
                throw new \InvalidArgumentException(sprintf('The extension "%s" does not have class.'));
            }
            $extension = new $data['class'](isset($data['options']) ? $data['options'] : array());
            $extension->process($this->container, $this->className, $this->configClass);
        }
    }

    protected function getMethodCode(\ReflectionMethod $method, array $replace = array())
    {
        $lines = file($method->getFileName());

        $code = '';
        for ($i = $method->getStartLine(); $i <= $method->getEndLine(); $i++) {
            $code .= $lines[$i - 1];
        }

        $code = substr($code, strpos($code, '{') + 1);
        $code = substr($code, 0, strrpos($code, '}'));
        $code = '        '.trim($code);

        return strtr($code, $replace);
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
