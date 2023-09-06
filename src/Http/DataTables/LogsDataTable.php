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

use Warlof\Seat\Connector\Models\Log;
use Yajra\DataTables\Services\DataTable;

/**
 * Class LogsDataTable.
 */
class LogsDataTable extends DataTable
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
            ->editColumn('created_at', fn($row) => view('web::partials.date', ['datetime' => $row->created_at]))
            ->editColumn('level', fn($row) => view('seat-connector::logs.includes.level', ['row' => $row]))
            ->make(true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return Log::query();
    }

    /**
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->parameters([
                'drawCallback' => 'function() { $("[data-toggle=tooltip]").tooltip(); }',
            ])
            ->ajax([
                'data' => 'function(d) { d.driver = $("#connector-driver").val(); d.level = $("#connector-log-level").data("level"); }',
            ]);
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        return [
            [
                'data' => 'created_at',
                'title' => trans('seat-connector::seat.datetime'),
            ],
            [
                'data' => 'level',
                'title' => trans('seat-connector::seat.level'),
            ],
            [
                'data' => 'connector_type',
                'title' => trans('seat-connector::seat.driver'),
            ],
            [
                'data' => 'category',
                'title' => trans('seat-connector::seat.category'),
            ],
            [
                'data' => 'message',
                'title' => trans('seat-connector::seat.message'),
            ],
        ];
    }
}
