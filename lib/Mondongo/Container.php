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

namespace Mondongo;

/**
 * Container of the Mondongo.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Container
{
    static protected $mondongo;

    static protected $loader;

    /**
     * Set the Mondongo.
     *
     * @param \Mondongo\Mondongo $mondongo The Mondongo.
     */
    static public function set(Mondongo $mondongo)
    {
        self::$mondongo = $mondongo;
    }

    /**
     * Returns the Mondongo.
     *
     * @return \Mondongo\Mondongo The Mondongo.
     *
     * @throws \RuntimeException If there is loader and the loader does not return an instaoce of \Mondongo\Mondongo.
     * @throws \RuntimeException If there is not Mondongo.
     */
    static public function get()
    {
        if (null !== self::$loader) {
            self::$mondongo = call_user_func(self::$loader);
            if (!self::$mondongo instanceof Mondongo) {
                throw new \RuntimeException('The mondongo is not an instance of \Mondongo\Mondongo.');
            }
        }

        if (null === self::$mondongo) {
            throw new \RuntimeException('There is not Mondongo.');
        }

        return self::$mondongo;
    }

    /**
     * Set the loader.
     *
     * @param mixed $loader The loader.
     *
     * @throws \RuntimeException If there is Mondongo already.
     */
    static public function setLoader($loader)
    {
        if (null !== self::$mondongo) {
            throw new \RuntimeException('There is Mondongo already.');
        }

        self::$loader = $loader;
    }

    /**
     * Returns the loader.
     *
     * @return mixed The loader.
     */
    static public function getLoader()
    {
        return self::$loader;
    }

    /**
     * Clear the Mondongo and the loader.
     */
    static public function clear()
    {
        self::$mondongo = null;
        self::$loader = null;
    }
}
