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

namespace Warlof\Seat\Connector\Commands;

use Illuminate\Console\Command;
use Warlof\Seat\Connector\Exceptions\MissingDriverException;
use Warlof\Seat\Connector\Exceptions\UnknownDriverException;

/**
 * Class DriverApplyPolicies.
 */
class DriverApplyPolicies extends Command
{
    /**
     * @var string
     */
    protected $signature = 'seat-connector:apply:policies
                            {--driver=* : The specific driver name for which you want apply policy}
                            {--terminator : Revoke all Sets}';

    /**
     * @var string
     */
    protected $description = 'Enqueue jobs which will apply policy for all installed Drivers.';

    /**
     * @throws \Warlof\Seat\Connector\Exceptions\MissingDriverException
     * @throws \Warlof\Seat\Connector\Exceptions\UnknownDriverException
     */
    public function handle()
    {
        $drivers_parameter = $this->option('driver');
        $drivers = collect(array_keys(config('seat-connector.drivers')));
        $terminator = $this->option('terminator') ?: false;

        if ($drivers->isEmpty()) {
            throw new MissingDriverException('No SeAT Connector drivers has been found.' . PHP_EOL .
                'Please install at least one driver in order to be able to use this command.');
        }

        // request user confirmation before queuing jobs
        if (empty($drivers_parameter)) {
            if (app()->runningInConsole() && ! $this->confirm('Sets from all installed drivers will be synchronized. Do you wish to continue?', true)) {
                return;
            }
        } else {
            if (app()->runningInConsole() && ! $this->confirm(
                sprintf('Sets from %s driver(s) will be synchronized. Do you wish to continue?',
                    implode(',', $drivers_parameter)), true)) {
                return;
            }

            // ensure all provided drivers are valid
            if ($drivers->intersect($drivers_parameter)->count() != (is_countable($drivers_parameter) ? count($drivers_parameter) : 0)) {
                throw new UnknownDriverException();
            }
        }

        foreach ($drivers as $driver) {
            if (! empty($drivers_parameter) && ! in_array($driver, $drivers_parameter)) {
                continue;
            }

            dispatch(new \Warlof\Seat\Connector\Jobs\DriverApplyPolicies($driver, $terminator))->onQueue('high');
            $this->info(sprintf('A new Policy job has been enqueue for driver %s', $driver));
        }
    }
}
