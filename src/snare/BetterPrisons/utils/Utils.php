<?php

declare(strict_types = 1);

namespace snare\BetterPrisons\utils;

use pocketmine\world\sound\DyeUseSound;
use snare\BetterPrisons\BetterPrisons;

class Utils
{
    /**
     * @return array
     */
    public static function getRanks() : array
    {
        return BetterPrisons::getBetterPrisons()->getConfig()->get("ranks");
    }

    /**
     * @return array
     */
    public static function getPrestiges() : array
    {
        return BetterPrisons::getBetterPrisons()->getConfig()->get("prestiges");
    }

    /**
     * @param string $rank
     * @return array|null
     */
    public static function getRank(string $rank) : ?array
    {
        return self::getRanks()[$rank] ?? null;
    }

    /**
     * @param int $prestige
     * @return array|null
     */
    public static function getPrestige(int $prestige) : ?array
    {
        return self::getPrestiges()[$prestige] ?? null;
    }

    /**
     * @param string $rank
     * @return int|null
     */
    public static function getRankupPrice(string $rank) : ?int
    {
        return self::getRank($rank) !== null ? (int)self::getRank($rank)["price"] : null;
    }

    /**
     * @param int $prestige
     * @return int|null
     */
    public static function getPrestigePrice(int $prestige) : ?int
    {
        return self::getPrestige($prestige) !== null ? (int)self::getPrestige($prestige)["price"] : null;
    }

    /**
     * @param string $rank
     * @return array|null
     */
    public static function getRankupAddPermissions(string $rank) : ?array
    {
        return self::getRank($rank) !== null ? self::getRank($rank)["added-permissions"] : null;
    }

    /**
     * @param string $rank
     * @return array|null
     */
    public static function getRankupRemovedPermissions(string $rank) : ?array
    {
        return self::getRank($rank) !== null ? self::getRank($rank)["removed-permissions"] : null;
    }

    /**
     * @param int $prestige
     * @return array|null
     */
    public static function getPrestigeAddPermissions(int $prestige) : ?array
    {
        return self::getPrestige($prestige) !== null ? self::getPrestige($prestige)["added-permissions"] : null;
    }

    /**
     * @param int $prestige
     * @return array|null
     */
    public static function getPrestigeRemovedPermissions(int $prestige) : ?array
    {
        return self::getPrestige($prestige) !== null ? self::getPrestige($prestige)["removed-permissions"] : null;
    }

    /**
     * @param string $rank
     * @return array|null
     */
    public static function getRankupCommands(string $rank) : ?array
    {
        return self::getRank($rank) !== null ? self::getRank($rank)["commands"] : null;
    }

    /**
     * @param int $prestige
     * @return array|null
     */
    public static function getPrestigeCommands(int $prestige) : ?array
    {
        return self::getPrestige($prestige) !== null ? self::getPrestige($prestige)["commands"] : null;
    }
}