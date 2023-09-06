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

namespace Warlof\Seat\Connector\Traits;

use Warlof\Seat\Connector\Drivers\IUser;
use Warlof\Seat\Connector\Events\EventLogger;
use Warlof\Seat\Connector\Models\User;

/**
 * Trait ConnectorPolicyManagement.
 */
trait ConnectorPolicyManagement
{
    /**
     * @throws \Seat\Services\Exceptions\SettingException
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    private function handleSetsUpdate(User $profile, IUser $identity): void
    {
        $user_sets = $identity->getSets();

        // collect all sets which are assigned to the user and determine if they are valid
        $pending_drops = $this->getDroppableSets($profile, $user_sets);

        // collect all valid sets for the current user
        $pending_adds = $this->getGrantableSets($profile, $user_sets);

        // check if there is a set to update
        if ($pending_adds->isNotEmpty() || $pending_drops->isNotEmpty()) {
            $this->updateUserSets($identity, $profile, $pending_adds->toArray(), $pending_drops->toArray());
        }
    }

    /**
     * @throws \Seat\Services\Exceptions\SettingException
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    private function handleNicknameUpdate(User $profile, IUser $identity): void
    {
        $new_nickname = $profile->buildConnectorNickname();

        // identity nick is already up-to-date - we have nothing to do here
        if ($identity->getName() === $new_nickname) {
            return;
        }

        if ($identity->setName($new_nickname)) {
            $profile->connector_name = $identity->getName();
            $profile->save();

            event(new EventLogger($profile->connector_type, 'info', 'policy',
                sprintf('Nickname from the user %s (%s) from group %d has been updated.',
                    '', $identity->getName(), $profile->user->id)));
        }
    }

    /**
     * @param  \Warlof\Seat\Connector\Drivers\ISet[]  $sets
     * @return \Illuminate\Support\Collection
     * @throws \Seat\Services\Exceptions\SettingException
     */
    private function getDroppableSets(User $profile, array $sets)
    {
        $pending_drops = collect();

        foreach ($sets as $set) {
            if ($this->terminator || ! $profile->isAllowedSet($set->getId())) {
                $pending_drops->push($set->getId());
            }
        }

        return $pending_drops;
    }

    /**
     * @return \Illuminate\Support\Collection
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    private function getGrantableSets(User $profile, array $sets)
    {
        $pending_adds = collect();

        if ($this->terminator) {
            return $pending_adds;
        }

        $allowed_sets = $profile->allowedSets();

        foreach ($allowed_sets as $set_id) {
            if (array_filter($sets, fn($set): bool => $set->getId() == $set_id) === []) {
                $pending_adds->push($set_id);
            }
        }

        return $pending_adds;
    }

    /**
     *
     * @throws \Warlof\Seat\Connector\Exceptions\DriverException
     */
    private function updateUserSets(IUser $identity, User $profile, array $pending_adds, array $pending_drops): void
    {
        // drop all sets which have been marked for a removal
        foreach ($pending_drops as $set_id) {
            $set = $this->client->getSet($set_id);
            $identity->removeSet($set);
        }

        // add all sets which have been marked for an addition
        foreach ($pending_adds as $set_id) {
            $set = $this->client->getSet($set_id);

            if (! $set) {
                logger()->error('Unable to retrieve a valid set from platform.', [
                    'platform' => $profile->connector_type,
                    'set ID'   => $set_id,
                ]);

                event(new EventLogger($profile->connector_type, 'critical', 'policy',
                    sprintf('Unable to retrieve a valid set with ID %s from platform.', $set_id)));

                continue;
            }

            $identity->addSet($set);
        }

        event(new EventLogger($profile->connector_type, 'info', 'policy',
            sprintf('Groups has successfully been updated for the user %s (%s) from group %d.',
                '', $identity->getName(), $profile->user->id)));
    }
}
