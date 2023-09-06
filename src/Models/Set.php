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

namespace Warlof\Seat\Connector\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;
use Seat\Web\Models\User;

/**
 * Class PermissionGroup.
 */
class Set extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'seat_connector_sets';

    /**
     * @var array
     */
    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'connector_type', 'connector_id', 'name', 'is_public',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function users()
    {
        return $this->morphedByMany(User::class, 'entity', 'seat_connector_set_entity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function corporations()
    {
        return $this->morphedByMany(CorporationInfo::class, 'entity', 'seat_connector_set_entity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function alliances()
    {
        return $this->morphedByMany(Alliance::class, 'entity', 'seat_connector_set_entity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles()
    {
        return $this->morphedByMany(Role::class, 'entity', 'seat_connector_set_entity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function titles()
    {
        return $this->morphedByMany(CorporationTitle::class, 'entity', 'seat_connector_set_entity');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function squads()
    {
        return $this->morphedByMany(Squad::class, 'entity', 'seat_connector_set_entity');
    }

    /**
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if (! parent::save($options)) {
            return false;
        }

        // in case the set has been registered in public filter - detach all existing filters
        if ($this->is_public) {
            $this->users()->sync([]);
            $this->corporations()->sync([]);
            $this->alliances()->sync([]);
            $this->roles()->sync([]);
            $this->titles()->sync([]);
            $this->squads()->sync([]);
        }

        return true;
    }
}
