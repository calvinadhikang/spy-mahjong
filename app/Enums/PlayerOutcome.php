<?php

namespace App\Enums;

enum PlayerOutcome: string
{
    case Win = 'win';
    case Loss = 'loss';
    case Even = 'even';

    public static function fromMoney(float $money): self
    {
        if ($money > 0) {
            return self::Win;
        }

        if ($money < 0) {
            return self::Loss;
        }

        return self::Even;
    }
}
