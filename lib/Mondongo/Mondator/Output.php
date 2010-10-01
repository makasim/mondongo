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
 * Represents a output for a definition type.
 *
 * @package Mondongo
 * @author  Pablo Díez Pascual <pablodip@gmail.com>
 */
class Output
{
    protected $directory;

    protected $override;

    /**
     * Constructor.
     *
     * @param string $dir      The directory.
     * @param bool   $override The override. It indicate if override files (optional, false by).
     *
     * @return void
     */
    public function __construct($directory, $override = false)
    {
        $this->setDirectory($directory);
        $this->setOverride($override);
    }

    /**
     * Set the directory.
     *
     * @param $string $directory The directory.
     *
     * @return void
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * Returns the directory.
     *
     * @return string The directory.
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set the override. It indicate if override files.
     *
     * @param bool $override The override.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the override is not a bool.
     */
    public function setOverride($override)
    {
        if (!is_bool($override)) {
            throw new \InvalidArgumentException('The override is not a bool.');
        }

        $this->override = $override;
    }

    /**
     * Returns the override.
     *
     * @return bool The override.
     */
    public function getOverride()
    {
        return $this->override;
    }
}
