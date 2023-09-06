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

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Warlof\Seat\Connector\Drivers\IUser;
use Warlof\Seat\Connector\Events\EventLogger;
use Warlof\Seat\Connector\Exceptions\InvalidDriverIdentityException;
use Warlof\Seat\Connector\Exceptions\MissingDriverClientException;
use Warlof\Seat\Connector\Models\User;
use Warlof\Seat\Connector\Traits\ConnectorPolicyManagement;

/**
 * Class DriverApplyPolicies.
 */
class DriverApplyPolicies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConnectorPolicyManagement;

    /**
     * @var \Warlof\Seat\Connector\Drivers\IClient
     */
    private $client;

    /**
     * @var bool
     */
    protected $terminator;

    /**
     * @var array
     */
    protected $tags = [
        'connector',
    ];

    /**
     * DriverApplyPolicies constructor.
     */
    public function __construct(protected string $driver, bool $terminator = false)
    {
        $this->terminator = $terminator;
        $this->tags = array_merge($this->tags, [$driver]);

        if ($terminator) {
            $this->tags = array_merge($this->tags, ['terminator']);
        }
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
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function handle()
    {
        $config_key = sprintf('seat-connector.drivers.%s.client', $this->driver);
        $client = config($config_key);

        if (is_null($config_key) || ! class_exists($client)) {
            throw new MissingDriverClientException(sprintf('The client for driver %s is missing.', $this->driver));
        }

        $this->client = $client::getInstance();

        $this->client->getSets();

        // collect all users from the active driver
        $users = $this->client->getUsers();

        if (empty($users)) {
            event(new EventLogger($this->driver, 'warning', 'policy', 'No users has been returned by the platform.'));
        }

        // loop over each entity and apply policy
        foreach ($users as $user) {
            try {
                $this->applyPolicy($user);
            } catch (InvalidDriverIdentityException $e) {
                logger()->warning($e->getMessage(), $e->getTrace());
            } catch (Exception $e) {
                event(new EventLogger($this->driver, 'error', 'policy',
                    sprintf('Unable to update the user %s. %s',
                        $user->getName(), $e->getMessage())));
            }
        }
    }

    /**
     *
     * @throws \Seat\Services\Exceptions\SettingException
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverIdentityException
     */
    private function applyPolicy(IUser $user)
    {
        $profile = User::where('connector_type', $this->driver)
            ->where('connector_id', $user->getClientId())
            ->first();

        // in case the user is unknown of SeAT; skip the process
        if (is_null($profile)) {
            throw new InvalidDriverIdentityException(sprintf('The identity with ID %s is unknown by SeAT', $user->getClientId()));
        }

        $this->handleSetsUpdate($profile, $user);

        $this->handleNicknameUpdate($profile, $user);
    }
}
