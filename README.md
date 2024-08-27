# BetterPrisons

Advanced prisons plugin for Pocketmine-MP..

## Features
 - Ranking up and prestiging system.
 - Customisable command executing and permission giving system per rank up/prestige.
 - Optional rank system and scorehud addons.
 - Compatibility with bedrock economy.
 - Customisable messages and multipliers.
 - Optional requirement for players to mine a specified number of blocks before prestiging.

## Installation
 1. Download plugin phar from [here](https://poggit.pmmp.io/ci/snarerectify/BetterPrisons/~)
 2. Add to your servers' plugin folder.
 3. Restart server.

## Commands
| Command   | Description      | Permission                     |                                                             
|-----------|------------------|--------------------------------|
| /rankup   | Rankup command.  | betterprisons.rankup.command   | 
| /prestige | Prestige Command | betterprisons.prestige.command |         

## ScoreHud Addon
Use the following in your scorehud.yml ([ScoreHud](https://poggit.pmmp.io/p/ScoreHud) must be downloaded for this feature).
| Tag                               | Description                                                                |
|-----------------------------------|----------------------------------------------------------------------------|
| {scorehudx.prisonrank}            | Displays the players current prison rank.                                  |
| {scorehudx.prisonprestige}        | Displays the players current prestige.                                     |
| {scorehudx.prisonrequiredblocks}  | Displays the number of blocks players need to mine to be able to prestige. |
| {scorehudx.prisonrequiredrank}    | Displays the amount of money the player requires to rank up.               |
| {scorehudx.prisonrequiredprestige}| Displays the amount of money the player requires to prestige.              |

## RankSystem Addon
Use the following in your config.yml's nametag and chat format ([RankSystem](https://poggit.pmmp.io/p/RankSystem) must be downloaded for this feature).
| Prefix           | Description                              |
|------------------|------------------------------------------|
| {prison_rank}    | Displays the players current prison rank.|
| {prison_prestige}| Displays the players current prestige.   |

## API
To obtain an instance of this plugin's main class:
```php
use snare\BetterPrisons\BetterPrisons;

$instance = BetterPrisons::getBetterPrisons();
```

Various methods can be found below:
```php
$instance->getBragItems(); // returns an array with player name as key, Item instance as value.

$instance->isBragging(Player $player); // returns true or false depending on whether or not the specified player is bragging.

$instance->startBragging(Player $player); // causes the specified player to brag about the item in their hand.

$instance->stopBragging(Player $player); // returns true if able to stop playing bragging, false if not.

$instance->getBraggingItem(Player $player); // returns Item instance if player is bragging, null if not.
```

             
