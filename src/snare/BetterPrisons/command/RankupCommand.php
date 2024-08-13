<?php

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
use Generator;
use snare\BetterPrisons\BetterPrisons;
use snare\BetterPrisons\utils\Utils;

class RankupCommand extends Command implements PluginOwned
{
    public function __construct()
    {
        parent::__construct("rankup", "Rankup command.", null, ["ru"]);
        $this->setPermission("betterprisons.rankup");
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

        if($session->getRank() === "z") {
            $sender->sendMessage(TextFormat::colorize(BetterPrisons::getBetterPrisons()->getConfig()->get("max-rank")));
            return false;
        }

        $entry = GlobalCache::ONLINE()->get($sender->getName());

        if($entry->amount < Utils::getRankupPrice($session->getRank())) {
            $sender->sendMessage(str_replace("{PRICE}", Utils::getRankupPrice($session->getRank()), TextFormat::colorize(BetterPrisons::getBetterPrisons()->getConfig()->get("insufficient-rankup"))));
            return false;
        }

        $prestige = $session->getPrestige() + 1;

        Await::f2c(
            function () use($sender, $session, $prestige) : Generator {
                try {
                    yield from BedrockEconomyAPI::ASYNC()->subtract($sender->getXuid(), $sender->getName(), Utils::getRankupPrice($session->getRank()) * $prestige * BetterPrisons::getBetterPrisons()->getConfig()->get("prestige-multiplier"), 1);
                } catch (RecordNotFoundException) {
                    BetterPrisons::getBetterPrisons()->getLogger()->alert(LanguageManager::getString(KnownMessages::ERROR_ACCOUNT_NONEXISTENT));
                } catch(SQLException $exception) {
                    BetterPrisons::getBetterPrisons()->getLogger()->alert(LanguageManager::getString(KnownMessages::ERROR_DATABASE));
                    BetterPrisons::getBetterPrisons()->getLogger()->logException($exception);
                }
            }
        );

        $newRank = $session->getRank();
        $newRank++;

        $sender->sendMessage(str_replace(["{PRICE}", "{RANK}"], [Utils::getRankupPrice($newRank) * $prestige * BetterPrisons::getBetterPrisons()->getConfig()->get("prestige-multiplier"), $newRank], BetterPrisons::getBetterPrisons()->getConfig()->get("ranked-up")));

        foreach (Utils::getRankupCommands($session->getRank()) as $command) {
            BetterPrisons::getBetterPrisons()->getServer()->dispatchCommand(new ConsoleCommandSender(BetterPrisons::getBetterPrisons()->getServer(), BetterPrisons::getBetterPrisons()->getServer()->getLanguage()), str_replace("{PLAYER}", $sender->getName(), $command));
        }

        if(BetterPrisons::getBetterPrisons()->getServer()->getPluginManager()->getPlugin("RankSystem") !== null) {
            foreach (Utils::getRankupAddPermissions($session->getRank()) as $addPermission) {
                if(!RankSystem::getInstance()->getSessionManager()->get($sender)->hasPermission($addPermission)) RankSystem::getInstance()->getSessionManager()->get($sender)->setPermission($addPermission);
            }

            foreach (Utils::getRankupRemovedPermissions($session->getRank()) as $removedPermission) {
                if(RankSystem::getInstance()->getSessionManager()->get($session)->hasPermission($removedPermission)) RankSystem::getInstance()->getSessionManager()->get($sender)->removePermission($removedPermission);
            }
        }

        $session->setRank($newRank);

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