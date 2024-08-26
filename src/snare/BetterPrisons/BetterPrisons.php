<?php

declare(strict_types = 1);

namespace snare\BetterPrisons;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\tag\Tag;
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
        $this->saveDefaultConfig();
        $this->dataSessionManager = new DataSessionManager();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("BetterPrisons", new RankupCommand());
        $this->getServer()->getCommandMap()->register("BetterPrisons", new PrestigeCommand());

        if($this->getServer()->getPluginManager()->getPlugin("RankSystem") !== null) {
            RankSystem::getInstance()->getTagManager()->registerTag(new Tag("prison_rank", static function(Session $session) : string {
                $player = $session->getPlayer();

                if($player !== null) {
                    return self::getBetterPrisons()->getDataSessionManager()->getDataSession($player->getName())->getRank();
                } else {
                    return "";
                }
            }));

            RankSystem::getInstance()->getTagManager()->registerTag(new Tag("prison_prestige", static function(Session $session) : string {
                $player = $session->getPlayer();

                if($player !== null) {
                    return (string)self::getBetterPrisons()->getDataSessionManager()->getDataSession($player->getName())->getPrestige();
                } else {
                    return "";
                }
            }));
        }
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