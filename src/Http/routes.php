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

Route::group([
    'namespace' => 'Warlof\Seat\Connector\Http\Controllers',
], function (): void {
    Route::group([
        'prefix'     => 'seat-connector',
        'middleware' => ['web', 'auth', 'locale'],
    ], function (): void {
        Route::get('/identities')
            ->name('seat-connector.identities')
            ->uses('IdentitiesController@index')
            ->middleware('can:seat-connector.view');

        Route::group([
            'middleware' => 'can:global.superuser',
        ], function (): void {
            Route::get('/settings')
                ->name('seat-connector.settings')
                ->uses('SettingsController@index');

            Route::post('/settings')
                ->name('seat-connector.settings.update')
                ->uses('SettingsController@update');

            Route::post('/settings/command')
                ->name('seat-connector.settings.command')
                ->uses('SettingsController@dispatch');
        });

        Route::group([
            'middleware' => 'can:seat-connector.security',
        ], function (): void {
            Route::get('/users')
                ->name('seat-connector.users')
                ->uses('UsersController@index');

            Route::delete('/users/{id}')
                ->name('seat-connector.users.destroy')
                ->uses('UsersController@destroy');

            Route::get('/access')
                ->name('seat-connector.acl')
                ->uses('AccessController@index');

            Route::post('/access')
                ->name('seat-connector.acl.create')
                ->uses('AccessController@create');

            Route::delete('/access')
                ->name('seat-connector.acl.remove')
                ->uses('AccessController@remove');

            Route::group([
                'prefix' => 'api',
            ], function (): void {
                Route::get('/roles')
                    ->name('seat-connector.api.roles')
                    ->uses('LookupController@getRoles');

                Route::get('/titles')
                    ->name('seat-connector.api.titles')
                    ->uses('LookupController@getTitles');

                Route::get('/squads')
                    ->name('seat-connector.api.squads')
                    ->uses('LookupController@getSquads');

                Route::get('/sets')
                    ->name('seat-connector.api.sets')
                    ->uses('LookupController@getSets');
            });
        });

        Route::group([
            'middleware' => 'can:seat-connector.logs_review',
        ], function (): void {
            Route::get('/logs')
                ->name('seat-connector.logs')
                ->uses('LogsController@index');

            Route::delete('/logs')
                ->name('seat-connector.logs.destroy')
                ->uses('LogsController@destroy');
        });
    });

    Route::group([
        'namespace'  => 'Api',
        'middleware' => ['api.request', 'api.auth'],
        'prefix'     => 'api',
    ], function (): void {
        Route::group([
            'namespace' => 'V2',
            'prefix'    => 'v2',
        ], function (): void {
            Route::group([
                'prefix' => 'seat-connector',
            ], function (): void {
                Route::get('/users', 'UserController@index');
            });
        });
    });
});
