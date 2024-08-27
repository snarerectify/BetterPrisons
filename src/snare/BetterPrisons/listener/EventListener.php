<?php

declare(strict_types = 1);

namespace snare\BetterPrisons\listener;

use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\database\transaction\TransferTransaction;
use cooldogedev\BedrockEconomy\database\transaction\UpdateTransaction;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSuccessEvent;
use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\exception\SQLException;
use snare\BetterPrisons\BetterPrisons;
use snare\BetterPrisons\utils\Utils;
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
        if(!in_array(strtolower($event->getBlock()->getPosition()->getWorld()->getFolderName()), range("a", "z"))) return;
        if(!$event->isCancelled()) return;

        BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->setBlocksBroken(BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getBlocksBroken() + 1);

        if(BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getPrestige() >= BetterPrisons::getBetterPrisons()->getConfig()->get("max-prestige")) {
            $required = "Prestige";
        } else {
            $required = (Utils::getRequiredBlocksBroken(BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getPrestige()) - BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getBlocksBroken()) > 0 ? Utils::getRequiredBlocksBroken(BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getPrestige()) - BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($event->getPlayer()->getName())->getBlocksBroken() : 0;
        }

        $ev = new PlayerTagUpdateEvent($event->getPlayer(), new ScoreTag("scorehudx.prisonrequiredblocks", (string)$required));
        $ev->call();
    }

    /**
     * @param TagsResolveEvent $event
     */
    public function onResolve(TagsResolveEvent $event) : void
    {
        $tag = $event->getTag();
        $player = $event->getPlayer();
        $session = BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($player->getName());

        if($session->getPrestige() >= BetterPrisons::getBetterPrisons()->getConfig()->get("max-prestige")) {
            $requiredBlocks = "N/A";
            $requiredPrestige = "N/A";
            $requiredRank = "N/A";
        } elseif($session->getRank() === "z") {
            $requiredRank = "Prestige";
            $requiredPrestige = (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($event->getPlayer()->getName())->amount) > 0 ? (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($event->getPlayer()->getName())->amount) : 0;
            $requiredBlocks = (Utils::getRequiredBlocksBroken($session->getPrestige()) - $session->getBlocksBroken()) > 0 ? (Utils::getRequiredBlocksBroken($session->getPrestige()) - $session->getBlocksBroken()) : 0;
        } else {
            $requiredBlocks = (Utils::getRequiredBlocksBroken($session->getPrestige()) - $session->getBlocksBroken()) > 0 ? (Utils::getRequiredBlocksBroken($session->getPrestige()) - $session->getBlocksBroken()) : 0;
            $rankupAmount = $session->getPrestige() === 0 ? Utils::getRankupPrice($session->getRank()) : (Utils::getRankupPrice($session->getRank()) * (BetterPrisons::getBetterPrisons()->getConfig()->get("prestige-multiplier") * $session->getPrestige()));
            $requiredRank = ($rankupAmount - GlobalCache::ONLINE()->get($event->getPlayer()->getName())->amount) > 0 ? ($rankupAmount - GlobalCache::ONLINE()->get($event->getPlayer()->getName())->amount) : 0;
            $requiredPrestige = (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($event->getPlayer()->getName())->amount) > 0 ? (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($event->getPlayer()->getName())->amount) : 0;
        }

        switch ($tag->getName()) {
            case "scorehudx.prisonrank":
                $tag->setValue(strtoupper($session->getRank()));
            break;

            case "scorehudx.prisonprestige":
                $tag->setValue((string)$session->getPrestige());
            break;

            case "scorehudx.prisonrequiredblocks":
                $tag->setValue((string)$requiredBlocks);
            break;

            case "scorehudx.prisonrequiredrank":
                $tag->setValue((string)$requiredRank);
            break;

            case "scorehudx.prisonrequiredprestige":
                $tag->setValue((string)$requiredPrestige);
            break;
        }
    }

    /**
     * @param TransactionSuccessEvent $event
     */
    public function onMoneyChange(TransactionSuccessEvent $event) : void
    {
        $transaction = $event->transaction;
        if(!$transaction instanceof UpdateTransaction) return;

        $player = $transaction->username;
        $session = BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($player);

        if($session === null) return;

        if($session->getPrestige() >= BetterPrisons::getBetterPrisons()->getConfig()->get("max-prestige")) {
            $requiredPrestige = "N/A";
            $requiredRank = "N/A";
        } elseif($session->getRank() === "z") {
            $requiredRank = "Prestige";
            $requiredPrestige = (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($player)->amount) > 0 ? (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($player)->amount) : 0;
        } else {
            $rankupAmount = $session->getPrestige() === 0 ? Utils::getRankupPrice($session->getRank()) : (Utils::getRankupPrice($session->getRank()) * (BetterPrisons::getBetterPrisons()->getConfig()->get("prestige-multiplier") * $session->getPrestige()));
            $requiredRank = ($rankupAmount - GlobalCache::ONLINE()->get($player)->amount) > 0 ? ($rankupAmount - GlobalCache::ONLINE()->get($player)->amount) : 0;
            $requiredPrestige = (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($player)->amount) > 0 ? (Utils::getPrestigePrice($session->getPrestige()) - GlobalCache::ONLINE()->get($player)->amount) : 0;
        }

        $ev = new PlayerTagsUpdateEvent(Server::getInstance()->getPlayerExact($player), [new ScoreTag("scorehudx.prisonrequiredrank", (string)$requiredRank), new ScoreTag("scorehudx.prisonrequiredprestige", (string)$requiredPrestige)]);
        $ev->call();
    }
}