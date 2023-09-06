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
 * Interface ISet.
 */
interface ISet
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return \Warlof\Seat\Connector\Drivers\IUser[]
     *
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function getMembers(): array;

    /**
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function addMember(IUser $user);

    /**
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function removeMember(IUser $user);
}
