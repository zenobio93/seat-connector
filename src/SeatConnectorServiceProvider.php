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

namespace Warlof\Seat\Connector;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Seat\Eveapi\Models\Character\CharacterAffiliation;
use Seat\Services\AbstractSeatPlugin;
use Seat\Web\Events\UserRoleAdded;
use Seat\Web\Events\UserRoleRemoved;
use Seat\Web\Models\Squads\SquadMember;
use Warlof\Seat\Connector\Commands\DriverApplyPolicies;
use Warlof\Seat\Connector\Commands\DriverUpdateSets;
use Warlof\Seat\Connector\Events\EventLogger;
use Warlof\Seat\Connector\Listeners\LoggerListener;
use Warlof\Seat\Connector\Listeners\UserRoleAddedListener;
use Warlof\Seat\Connector\Listeners\UserRoleRemovedListener;
use Warlof\Seat\Connector\Observers\CharacterAffiliationObserver;
use Warlof\Seat\Connector\Observers\SquadMemberObserver;

/**
 * Class SeatConnectorProvider.
 */
class SeatConnectorServiceProvider extends AbstractSeatPlugin
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->addCommands();
        $this->addMigrations();
        $this->addRoutes();
        $this->addViews();
        $this->addTranslations();
        $this->addApiEndpoints();
        $this->addEvents();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/package.sidebar.php', 'package.sidebar');
        $this->mergeConfigFrom(__DIR__ . '/Config/seat-connector.config.php', 'seat-connector.config');

        $this->registerPermissions(__DIR__ . '/Config/seat-connector.permissions.php', 'seat-connector');
    }

    /**
     * Return the plugin public name as it should be displayed into settings.
     *
     * @return string
     *
     * @example SeAT Web
     */
    public function getName(): string
    {
        return 'SeAT Connector';
    }

    /**
     * Return the plugin repository address.
     *
     * @example https://github.com/eveseat/web
     *
     * @return string
     */
    public function getPackageRepositoryUrl(): string
    {
        return 'https://github.com/warlof/seat-connector';
    }

    /**
     * Return the plugin technical name as published on package manager.
     *
     * @return string
     *
     * @example web
     */
    public function getPackagistPackageName(): string
    {
        return 'seat-connector';
    }

    /**
     * Return the plugin vendor tag as published on package manager.
     *
     * @return string
     *
     * @example eveseat
     */
    public function getPackagistVendorName(): string
    {
        return 'warlof';
    }

    /**
     * Import migrations.
     */
    private function addMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');
    }

    /**
     * Register cli commands.
     */
    private function addCommands(): void
    {
        $this->commands([
            DriverUpdateSets::class,
            DriverApplyPolicies::class,
        ]);
    }

    /**
     * Register views.
     */
    private function addViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'seat-connector');
    }

    /**
     * Import routes.
     */
    private function addRoutes(): void
    {
        if (! $this->app->routesAreCached()) {
            include __DIR__ . '/Http/routes.php';
        }
    }

    /**
     * Import translations.
     */
    private function addTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'seat-connector');
    }

    /**
     * Import API endpoints.
     */
    private function addApiEndpoints(): void
    {
        $this->registerApiAnnotationsPath([
            __DIR__ . '/Http/Resources',
            __DIR__ . '/Http/Controllers/Api/V2',
        ]);
    }

    /**
     * Register events listeners.
     */
    private function addEvents(): void
    {
        Event::listen(EventLogger::class, LoggerListener::class);

        // detect user roles updates
        Event::listen(UserRoleAdded::class, UserRoleAddedListener::class);
        Event::listen(UserRoleRemoved::class, UserRoleRemovedListener::class);

        // detect corporation and alliance affiliation updates and squad membership updates
        CharacterAffiliation::observe(CharacterAffiliationObserver::class);
        SquadMember::observe(SquadMemberObserver::class);
    }
}
