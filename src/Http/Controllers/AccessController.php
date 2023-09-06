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
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Web\Http\Controllers\Controller;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;
use Seat\Web\Models\User;
use Warlof\Seat\Connector\Http\DataTables\AccessDataTable;
use Warlof\Seat\Connector\Http\DataTables\Scopes\AccessDataTableScope;
use Warlof\Seat\Connector\Http\Validations\AccessRuleValidation;
use Warlof\Seat\Connector\Models\Set;

/**
 * Class AccessManagementController.
 */
class AccessController extends Controller
{
    /**
     * @return mixed
     */
    public function index(AccessDataTable $datatable)
    {
        $filter_type = '';

        // retrieve all registered SeAT Connector drivers
        $available_drivers = config('seat-connector.drivers', []);

        // init the driver using either the query parameter or the first available driver
        $driver = request()->query('driver', Arr::get(Arr::last($available_drivers), 'name'));

        // init the filter type using either the query parameter or public
        switch (request()->query('filter_type', 'users')) {
            case 'public':
                $filter_type = 'public';
                break;
            case 'users':
                $filter_type = User::class;
                break;
            case 'roles':
                $filter_type = Role::class;
                break;
            case 'corporations':
                $filter_type = CorporationInfo::class;
                break;
            case 'titles':
                $filter_type = CorporationTitle::class;
                break;
            case 'alliances':
                $filter_type = Alliance::class;
                break;
            case 'squads':
                $filter_type = Squad::class;
                break;
        }

        return $datatable
            ->addScope(new AccessDataTableScope($filter_type, $driver))
            ->render('seat-connector::access.list');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function create(AccessRuleValidation $request)
    {
        $entity_type = $request->input('entity_type');

        $entity_pk = match ($entity_type) {
            'alliances' => 'alliance_id',
            'corporations' => 'corporation_id',
            default => 'id',
        };

        $set = Set::find($request->input('set_id'));

        if ($entity_type != 'public') {
            if ($set->$entity_type()->where($entity_pk, $request->input('entity_id'))->exists()) {
                return redirect()->back()
                    ->with('warning', 'The rule already exists. Nothing has been changed.');
            }

            $set->$entity_type()->attach($request->input('entity_id'));
        }

        $set->is_public = $entity_type == 'public';
        $set->save();

        return redirect()
            ->back()
            ->with('success', 'The rule has been successfully added.');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function remove(AccessRuleValidation $request)
    {
        $entity_type = $this->classAlias($request->input('entity_type'));

        $entity_pk = match ($entity_type) {
            'alliances' => 'alliance_id',
            'corporations' => 'corporation_id',
            default => 'id',
        };

        $set = Set::find($request->input('set_id'));

        if ($entity_type != 'public') {
            if (! $set->$entity_type()->where($entity_pk, $request->input('entity_id'))->exists()) {
                return redirect()->back()
                    ->with('error', 'The rule does not exists.');
            }

            $set->$entity_type()->detach($request->input('entity_id'));
        }

        if ($entity_type == 'public') {
            $set->is_public = false;
            $set->save();
        }

        return redirect()
            ->back()
            ->with('success', 'The rule has been successfully removed.');
    }

    /**
     * Map Connector class to user friendly alias.
     *
     * @return string
     */
    private function classAlias(string $class_name): string
    {
        switch ($class_name) {
            case User::class:
                return 'users';
            case Role::class:
                return 'roles';
            case CorporationInfo::class:
                return 'corporations';
            case CorporationTitle::class:
                return 'titles';
            case Alliance::class:
                return 'alliances';
            case Squad::class:
                return 'squads';
            default:
                return $class_name;
        }
    }
}
