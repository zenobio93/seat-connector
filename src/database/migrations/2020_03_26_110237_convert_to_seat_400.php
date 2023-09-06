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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Class ConvertToSeat400.
 */
class ConvertToSeat400 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('seat_connector_users')
            ->select('group_id')
            ->orderBy('group_id')
            ->distinct()
            ->each(function ($user): void {
                $migration = DB::table('mig_groups')
                    ->where('group_id', $user->group_id)
                    ->first();

                DB::table('seat_connector_users')
                    ->where('group_id', $user->group_id)
                    ->update([
                        'user_id' => $migration->new_user_id,
                    ]);
            });

        DB::table('seat_connector_set_entity')
            ->select('entity_id')
            ->where('entity_type', 'Seat\Web\Models\Group')
            ->orderBy('entity_id')
            ->distinct()
            ->each(function ($map): void {
                $migration = DB::table('mig_groups')
                    ->where('group_id', $map->entity_id)
                    ->first();

                DB::table('seat_connector_set_entity')
                    ->where('entity_id', $map->entity_id)
                    ->where('entity_type', 'Seat\Web\Models\Group')
                    ->update([
                        'entity_type' => \Seat\Web\Models\User::class,
                        'entity_id'   => $migration->new_user_id,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
}
