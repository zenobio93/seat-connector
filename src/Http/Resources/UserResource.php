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

namespace Warlof\Seat\Connector\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * Class UserResource.
 *
 * @OA\Schema(
 *     description="SeAT to Connector User mapping model",
 *     title="Connector User",
 *     type="object"
 * )
 *
 * @OA\Property(
 *     type="integer",
 *     minimum=1,
 *     description="SeAT User ID",
 *     property="user_id",
 *     example=1
 * )
 *
 * @OA\Property(
 *     type="string",
 *     description="SeAT Connector driver",
 *     property="connector_type",
 *     example="discord"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     description="SeAT Connector entity ID for this driver",
 *     property="connector_id",
 *     example="133312047051046912"
 * )
 *
 * @OA\Property(
 *     type="string",
 *     description="SeAT Connector entity name for this driver",
 *     property="connector_name",
 *     example="Demo User"
 * )
 */
class UserResource extends Resource
{
    public $user_id;
    public $connector_type;
    public $connector_id;
    public $connector_name;
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id'        => $this->user_id,
            'connector_type' => $this->connector_type,
            'connector_id'   => $this->connector_id,
            'connector_name' => $this->connector_name,
        ];
    }
}
