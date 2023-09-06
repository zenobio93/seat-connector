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

namespace Warlof\Seat\Connector\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Seat\Web\Events\UserRoleRemoved;
use Seat\Web\Models\User;
use Warlof\Seat\Connector\Observers\AbstractIdentityObserver;

/**
 * Class UserRoleRemovedListener.
 */
class UserRoleRemovedListener extends AbstractIdentityObserver implements ShouldQueue
{
    /**
     * @var int
     */
    public $delay = 60;

    /**
     * @var string
     */
    public $queue = 'high';

    public function handle(UserRoleRemoved $event): void
    {
        $user = User::find($event->user_id);

        if (! $user) {
            return;
        }

        $this->notifyDrivers($user);
    }

    /**
     * @return bool
     */
    public function shouldQueue(UserRoleRemoved $event): bool
    {
        return User::find($event->user_id) != null;
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return [
            'connector',
        ];
    }
}
