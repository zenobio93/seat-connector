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
use Warlof\Seat\Connector\Events\EventLogger;
use Warlof\Seat\Connector\Exceptions\MissingDriverClientException;
use Warlof\Seat\Connector\Models\Set;

/**
 * Class DriverUpdateSets.
 */
class DriverUpdateSets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Warlof\Seat\Connector\Drivers\IClient
     */
    private $client;

    /**
     * @var array
     */
    protected $tags = [
        'connector',
    ];

    /**
     * DriverUpdateSets constructor.
     */
    public function __construct(protected string $driver)
    {
        $this->tags = array_merge($this->tags, [$driver]);
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * Process the job.
     *
     * @throws \Warlof\Seat\Connector\Exceptions\MissingDriverClientException
     */
    public function handle()
    {
        // init a buffer which will contain a list of all collected sets
        $processed_sets = [];

        // build the config key related to the requested driver
        $config_key = sprintf('seat-connector.drivers.%s.client', $this->driver);
        $client = config($config_key);

        if (is_null($config_key) || ! class_exists($client)) {
            throw new MissingDriverClientException(sprintf('The client for driver %s is missing.', $this->driver));
        }

        // get the driver client
        $this->client = $client::getInstance();

        // retrieve all sets for the active driver
        $sets = $this->client->getSets();

        // loop over collected set and update database
        foreach ($sets as $set) {
            Set::updateOrCreate([
                'connector_type' => $this->driver,
                'connector_id' => $set->getId(),
            ], [
                'name' => $set->getName(),
            ]);

            // update buffer
            $processed_sets[] = $set->getId();
        }

        // remove all existing sets for that driver which have not been returned this stage
        Set::where('connector_type', $this->driver)
            ->whereNotIn('connector_id', $processed_sets)
            ->delete();

        event(new EventLogger($this->driver, 'info', 'sets', 'Sets metadata has been updated.'));
    }
}
