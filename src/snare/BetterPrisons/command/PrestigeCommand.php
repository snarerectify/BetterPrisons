<?php

declare(strict_types = 1);

namespace snare\BetterPrisons\command;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\database\exception\RecordNotFoundException;
use cooldogedev\BedrockEconomy\language\KnownMessages;
use cooldogedev\BedrockEconomy\language\LanguageManager;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\cooldogedev\libSQL\exception\SQLException;
use cooldogedev\BedrockEconomy\libs\_1bf65e59a1e61f74\SOFe\AwaitGenerator\Await;
use IvanCraft623\RankSystem\RankSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use snare\BetterPrisons\BetterPrisons;
use snare\BetterPrisons\utils\Utils;
use Generator;

class PrestigeCommand extends Command implements PluginOwned
{
    public function __construct()
    {
        parent::__construct("prestige", "Prestige command.");
        $this->setPermission("betterprisons.prestige");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
    {
        if(!$sender instanceof Player) return false;
        if(($session = BetterPrisons::getBetterPrisons()->getDataSessionManager()->getDataSession($sender->getName())) === null) return false;

        if($session->getPrestige() >= BetterPrisons::getBetterPrisons()->getConfig()->get("max-prestige")) {
            $sender->sendMessage(TextFormat::colorize(BetterPrisons::getBetterPrisons()->getConfig()->get("max-prestige-msg")));
            return false;
        }

        if($session->getRank() !== "z") {
            $sender->sendMessage(TextFormat::colorize(BetterPrisons::getBetterPrisons()->getConfig()->get("must-be-z")));
            return false;
        }

        $entry = GlobalCache::ONLINE()->get($sender->getName());

        if($entry->amount < Utils::getPrestigePrice($session->getPrestige())) {
            $sender->sendMessage(str_replace("{PRICE}", (string)Utils::getPrestigePrice($session->getPrestige()), TextFormat::colorize(BetterPrisons::getBetterPrisons()->getConfig()->get("insufficient-prestige"))));
            return false;
        }

        Await::f2c(
            function () use($sender, $session) : Generator {
                try {
                    yield from BedrockEconomyAPI::ASYNC()->subtract($sender->getXuid(), $sender->getName(), Utils::getPrestigePrice($session->getPrestige()), 1);
                } catch (RecordNotFoundException) {
                    BetterPrisons::getBetterPrisons()->getLogger()->alert(LanguageManager::getString(KnownMessages::ERROR_ACCOUNT_NONEXISTENT));
                } catch(SQLException $exception) {
                    BetterPrisons::getBetterPrisons()->getLogger()->alert(LanguageManager::getString(KnownMessages::ERROR_DATABASE));
                    BetterPrisons::getBetterPrisons()->getLogger()->logException($exception);
                }
            }
        );

        $newRank = $session->getPrestige();
        $newRank++;

        $sender->sendMessage(str_replace(["{PRICE}", "{PRESTIGE}"], [Utils::getPrestigePrice($session->getPrestige()), $newRank], BetterPrisons::getBetterPrisons()->getConfig()->get("prestiged")));

        foreach (Utils::getPrestigeCommands($session->getPrestige()) as $command) {
            BetterPrisons::getBetterPrisons()->getServer()->dispatchCommand(new ConsoleCommandSender(BetterPrisons::getBetterPrisons()->getServer(), BetterPrisons::getBetterPrisons()->getServer()->getLanguage()), str_replace("{PLAYER}", $sender->getName(), $command));
        }

        if(BetterPrisons::getBetterPrisons()->getServer()->getPluginManager()->getPlugin("RankSystem") !== null) {
            foreach (Utils::getPrestigeAddPermissions($session->getPrestige()) as $addPermission) {
                if(!RankSystem::getInstance()->getSessionManager()->get($sender)->hasPermission($addPermission)) RankSystem::getInstance()->getSessionManager()->get($sender)->setPermission($addPermission);
            }

            foreach (Utils::getPrestigeRemovedPermissions($session->getPrestige()) as $removedPermission) {
                if(RankSystem::getInstance()->getSessionManager()->get($session)->hasPermission($removedPermission)) RankSystem::getInstance()->getSessionManager()->get($sender)->removePermission($removedPermission);
            }
        }

        $session->setPrestige($newRank);
        $session->setRank("a");

        if(BetterPrisons::getBetterPrisons()->getConfig()->get("world-name") === "" || BetterPrisons::getBetterPrisons()->getServer()->getWorldManager()->getWorldByName(BetterPrisons::getBetterPrisons()->getConfig()->get("world-name")) === null) {
            $sender->teleport(BetterPrisons::getBetterPrisons()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        } else {
            $sender->teleport(BetterPrisons::getBetterPrisons()->getServer()->getWorldManager()->getWorldByName(BetterPrisons::getBetterPrisons()->getConfig()->get("world-name"))->getSpawnLocation());
        }

        return true;
    }

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin
    {
        return BetterPrisons::getBetterPrisons();
    }
}