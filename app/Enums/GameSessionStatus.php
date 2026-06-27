<?php

namespace App\Enums;

enum GameSessionStatus: string
{
    case Waiting = 'waiting';
    case InProgress = 'in_progress';
    case Finishing = 'finishing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Waiting => 'Waiting',
            self::InProgress => 'In progress',
            self::Finishing => 'Finishing',
            self::Completed => 'Completed',
        };
    }
}
