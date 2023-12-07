<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Enum;

enum Card: string
{
    case As = 'A';
    case King = 'K';
    case Queen = 'Q';
    case Jack = 'J';
    case Ten = 'T';
    case Nine = '9';
    case Eight = '8';
    case Seven = '7';
    case Six = '6';
    case Five = '5';
    case Four = '4';
    case Three = '3';
    case Two = '2';
    case One = '1';

    private const Value = [
        self::As->value    => 14,
        self::King->value  => 13,
        self::Queen->value => 12,
        self::Jack->value  => 11,
        self::Ten->value   => 10,
        self::Nine->value  => 9,
        self::Eight->value => 8,
        self::Seven->value => 7,
        self::Six->value   => 6,
        self::Five->value  => 5,
        self::Four->value  => 4,
        self::Three->value => 3,
        self::Two->value   => 2,
        self::One->value   => 1,
    ];

    public static function best(string $cardA, string $cardB, bool $jackAsJoker = false): int
    {
        $cardA = self::from($cardA);
        $cardB = self::from($cardB);

        return $cardA->faceValue($jackAsJoker) <=> $cardB->faceValue($jackAsJoker);
    }

    public function faceValue(bool $jackAsJoker = false): int
    {
        if ($jackAsJoker && $this === self::Jack) {
            return 0;
        }

        return self::Value[$this->value];
    }
}
