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
abstract class Extension extends ClassExtension
{
    protected $configClasses;

    /**
     * Pre global process of the extension.
     *
     * @param Mondongo\Mondator\Container $container     The global container.
     * @param \ArrayObject                $configClasses The config classes.
     *
     * @return void
     */
    public function preGlobalProcess(Container $container, \ArrayObject $configClasses)
    {
        $this->container     = $container;
        $this->configClasses = $configClasses;

        $this->definitions = $container->getDefinitions();
        $this->outputs     = $container->getOutputs();

        $this->doPreGlobalProcess();

        $this->container     = null;
        $this->configClasses = null;
    }

    /**
     * Do the pre global process.
     */
    protected function doPreGlobalProcess()
    {
    }

    /**
     * Post global process of the extension.
     *
     * @param Mondongo\Mondator\Container $container     The global container.
     * @param \ArrayObject                $configClasses The config classes.
     *
     * @return void
     */
    public function postGlobalProcess(Container $container, \ArrayObject $configClasses)
    {
        $this->container     = $container;
        $this->configClasses = $configClasses;

        $this->definitions = $container->getDefinitions();
        $this->outputs     = $container->getOutputs();

        $this->doPreGlobalProcess();

        $this->container     = null;
        $this->configClasses = null;
    }

    /**
     * Do the post global process.
     */
    protected function doPostGlobalProcess()
    {
    }
}
