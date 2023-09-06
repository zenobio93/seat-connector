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

namespace Warlof\Seat\Connector\Http\DataTables\Scopes;

use Yajra\DataTables\Contracts\DataTableScope;

/**
 * Class LogsDataTableScope.
 */
class LogsDataTableScope implements DataTableScope
{
    /**
     * LogsDataTableScope constructor.
     *
     * @param  mixed  $driver
     */
    public function __construct(private readonly string $connector_driver, private readonly string $level)
    {
    }

    /**
     * Apply a query scope.
     *
     * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $query
     * @return mixed
     */
    public function apply($query)
    {
        // limit entries regarding the requested driver
        if (! empty($this->level)) {
            $query->where('level', $this->level);
        }

        // limit entries regarding the requested driver
        if (! empty($this->connector_driver)) {
            $query->where('connector_type', $this->connector_driver);
        }

        return $query;
    }
}
