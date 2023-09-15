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

namespace Warlof\Seat\Connector\Http\DataTables;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;
use Seat\Web\Models\User;
use Warlof\Seat\Connector\Models\Set;
use Yajra\DataTables\Services\DataTable;

/**
 * Class AccessDataTable.
 */
class AccessDataTable extends DataTable
{
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function ajax(): \Illuminate\Http\JsonResponse
    {
        return datatables()
            ->query($this->applyScopes($this->query()))
            ->editColumn('action', fn($row) => view('seat-connector::access.includes.buttons.remove', ['row' => $row]))
            ->editColumn('entity_name', fn($row): string => strip_tags((string) $row->entity_name))
            ->toJson();
    }

    /**
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function query(): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $corporation = $this->getCorporationQuery();
        $titles = $this->getTitleQuery();
        $alliances = $this->getAllianceQuery();
        $roles = $this->getRoleQuery();
        $users = $this->getUserQuery();
        $public = $this->getPublicQuery();
        $squads = $this->getSquadQuery();

        $union = $corporation->union($titles->getQuery())
            ->union($alliances->getQuery())
            ->union($roles->getQuery())
            ->union($users->getQuery())
            ->union($public->getQuery())
            ->union($squads->getQuery());

        return DB::query()->fromSub($union, 'mapping');
    }

    /**
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): \Yajra\DataTables\Html\Builder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->addAction([
                'class' => 'text-right',
                'title' => '',
            ])
            ->ajax([
                'data' => 'function(d) { d.driver = $("#connector-driver").val(); d.filter_type = $("#connector-table-filters a.active").data("filter"); }',
            ]);
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return [
            'entity_name' => [
                'data'  => 'entity_name',
                'title' => trans('seat-connector::seat.entity_name'),
            ],
            'name' => [
                'data'  => 'name',
                'title' => trans_choice('seat-connector::seat.sets', 0),
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getCorporationQuery()
    {
        return Set::
            join('seat_connector_set_entity', 'set_id', 'id')
            ->join((new CorporationInfo())->getTable(), function ($join): void {
                $join->on('entity_id', 'corporation_id');
                $join->where('entity_type', CorporationInfo::class);
            })
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name',
                'seat_connector_set_entity.entity_type',
                'seat_connector_set_entity.entity_id',
                (new CorporationInfo())->getTable() . '.name as entity_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getTitleQuery()
    {
        return Set::
            join('seat_connector_set_entity', 'set_id', 'seat_connector_sets.id')
            ->join((new CorporationTitle())->getTable(), function ($join): void {
                $join->on('entity_id', (new CorporationTitle())->getTable() . '.id')
                    ->where('entity_type', CorporationTitle::class);
            })
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name',
                'seat_connector_set_entity.entity_type',
                'seat_connector_set_entity.entity_id',
                (new CorporationTitle())->getTable() . '.name as entity_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getAllianceQuery()
    {
        return Set::
            join('seat_connector_set_entity', 'set_id', 'id')
            ->join((new Alliance())->getTable(), function ($join): void {
                $join->on('entity_id', 'alliance_id');
                $join->where('entity_type', Alliance::class);
            })
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name',
                'seat_connector_set_entity.entity_type',
                'seat_connector_set_entity.entity_id',
                (new Alliance())->getTable() . '.name as entity_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getRoleQuery()
    {
        return Set::
            join('seat_connector_set_entity', 'set_id', 'id')
            ->join((new Role())->getTable(), function ($join): void {
                $join->on('entity_id', (new Role())->getTable() . '.id');
                $join->where('entity_type', Role::class);
            })
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name',
                'seat_connector_set_entity.entity_type',
                'seat_connector_set_entity.entity_id',
                (new Role())->getTable() . '.title as entity_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getUserQuery()
    {
        return Set::
            join('seat_connector_set_entity', 'set_id', 'id')
            ->join((new User())->getTable(), function ($join): void {
                $join->on('entity_id', (new User())->getTable() . '.id');
                $join->where('entity_type', User::class);
            })
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name',
                'seat_connector_set_entity.entity_type',
                'seat_connector_set_entity.entity_id',
                (new User())->getTable() . '.name as entity_name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getPublicQuery()
    {
        return Set::where('is_public', true)
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name'
            )
            ->selectRaw('? as entity_type, ? as entity_id, ? as entity_name', ['public', '0', '']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getSquadQuery()
    {
        return Set::
            join('seat_connector_set_entity', 'set_id', 'id')
            ->join((new Squad())->getTable(), function ($join): void {
                $join->on('entity_id', (new Squad())->getTable() . '.id');
                $join->where('entity_type', Squad::class);
            })
            ->select(
                'seat_connector_sets.id',
                'seat_connector_sets.connector_type',
                'seat_connector_sets.connector_id',
                'seat_connector_sets.name',
                'seat_connector_set_entity.entity_type',
                'seat_connector_set_entity.entity_id',
                (new Squad())->getTable() . '.name as entity_name');
    }
}
