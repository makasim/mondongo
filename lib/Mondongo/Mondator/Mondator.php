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

use Mondongo\Mondator\Definition\Container;

/**
 * Mondator.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Mondator
{
    protected $classDefinitions = array();

    protected $extensions = array();

    protected $outputs = array();

    /**
     * Set a class definition.
     *
     * @param string $className The class name.
     * @param array  $classData The class data.
     *
     * @return void
     */
    public function setClassDefinition($className, array $classData)
    {
        $this->classDefinitions[$className] = $classData;
    }

    /**
     * Set the class definitions.
     *
     * @param array $classDefinitions An array of class definitions (class name as key and class data as value).
     *
     * @return void
     */
    public function setClassDefinitions(array $classDefinitions)
    {
        $this->classDefinitions = array();
        foreach ($classDefinitions as $className => $classData) {
            $this->setClassDefinition($className, $classData);
        }
    }

    /**
     * Returns if a class definition exists.
     *
     * @param string $className The class name.
     *
     * @return bool Returns if the class definition exists.
     */
    public function hasClassDefinition($className)
    {
        return array_key_exists($className, $this->classDefinitions);
    }

    /**
     * Returns the class definitions.
     *
     * @return array The class definitions.
     */
    public function getClassDefinitions()
    {
        return $this->classDefinitions;
    }

    /**
     * Returns a class definition.
     *
     * @param string $className The class name.
     *
     * @return array The class definition.
     *
     * @throws \InvalidArgumentException If the class definition does not exists.
     */
    public function getClassDefinition($className)
    {
        if (!$this->hasClassDefinition($className)) {
            throw new \InvalidArgumentException(sprintf('The class definition "%s" does not exists.', $className));
        }

        return $this->classDefinitions[$className];
    }

    /**
     * Add a extension.
     *
     * @param Mondongo\Mondator\Extension $extension The extension.
     *
     * @return void
     */
    public function addExtension(Extension $extension)
    {
        $this->extensions[] = $extension;
    }

    /**
     * Set the extensions.
     *
     * @param array $extensions An array of extensions.
     *
     * @return void
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = array();
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }
    }

    /**
     * Returns the extensions.
     *
     * @return array The extensions.
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Set a output.
     *
     * @param string                   $name   The name.
     * @param Mondongo\Mondator\Output $output The output.
     *
     * @return void.
     */
    public function setOutput($name, Output $output)
    {
        $this->outputs[$name] = $output;
    }

    /**
     * Set the outputs.
     *
     * @param array $outputs The outputs.
     *
     * @return void
     */
    public function setOutputs(array $outputs)
    {
        $this->outputs = array();
        foreach ($outputs as $name => $output) {
            $this->setOutput($name, $output);
        }
    }

    /**
     * Returns if exists a output by name.
     *
     * @param string $name The output name.
     *
     * @return bool Returns if exists the output.
     */
    public function hasOutput($name)
    {
        return isset($this->outputs[$name]);
    }

    /**
     * Returns the outputs.
     *
     * @return array The outputs.
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    /**
     * Return a output by name.
     *
     * @return Mondongo\Mondator\Output The output.
     *
     * @throws \InvalidArgumentException If the output does not exists.
     */
    public function getOutput($name)
    {
        if (!$this->hasOutput($name)) {
            throw new \InvalidArgumentException(sprintf('The output "%s" does not exists.', $name));
        }

        return $this->outputs[$name];
    }

    /**
     * Generate the definition containers of classes.
     *
     * @return array The array of containers.
     */
    public function generateContainers()
    {
        $containers = array();

        // classes
        $classes = array();
        foreach ($this->getClassDefinitions() as $className => $classData) {
            $classes[$className] = new \ArrayObject($classData);
        }

        // extensions
        foreach ($classes as $className => $classData) {
            $containers[$className] = $container = new Container();

            foreach ($this->getExtensions() as $extension) {
                $extension->process($container, $className, $classData);
            }
        }

        return $containers;
    }

    /**
     * Dump containers.
     *
     * @param array $containers An array of containers.
     *
     * @return void
     */
    public function dumpContainers(array $containers)
    {
        // directories
        foreach ($containers as $container) {
            foreach ($container->getDefinitions() as $name => $definition) {
                $output = $this->getOutput($name);
                $dir    = $output->getDirectory();

                if (!file_exists($dir) && false === @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf('Unable to create the %s directory (%s).', $name, $dir));
                }

                if (!is_writable($dir)) {
                    throw new \RuntimeException(sprintf('Unable to write in the %s directory (%s).', $name, $dir));
                }
            }
        }

        // output
        foreach ($containers as $container) {
            foreach ($container->getDefinitions() as $name => $definition) {
                $output = $this->getOutput($name);
                $dir    = $output->getDirectory();

                $file = $dir.DIRECTORY_SEPARATOR.$definition->getClassName().'.php';

                if (!file_exists($file) || $output->getOverride()) {
                    $dumper  = new Dumper($definition);
                    $content = $dumper->dump();

                    if (false === @file_put_contents($file, $content)) {
                        throw new \RuntimeException(sprintf('Failed to write the file "%s".', $file));
                    }
                    chmod($file, 0644);
                }
            }
        }
    }

    /**
     * Generate and dump the containers.
     *
     * @return void
     */
    public function process()
    {
        $this->dumpContainers($this->generateContainers());
    }
}
