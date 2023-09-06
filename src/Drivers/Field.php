<?php

/*
 * This file is part of seat-connector and provides user synchronization between both SeAT and third party platform
 *
 * Copyright (C) 2019 to 2022 Loïc Leuilliot <loic.leuilliot@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Warlof\Seat\Connector\Drivers;

/**
 * Class Field.
 */
class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array|\Illuminate\Contracts\Translation\Translator|string|null
     */
    private $label;

    /**
     * @var string
     */
    private $type;

    /**
     * Field constructor.
     */
    public function __construct(private readonly string $driver, array $field)
    {
        $this->name = $field['name'];
        $this->label = $field['label'];
        $this->type = $field['type'];
    }

    /**
     * @return mixed|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed|null
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function getValue()
    {
        $name = $this->name;
        $settings = setting(sprintf('seat-connector.drivers.%s', $this->driver), true);

        if (is_null($settings)) {
            return null;
        }

        if (! is_object($settings)) {
            return null;
        }

        if (! property_exists($settings, $this->name)) {
            return null;
        }

        return $settings->$name;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $method = sprintf('get%s', ucfirst((string) $name));

        if (property_exists($this, $name)) {
            return $this->$name;
        }

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
}
