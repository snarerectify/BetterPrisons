<?php

declare(strict_types = 1);

namespace snare\BetterPrisons;

use pocketmine\plugin\PluginBase;
use snare\BetterPrisons\command\PrestigeCommand;
use snare\BetterPrisons\command\RankupCommand;
use snare\BetterPrisons\listener\EventListener;
use snare\BetterPrisons\session\DataSessionManager;

class BetterPrisons extends PluginBase
{
    /** @var BetterPrisons */
    private static BetterPrisons $betterPrisons;

    /** @var DataSessionManager */
    private DataSessionManager $dataSessionManager;

    public function onLoad(): void
    {
        self::$betterPrisons = $this;
    }

    public function onEnable(): void
    {
        $this->dataSessionManager = new DataSessionManager();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("BetterPrisons", new RankupCommand());
        $this->getServer()->getCommandMap()->register("BetterPrisons", new PrestigeCommand());
    }

    public function onDisable(): void
    {
        $this->dataSessionManager->unload();
    }

    /**
     * @return BetterPrisons
     */
    public static function getBetterPrisons() : BetterPrisons
    {
        return self::$betterPrisons;
    }

    /**
     * @return DataSessionManager
     */
    public function getDataSessionManager() : DataSessionManager
    {
        return $this->dataSessionManager;
    }
}