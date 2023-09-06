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

namespace Warlof\Seat\Connector\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Seat\Web\Models\User as SeatUser;
use Warlof\Seat\Connector\Observers\AbstractIdentityObserver;

/**
 * Class NotifyDriver.
 */
class NotifyDriver extends AbstractIdentityObserver implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    protected $tags = [
        'connector',
    ];

    /**
     * NotifyDriver constructor.
     */
    public function __construct(private readonly SeatUser $user)
    {
        $this->tags = array_merge($this->tags, [sprintf('user_id:%s', Str::slug($user->name, '_'))]);
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * Job.
     */
    public function handle(): void
    {
        $this->notifyDrivers($this->user);
    }
}
