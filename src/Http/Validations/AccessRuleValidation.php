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

namespace Warlof\Seat\Connector\Http\Validations;

use Illuminate\Foundation\Http\FormRequest;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Corporation\CorporationTitle;
use Seat\Web\Models\Acl\Role;
use Seat\Web\Models\Squads\Squad;
use Seat\Web\Models\User;

/**
 * Class AccessRuleValidation.
 */
class AccessRuleValidation extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $filter_type = [
            'public',
            'users',
            User::class,
            'roles',
            Role::class,
            'corporations',
            CorporationInfo::class,
            'alliances',
            Alliance::class,
            'titles',
            CorporationTitle::class,
            'squads',
            Squad::class,
        ];

        return [
            'entity_id'   => 'required_unless:entity_type,public|integer',
            'entity_type' => 'required|in:' . implode(',', $filter_type),
            'set_id'      => 'required|exists:seat_connector_sets,id',
        ];
    }
}
