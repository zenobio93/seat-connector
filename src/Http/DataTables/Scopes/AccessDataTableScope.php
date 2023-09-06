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
 * Class AccessDataTableScope.
 */
class AccessDataTableScope implements DataTableScope
{
    /**
     * AccessDataTableScope constructor.
     *
     * @param  string|null  $connector_driver
     */
    public function __construct(private readonly string $filter_type, private readonly ?string $connector_driver)
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
        if (! empty($this->filter_type)) {
            $query->where('entity_type', $this->filter_type);
        }

        // apply a dummy filter which will always return no result if we don't have any driver provided
        return is_null($this->connector_driver) ?
            $query->whereRaw('? = ?', [0, 1]) :
            $query->where('connector_type', $this->connector_driver);
    }
}
