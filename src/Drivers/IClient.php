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
 * Interface IClient.
 */
interface IClient
{
    /**
     * @return \Warlof\Seat\Connector\Drivers\IClient
     *
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public static function getInstance(): IClient;

    /**
     * @return \Warlof\Seat\Connector\Drivers\IUser[]
     *
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function getUsers(): array;

    /**
     * @return \Warlof\Seat\Connector\Drivers\ISet[]
     *
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function getSets(): array;

    /**
     * @return \Warlof\Seat\Connector\Drivers\IUser|null
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function getUser(string $id): ?IUser;

    /**
     * @return \Warlof\Seat\Connector\Drivers\ISet|null
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function getSet(string $id): ?ISet;
}
