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

namespace Warlof\Seat\Connector\Http\Controllers\Api\V2;

use Seat\Api\Http\Controllers\Api\v2\ApiController;
use Warlof\Seat\Connector\Http\Resources\UserResource;
use Warlof\Seat\Connector\Models\User;
use OpenApi\Annotations as OA;

/**
 * Class UserController.
 */
class UserController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/v2/seat-connector/users",
     *     tags={"SeAT Connector"},
     *     summary="Get a list of users",
     *     description="Return list of users along with their mapping",
     *     security={
     *      {"ApiKeyAuth": {}}
     *     },
     *     @OA\Response(response=200, description="Successful operation",
     *       @OA\JsonContent(
     *          type="object",
     *          @OA\Property(
     *              type="array",
     *              property="data",
     *              @OA\Items(ref="#/components/schemas/UserResource")
     *          ),
     *          @OA\Property(
     *              property="links",
     *              ref="#/components/schemas/ResourcePaginatedLinks"
     *          ),
     *          @OA\Property(
     *              property="meta",
     *              ref="#/components/schemas/ResourcePaginatedMetadata"
     *          )
     *       )
     *     ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return UserResource::collection(User::paginate());
    }
}
