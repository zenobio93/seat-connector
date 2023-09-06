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
use Seat\Services\Settings\Seat as GlobalSettings;

/**
 * Class ConvertTickerToNewFormat.
 */
class ConvertTickerToNewFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $original = GlobalSettings::get('seat-connector.format');

        if (is_null($original)) {
            // Keep it as default if is not explicitly set.
            return;
        }

        $replaced = sprintf($original, '%2$s', '%1$s');

        GlobalSettings::set('seat-connector.format', $replaced, null);

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
