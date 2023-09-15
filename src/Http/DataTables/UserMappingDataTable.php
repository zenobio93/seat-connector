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

use Warlof\Seat\Connector\Models\User;
use Yajra\DataTables\Services\DataTable;

/**
 * Class UserMappingDataTable.
 */
class UserMappingDataTable extends DataTable
{
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Exception
     */
    public function ajax(): \Illuminate\Http\JsonResponse
    {
        return datatables()
            ->eloquent($this->applyScopes($this->query()))
            ->editColumn('action', fn($row) => view('seat-connector::users.partials.delete', ['row' => $row]))
            ->toJson();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
    {
        return User::with('user')
            ->select('seat_connector_users.id', 'connector_id', 'connector_name', 'user_id');
    }

    /**
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): \Yajra\DataTables\Html\Builder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) { d.driver = $("#connector-table-filters a.active").data("filter"); }',
            ])
            ->addAction();
    }

    /**
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            [
                'data'  => 'user.id',
                'title' => trans('seat-connector::seat.user_id'),
            ],
            [
                'data'  => 'user.main_character_id',
                'title' => trans('seat-connector::seat.character_id'),
            ],
            [
                'data'  => 'user.name',
                'title' => trans('seat-connector::seat.character_name'),
            ],
            [
                'data'  => 'connector_id',
                'title' => trans('seat-connector::seat.connector_id'),
            ],
            [
                'data'  => 'connector_name',
                'title' => trans('seat-connector::seat.connector_name'),
            ],
        ];
    }
}
