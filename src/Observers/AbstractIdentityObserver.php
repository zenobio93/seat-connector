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

namespace Warlof\Seat\Connector\Observers;

use Exception;
use Seat\Web\Models\User as SeatUser;
use Warlof\Seat\Connector\Drivers\IUser;
use Warlof\Seat\Connector\Events\EventLogger;
use Warlof\Seat\Connector\Models\User;
use Warlof\Seat\Connector\Traits\ConnectorPolicyManagement;

/**
 * Class AbstractIdentityObserver.
 */
abstract class AbstractIdentityObserver
{
    use ConnectorPolicyManagement;

    /**
     * @var \Warlof\Seat\Connector\Drivers\IClient
     */
    private $client;

    /**
     * @var bool
     */
    private $terminator = false;

    public function notifyDrivers(SeatUser $user): void
    {
        // extract registered drivers from the configuration stack
        $drivers = collect(array_keys(config('seat-connector.drivers')));

        // retrieve all identities bind to the user
        $profiles = User::where('user_id', $user->id)->get();

        if ($drivers->isEmpty()) {
            return;
        }

        foreach ($drivers as $driver) {

            // extract identity related to current driver and retrieve platform user instance
            if (! $profile = $profiles->where('connector_type', $driver)->first()) {
                event(new EventLogger($driver, 'warning', 'policy',
                    sprintf('User %s does not have any identity.', $user->name)));

                continue;
            }

            $config_key = sprintf('seat-connector.drivers.%s.client', $driver);
            $client = config($config_key);

            if (is_null($config_key) || ! class_exists($client)) {
                event(new EventLogger($driver, 'emergency', 'policy',
                    'Unable to detect client for this driver.'));

                continue;
            }

            try {
                $this->client = $client::getInstance();

                // load platform sets in cache
                $this->client->getSets();

                // load platform users in cache
                $this->client->getUsers();

                // retrieve user identity instance
                if (! $identity = $this->client->getUser($profile->connector_id)) {
                    event(new EventLogger($driver, 'critical', 'policy',
                        sprintf('Unable to retrieve identity for %s using %s', $user->name, $profile->connector_id)));

                    continue;
                }

                $this->applyPolicy($profile, $identity);
            } catch (Exception $e) {
                event(new EventLogger($driver, 'error', 'policy',
                    sprintf('Unable to update the user %s. %s', $user->name, $e->getMessage())));
            }
        }
    }

    /**
     * @throws \Seat\Services\Exceptions\SettingException
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    public function applyPolicy(User $profile, IUser $identity): void
    {
        $this->handleSetsUpdate($profile, $identity);

        $this->handleNicknameUpdate($profile, $identity);
    }
}
