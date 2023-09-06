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

namespace Warlof\Seat\Connector\Http\Controllers;

use Illuminate\Support\Arr;
use Seat\Web\Http\Controllers\Controller;
use Warlof\Seat\Connector\Exceptions\DriverException;
use Warlof\Seat\Connector\Exceptions\DriverSettingsException;
use Warlof\Seat\Connector\Exceptions\InvalidDriverIdentityException;
use Warlof\Seat\Connector\Http\DataTables\Scopes\UserDataTableScope;
use Warlof\Seat\Connector\Http\DataTables\UserMappingDataTable;
use Warlof\Seat\Connector\Models\User;

/**
 * Class UsersController.
 */
class UsersController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(UserMappingDataTable $datatable)
    {
        // retrieve all registered SeAT Connector drivers
        $available_drivers = config('seat-connector.drivers', []);

        // init the driver using either the query parameter or the first available driver
        $driver = request()->query('driver') ?: Arr::get(Arr::last($available_drivers), 'name');

        return $datatable
            ->addScope(new UserDataTableScope($driver))
            ->render('seat-connector::users.list');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        // attempt to retrieve requested identity
        $identity = User::find($id);

        if (is_null($identity)) {
            return redirect()->back()
                ->with('error', 'An error occurred attempting to delete the user mapping. Identity is not found.');
        }

        // load driver instance
        $config_key = sprintf('seat-connector.drivers.%s.client', $identity->connector_type);
        $client = config($config_key);

        if (is_null($config_key) || ! class_exists($client)) {
            return redirect()->back()
                ->with('error', sprintf('The client for driver %s is missing.', $identity->connector_type));
        }

        try {
            $instance = $client::getInstance();

            // retrieve platform user
            $user = $instance->getUser($identity->connector_id);

            // request platform user sets
            $sets = $user->getSets();

            // drop all sets
            foreach ($sets as $set) {
                $user->removeSet($set);
            }

            // remove user identity
            $identity->delete();
        } catch (InvalidDriverIdentityException|DriverSettingsException) {
            $identity->delete();
        } catch (DriverException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Identity has been successfully dropped.');
    }
}
