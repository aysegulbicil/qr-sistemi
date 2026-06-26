<?php

namespace App\Services;

use App\Models\SettingModel;
use App\Models\ShiftModel;
use App\Models\UserModel;

/**
 * Resolves the effective working hours for a user, honouring the company's
 * work mode: "shift" (per-employee shift) or "fixed" (one daily schedule).
 *
 * Always returns an array shaped like a shift row:
 *   start_time, end_time, grace_in_minutes, grace_out_minutes, crosses_midnight
 */
class ShiftResolver
{
    public function forUser(int $userId): array
    {
        $settings = (new SettingModel())->allMerged();

        if ($settings['work_mode'] === 'shift') {
            $user = (new UserModel())->find($userId);
            if ($user !== null && ! empty($user['shift_id'])) {
                $shift = (new ShiftModel())->find((int) $user['shift_id']);
                if ($shift !== null) {
                    return $shift;
                }
            }
        }

        // Fixed company hours (default / fallback)
        return [
            'start_time'        => $settings['fixed_start'] . ':00',
            'end_time'          => $settings['fixed_end'] . ':00',
            'grace_in_minutes'  => (int) $settings['grace_in'],
            'grace_out_minutes' => (int) $settings['grace_out'],
            'crosses_midnight'  => 0,
        ];
    }
}
