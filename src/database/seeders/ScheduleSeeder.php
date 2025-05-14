<?php

namespace Warlof\Seat\Connector\Database\Seeders;

use Seat\Services\Seeding\AbstractScheduleSeeder;

class ScheduleSeeder extends AbstractScheduleSeeder
{

    /**
     * Returns an array of schedules that should be seeded whenever the stack boots up.
     *
     * @return array
     */
    public function getSchedules(): array
    {
        return [
            [   // Sync sets for all drivers | Once a day
                'command' => 'seat-connector:sync:sets',
                'expression' => '0 0 * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ],
            [   // Apply policies | Every hour
                'command' => 'seat-connector:apply:policies',
                'expression' => '0 * * * *',
                'allow_overlap' => false,
                'allow_maintenance' => false,
                'ping_before' => null,
                'ping_after' => null,
            ]
        ];
    }

    /**
     * Returns a list of commands to remove from the schedule.
     *
     * @return array
     */
    public function getDeprecatedSchedules(): array
    {
        return [];
    }
}