<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/24
 * Time: 17:01
 */

namespace App\Library;


class VoteStatus
{
    const HIDDEN = 0;
    const PREVIEWING = 1;
    const VOTING = 2;
    const RESULTS_RELEASED = 3;

    static function getDescription(int $value) {
        switch ($value) {
            case self::HIDDEN:
                return "hidden";
            case self::PREVIEWING:
                return "previewing";
            case self::VOTING:
                return "voting";
            case self::RESULTS_RELEASED:
                return "results_released";
            default:
                return "unknown";
        }
    }

    static function getValue(string $description) {
        switch ($description) {
            case "hidden":
                return self::HIDDEN;
            case "previewing":
                return self::PREVIEWING;
            case "voting":
                return self::VOTING;
            case "results_released":
                return self::RESULTS_RELEASED;
        }
    }
}