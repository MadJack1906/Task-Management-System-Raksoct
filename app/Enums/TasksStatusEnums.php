<?php

namespace App\Enums;

class TasksStatusEnums
{
    public const STATUS_DONE = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_TODO = 2;

    public static function getStatuses(): array
    {
        return [
            0 => self::STATUS_DONE,
            1 => self::STATUS_IN_PROGRESS,
            2 => self::STATUS_TODO,
        ];
    }

    /**
     * Returns the text of a status
     *
     * @param int $status
     * @return string
     */
    public static function getStatusesText(int $status = 0): string
    {
        return match ($status) {
            self::STATUS_IN_PROGRESS => "In Progress",
            self::STATUS_TODO => "Todo",
            default => "Done",
        };
    }
}
