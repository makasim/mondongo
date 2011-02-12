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
 * Mondator.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Mondator
{
    protected $configClasses = array();

    protected $extensions = array();

    protected $outputs = array();

    /**
     * Set a config class.
     *
     * @param string $class       The class.
     * @param array  $configClass The config class.
     *
     * @return void
     */
    public function setConfigClass($class, array $configClass)
    {
        $this->configClasses[$class] = $configClass;
    }

    /**
     * Set the config classes.
     *
     * @param array $configClasses An array of config classes (class as key and config class as value).
     *
     * @return void
     */
    public function setConfigClasses(array $configClasses)
    {
        $this->configClasses = array();
        foreach ($configClasses as $class => $configClass) {
            $this->setConfigClass($class, $configClass);
        }
    }

    /**
     * Returns if a config class exists.
     *
     * @param string $class The class.
     *
     * @return bool Returns if the config class exists.
     */
    public function hasConfigClass($class)
    {
        return array_key_exists($class, $this->configClasses);
    }

    /**
     * Returns the config classes.
     *
     * @return array The config classes.
     */
    public function getConfigClasses()
    {
        return $this->configClasses;
    }

    /**
     * Returns a config class.
     *
     * @param string $class The class.
     *
     * @return array The config class.
     *
     * @throws \InvalidArgumentException If the config class does not exists.
     */
    public function getConfigClass($class)
    {
        if (!$this->hasConfigClass($class)) {
            throw new \InvalidArgumentException(sprintf('The config class "%s" does not exists.', $class));
        }

        return $this->configClasses[$class];
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
     * Generate the containers.
     *
     * @return array The containers.
     */
    public function generateContainers()
    {
        $containers = array();
        $containers['_global'] = new Container();

        // global extensions
        $globalExtensions = $this->getExtensions();

        // configClasses
        $configClasses = new \ArrayObject();
        foreach ($this->getConfigClasses() as $class => $configClass) {
            $configClasses[$class] = new \ArrayObject($configClass);
        }

        // classes extensions
        $classesExtensions = new \ArrayObject();
        $this->generateContainersClassesExtensions($globalExtensions, $classesExtensions, $configClasses);

        // config class process
        foreach ($classesExtensions as $class => $classExtensions) {
            foreach ($classExtensions as $classExtension) {
                $classExtension->configClassProcess($class, $configClasses[$class]);
            }
        }

        // pre global process
        foreach ($globalExtensions as $globalExtension) {
            $globalExtension->preGlobalProcess($configClasses, $containers['_global']);
        }

        // class process
        foreach ($configClasses as $class => $configClass) {
            $containers[$class] = $container = new Container();
            foreach ($classesExtensions[$class] as $extension) {
                $extension->classProcess($class, $configClass, $container);
            }
        }

        // post global process
        foreach ($globalExtensions as $globalExtension) {
            $globalExtension->postGlobalProcess($configClasses, $containers['_global']);
        }

        return $containers;
    }

    protected function generateContainersClassesExtensions(array $extensions, \ArrayObject $classesExtensions, \ArrayObject $configClasses)
    {
        foreach ($configClasses as $class => $configClass) {
            if (isset($classesExtensions[$class])) {
                continue;
            }

            $classesExtensions[$class] = $classExtensions = new \ArrayObject($extensions);
            $this->generateContainersNewClassExtensions($configClass, $classExtensions);

            foreach ($classExtensions as $extension) {
                $newConfigClasses = new \ArrayObject();
                $extension->newConfigClassesProcess($class, $configClass, $newConfigClasses);

                foreach ($newConfigClasses as $newClass => $newConfigClass) {
                    if (isset($classesExtensions[$newClass])) {
                        throw new \RuntimeException(sprintf('The class "%s" has several config classes.', $class));
                    }
                    $configClasses[$newClass] = new \ArrayObject($newConfigClass);
                }

                $this->generateContainersClassesExtensions($extensions, $classesExtensions, $configClasses);
            }
        }
    }

    protected function generateContainersNewClassExtensions(\ArrayObject $configClass, \ArrayObject $classExtensions)
    {
        foreach ($classExtensions as $class => $extension) {
            $newClassExtensions = new \ArrayObject();
            $extension->newClassExtensionsProcess($class, $configClass, $newClassExtensions);

            foreach ($newClassExtensions as $newClassExtension) {
                if (!$newClassExtension instanceof ClassExtension) {
                    throw new \RuntimeException(sprintf('Some class extension of the class "%s" in the extension "%s" is not an instance of ClassExtension.', $class, get_class($extension)));
                }
                if ($newClassExtension instanceof Extension) {
                    throw new \RuntimeException(sprintf('Some class extension of the class "%s" in the extension "%s" is a instance of Extension, and it can be only a instance of ClassExtension.', $class, get_class($extension)));
                }

                $classExtensions[] = $newClassExtension;
                $this->generateContainersNewClassExtensions($configClass, new \ArrayObject(array($newClassExtension)));
            }
        }
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
                $output = $definition->getOutput();
                $dir    = $output->getDir();

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
                $output = $definition->getOutput($name);
                $dir    = $output->getDir();

                $file = $dir.DIRECTORY_SEPARATOR.$definition->getClassName().'.php';

                if (!file_exists($file) || $output->getOverride()) {
                    $dumper  = new Dumper($definition);
                    $content = $dumper->dump();

                    $tmpFile = tempnam(dirname($file), basename($file));
                    if (false === @file_put_contents($tmpFile, $content) || !@rename($tmpFile, $file)) {
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
