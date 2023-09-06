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
 * Interface IUser.
 */
interface IUser
{
    /**
     * @return string
     */
    public function getClientId(): string;

    /**
     * @return string
     */
    public function getUniqueId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return bool
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function setName(string $name): bool;

    /**
     * @return \Warlof\Seat\Connector\Drivers\ISet[]
     *
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function getSets(): array;

    /**
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function addSet(ISet $group);

    /**
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function removeSet(ISet $group);
}
