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

use Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsException;
use Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsType;
use Warlof\Seat\Connector\Exceptions\MissingDriverSettingsField;

/**
 * Class Driver.
 */
class Driver
{
    private readonly string $name;

    private readonly string $icon;

    private readonly string $client;

    /**
     * @var \Warlof\Seat\Connector\Drivers\Field[]
     */
    private readonly \Illuminate\Support\Collection $settings;

    /**
     * Driver constructor.
     *
     *
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverException
     */
    public function __construct(array $config)
    {
        $this->checkStructure($config);

        $this->name = $config['name'];
        $this->icon = $config['icon'];
        $this->client = $config['client'];
        $this->settings = collect();

        foreach ($config['settings'] as $field) {
            $this->settings->push(new Field($this->name, $field));
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getClient(): string
    {
        return $this->client;
    }

    /**
     * @return \Warlof\Seat\Connector\Drivers\Field[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverException
     */
    private function checkStructure(array $structure): void
    {
        $configuration_schema = [
            'name:string',
            'icon:string',
            'client:class',
            'settings:array' => [
                'name:string',
                'label:string',
                'type:enum(checkbox,email,hidden,number,password,text,url)',
            ],
        ];

        $this->validate($configuration_schema, $structure);
    }

    /**
     *
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsException
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsType
     * @throws \Warlof\Seat\Connector\Exceptions\MissingDriverSettingsField
     */
    private function validate(array $schema, array $structure): void
    {
        foreach ($schema as $element => $component) {
            if (is_int($element)) {
                $element = $component;
            }

            $property = $this->validateNode($element, $structure);

            if (! is_array($component)) {
                continue;
            }

            foreach ($component as $sub_element) {
                foreach ($structure[$property] as $value) {
                    $this->validateNode($sub_element, $value);
                }
            }
        }
    }

    /**
     * @param $value
     * @return mixed
     *
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsException
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsType
     * @throws \Warlof\Seat\Connector\Exceptions\MissingDriverSettingsField
     */
    private function validateNode(string $node, array $value): string
    {
        $parts = explode(':', $node);
        $property = $parts[0];
        $type = $parts[1];

        if (! array_key_exists($property, $value)) {
            throw new MissingDriverSettingsField(sprintf('The property %s is missing.', $property));
        }

        if (! $this->is($type, $value[$property])) {
            throw new InvalidDriverSettingsType(sprintf('The property %s must be of type %s.', $property, $type));
        }

        if (is_null($value) || $value === []) {
            throw new InvalidDriverSettingsException(sprintf('The property %s is mandatory.', $property));
        }

        return $property;
    }

    /**
     * @param $value
     * @return bool
     */
    private function is(string $type, $value)
    {
        $method = sprintf('is_%s', $type);

        switch (true) {
            case $type == 'class':
                if (! is_string($value)) {
                    return false;
                }

                return class_exists($value, true);
            case str_starts_with($type, 'enum'):
                $types = explode(',', substr($type, 5, -1));

                return in_array($value, $types);
                break;
            default:
                return $method($value);
        }
    }
}
