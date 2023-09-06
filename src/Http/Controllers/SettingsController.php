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

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Seat\Web\Http\Controllers\Controller;
use Warlof\Seat\Connector\Drivers\Driver;

/**
 * Class SettingsController.
 */
class SettingsController extends Controller
{
    /**
     * return array.
     */
    final public const ALLOWED_COMMANDS = [
        'seat-connector:sync:sets',
        'seat-connector:apply:policies',
        'seat-connector:apply:policies --terminator',
    ];

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverException
     * @throws \Warlof\Seat\Connector\Exceptions\InvalidDriverSettingsException
     */
    public function index()
    {
        $drivers = collect();
        $available_drivers = config('seat-connector.drivers', []);

        foreach ($available_drivers as $driver => $metadata) {
            $drivers->put($driver, new Driver($metadata));
        }

        return view('seat-connector::settings.list', ['drivers' => $drivers]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function update(Request $request)
    {
        $request->validate([
            'security-level' => 'required|in:strict,normal',
            'use-ticker'     => 'required|boolean',
            'prefix-format'  => 'required|string',
        ]);

        setting(['seat-connector.ticker', $request->input('use-ticker') == '1'], true);
        setting(['seat-connector.strict', $request->input('security-level') == 'strict'], true);
        setting(['seat-connector.format', $request->input('prefix-format')], true);

        return redirect()->back()
            ->with('success', 'SeAT Connector has been updated.');
    }

    public function dispatch(Request $request): void
    {
        $drivers = [...array_keys(config('seat-connector.drivers', [])), ''];

        $request->validate([
            'command' => sprintf('required|in:%s', implode(',', self::ALLOWED_COMMANDS)),
            'driver'  => sprintf('in:%s', implode(',', $drivers)),
        ]);

        $arguments = [];
        $command_string = explode(' ', (string) $request->input('command'));
        $command = Arr::first($command_string);

        // add requested driver filter to command arguments, if any
        if (! empty($request->input('driver'))) {
            $arguments['--driver'][0] = $request->input('driver');
        }

        // add terminator option to command arguments, if any
        if (count($command_string) > 1) {
            $arguments['--terminator'] = true;
        }

        Artisan::call($command, $arguments);
    }
}
