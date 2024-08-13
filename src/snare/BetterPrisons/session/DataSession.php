<?php

declare(strict_types = 1);

namespace snare\BetterPrisons\session;

use snare\BetterPrisons\BetterPrisons;

class DataSession
{
    /** @var string */
    protected string $name;

    /** @var string */
    protected string $rank;

    /** @var int */
    protected int $prestige;

    /** @var int */
    protected int $blocksBroken;

    public function __construct(string $name, string $rank, int $prestige, int $blocksBroken)
    {
        $this->name = $name;
        $this->rank = $rank;
        $this->prestige = $prestige;
        $this->blocksBroken = $blocksBroken;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRank() : string
    {
        return $this->rank;
    }

    /**
     * @return int
     */
    public function getPrestige() : int
    {
        return $this->prestige;
    }

    /**
     * @return int
     */
    public function getBlocksBroken() : int
    {
        return $this->blocksBroken;
    }

    /**
     * @param string $rank
     */
    public function setRank(string $rank) : void
    {
        BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDatabase()->executeChange("data.users.setRank", [
            "name" => $this->name,
            "rank" => $this->rank
        ]);
    }

    /**
     * @param int $prestige
     */
    public function setPrestige(int $prestige) : void
    {
        BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDatabase()->executeChange("data.users.setPrestige", [
            "name" => $this->name,
            "prestige" => $prestige
        ]);
    }

    /**
     * @param int $blocksBroken
     */
    public function setBlocksBroken(int $blocksBroken) : void
    {
        BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDatabase()->executeChange("data.users.setBlocksBroken", [
            "name" => $this->name,
            "blocksBroken" => $blocksBroken
        ]);
    }
}