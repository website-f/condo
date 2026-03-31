<?php

namespace FSPoster\App\Providers\Schedules\Repositories;

use FSPoster\App\Models\Schedule;
use FSPoster\App\Providers\DB\Collection;

class ScheduleRepository
{
    public function markStuckSendingSchedulesAsError(string $date): void
    {
        Schedule::query()
            ->where('status', 'sending')
            ->where('send_time', '<', $date)
            ->update([
                'status' => 'error',
                'error_msg' => fsp__('unknown error')
            ]);
    }

    public function get(int $id): ?Collection
    {
        return Schedule::query()->get($id);
    }
}