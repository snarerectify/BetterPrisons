<?php

declare(strict_types = 1);

namespace snare\BetterPrisons\listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\utils\TextFormat;
use snare\BetterPrisons\BetterPrisons;
use Valres\MineSystem\Main;

class EventListener implements Listener
{
    /**
     * @param PlayerLoginEvent $event
     */
    public function onLogin(PlayerLoginEvent $event) : void
    {
        if(BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName()) === null) {
            BetterPrisons::getBetterPrisons()->getDataSessionManager()->createDataSession($event->getPlayer()->getName());
        }
    }

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBreak(BlockBreakEvent $event) : void
    {
        if(BetterPrisons::getBetterPrisons()->getServer()->getPluginManager()->getPlugin("MineSystem") === null) return;
        if(BetterPrisons::getBetterPrisons()->getConfig()->get("mine-crossover") === false) return;
        if(($zone = Main::getInstance()->zoneManager->getZoneByPosition($event->getBlock()->getPosition())) === null) return;
        if(!in_array(strtolower($zone->getName()), range("a", "z"))) return;
        if($event->getPlayer()->hasPermission("betterprisons.bypass")) return;

        if(BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getRank() < strtolower($zone->getName())) {
            $event->cancel();
            $event->getPlayer()->sendMessage(TextFormat::colorize(BetterPrisons::getBetterPrisons()->getConfig()->get("not-correct-rank")));
        }
    }
}