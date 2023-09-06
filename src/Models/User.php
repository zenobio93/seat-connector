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
use Seat\Web\Models\User as SeatUser;

/**
 * Class User.
 */
class User extends Model
{
    /**
     * @var string
     */
    protected $table = 'seat_connector_users';

    /**
     * @var array
     */
    protected $fillable = [
        'connector_type', 'connector_id', 'connector_name', 'user_id', 'unique_id',
    ];

    /**
     * @var array
     */
    private $allowed_sets = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(SeatUser::class, 'user_id', 'id');
    }

    /**
     * @return bool
     */
    public function isEnabledAccount(): bool
    {
        return $this->user->active;
    }

    /**
     * @return bool
     */
    public function areAllTokensValid(): bool
    {
        return $this->user->refresh_tokens->count() == $this->user->all_characters()->count();
    }

    /**
     * @return bool
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function isAllowedSet(string $set_id): bool
    {
        return in_array($set_id, $this->allowedSets());
    }

    /**
     * @return array
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function allowedSets(): array
    {
        $strict_mode = setting('seat-connector.strict', true);

        $active_tokens = $this->user->refresh_tokens;

        if (empty($active_tokens) || ($strict_mode && ! $this->areAllTokensValid()) || ! $this->isEnabledAccount()) {
            return [];
        }

        if (! empty($this->allowed_sets)) {
            return $this->allowed_sets;
        }

        $rows = $this->getUserSets()
            ->union($this->getRoleSets())
            ->union($this->getCorporationSets())
            ->union($this->getTitleSets())
            ->union($this->getAllianceSets())
            ->union($this->getPublicSets())
            ->union($this->getSquadSets())
            ->get();

        $this->allowed_sets = $rows->unique('connector_id')->pluck('connector_id')->toArray();

        return $this->allowed_sets;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getUserSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->whereHas('users', function ($query): void {
                $query->where('entity_id', $this->user_id);
            })
            ->select('connector_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getRoleSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->whereHas('roles', function ($query): void {
                $query->whereIn('entity_id', $this->user->roles->pluck('id'));
            })
            ->select('connector_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getCorporationSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->whereHas('corporations', function ($query): void {
                $corporations = $this->user->characters->pluck('affiliation.corporation_id');

                $query->whereIn('entity_id', $corporations);
            })
            ->select('connector_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getTitleSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->whereHas('titles', function ($query): void {
                $titles = $this->user->characters->map(fn($item) => $item->titles->pluck('id'));

                $query->whereIn('entity_id', $titles->flatten());
            })
            ->select('connector_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getAllianceSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->whereHas('alliances', function ($query): void {
                $alliances = $this->user->characters->pluck('affiliation.alliance_id');

                $query->whereIn('entity_id', $alliances);
            })
            ->select('connector_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getSquadSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->whereHas('squads', function ($query): void {
                $query->whereIn('entity_id', $this->user->squads->pluck('id'));
            })
            ->select('connector_id');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function getPublicSets()
    {
        return Set::where('connector_type', $this->connector_type)
            ->where('is_public', true)
            ->select('connector_id');
    }

    /**
     * @return string
     *
     * @throws \Seat\Services\Exceptions\SettingException
     */
    public function buildConnectorNickname(): string
    {
        $character = $this->user->main_character;

        if (is_null($character->name)) {
            $character = $this->user->characters->first();
        }

        $nickname = $this->user->name;

        if (! is_null($character)) {
            $nickname = $character->name;

            if (setting('seat-connector.ticker', true)) {
                $corporation = CorporationInfo::find($character->affiliation->corporation_id);
                $alliance = is_null($character->affiliation->alliance_id) ? null : Alliance::find($character->affiliation->alliance_id);
                $format = setting('seat-connector.format', true) ?: '[%2$s] %1$s';

                $corp_ticker = $corporation->ticker ?? '';
                $alliance_ticker = $alliance->ticker ?? '';

                $nickname = sprintf($format, $nickname, $corp_ticker, $alliance_ticker);
            }
        }

        return $nickname;
    }
}
