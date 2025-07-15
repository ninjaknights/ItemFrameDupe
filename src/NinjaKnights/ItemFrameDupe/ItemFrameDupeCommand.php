<?php
declare(strict_types=1);

namespace NinjaKnights\ItemFrameDupe;

use NinjaKnights\ItemFrameDupe\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ItemFrameDupeCommand extends Command{

	public function __construct(){
		parent::__construct("iframedupe", "manage item frame duplication settings", "/iframedupe", ["ifd", "fd"]);
		$this->setPermission("iframedupe.admin");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
		if(!$sender instanceof Player){
			$sender->sendMessage(Main::PREFIX."§mThis command can only be used in-game.");
			return false;
		}
		if(!$sender->hasPermission("iframedupe.admin")){
			$sender->sendMessage(Main::PREFIX . "§mYou don't have permission to use this command.");
			return false;
		}
		$sub = strtolower($args[0] ?? "help");
		switch($sub){
			case "enable":
			case "disable":
				Main::getData()->set("enabled", ($sub === "enable"));
				Main::getData()->save();
				Main::getData()->reload();
				$sender->sendMessage(Main::PREFIX . "Plugin is now " . ($sub === "enable" ? "§aenabled" : "§cdisabled") . ".");
				break;
			case "cooldown-set":
				if(count($args) < 2){
					$sender->sendMessage(Main::PREFIX . "§cUsage: /iframedupe cooldown-set <seconds>");
					break;
				}
				$seconds = (int) $args[1];
				if($seconds <= 0){
					$sender->sendMessage(Main::PREFIX . "§cCooldown must be a positive integer.");
					break;
				}
				Main::getData()->set("default-cooldown", $seconds);
				Main::getData()->save();
				Main::getData()->reload();
				$sender->sendMessage(Main::PREFIX . "Global Dupe Cooldown is set to {$seconds} seconds.");
				break;
			case "cooldown-remove":
				if(count($args) < 3){
					$sender->sendMessage(Main::PREFIX . "§cUsage: /iframedupe cooldown-remove <item> <player>");
					break;
				}
				Main::getCooldownManager()?->clearCooldown(strtolower($args[2]), strtolower($args[1]));
				$sender->sendMessage(Main::PREFIX . "Cooldown for '{$args[1]}' removed for '{$args[2]}'.");
				break;
			case "cooldown-clear":
				if(count($args) < 2){
					$sender->sendMessage(Main::PREFIX . "§cUsage: /iframedupe cooldown-clear <player>");
					break;
				}
				Main::getCooldownManager()?->clearAllCooldowns(strtolower($args[1]));
				$sender->sendMessage(Main::PREFIX . "All cooldowns for '{$args[1]}' cleared.");
				break;
			case "cooldown-list":
				if(count($args) < 2){
					$sender->sendMessage(Main::PREFIX . "§cUsage: /iframedupe cooldown-list <player>");
					break;
				}
				$cooldowns = Main::getCooldownManager()?->getCooldowns(strtolower($args[1]));
				if(empty($cooldowns)){
					$sender->sendMessage(Main::PREFIX . "No cooldowns found for '{$args[1]}'.");
				}else{
					$sender->sendMessage(Main::PREFIX . "Cooldowns for '{$args[1]}':");
					foreach($cooldowns as $item => $time){
						$remaining = max(0, $time - time());
						$sender->sendMessage(" §7- §n{$item}§r: §u{$remaining}§r§ss");
					}
				}
				break;
			case "config":
				if(count($args) < 3){
					$sender->sendMessage(Main::PREFIX . "§cUsage: /iframedupe config <option> <true|false>");
					break;
				}
				$option = strtolower($args[1]);
				$value = strtolower($args[2]);
				if(!in_array($option, ["need-permission", "log-dupes", "dupe-shulker", "allow-enchanted"])){
					$sender->sendMessage(Main::PREFIX . "§cInvalid option '{$option}'. Allowed: need-permission, log-dupes, dupe-shulker, allow-enchanted");
					break;
				}
				if(!in_array($value, ["true", "false"])){
					$sender->sendMessage(Main::PREFIX . "§cInvalid value '{$value}'. Use 'true' or 'false'.");
					break;
				}
				$boolValue = $value === "true";
				Main::getData()->set($option, $boolValue);
				Main::getData()->save();
				Main::getData()->reload();
				$sender->sendMessage(Main::PREFIX . "Config option '{$option}' set to " . ($boolValue ? "enabled" : "disabled") . ".");
				break;
			case "chance-set":
				if (count($args) < 3) {
					$sender->sendMessage(Main::PREFIX . "§cUsage: /iframedupe chance-set <rotation (0-7)> <chance (0-100)>");
					break;
				}
				$rotation = (int) $args[1];
				$chance = (int) $args[2];
				if ($rotation < 0 || $rotation > 7 || $chance < 0 || $chance > 100) {
					$sender->sendMessage(Main::PREFIX . "§cInvalid rotation or chance value.");
					break;
				}
				Main::getData()->setNested("chances.rotation_" . $rotation, $chance);
				Main::getData()->save();
				$sender->sendMessage(Main::PREFIX . "Set rotation_{$rotation} chance to §e{$chance}§f%§r");
				break;
			case "help":
			default:
				$this->sendUsage($sender);
				break;
		}
		return true;
	}

	public function sendUsage(CommandSender $sender): void{
		$sender->sendMessage(
			'§8-=[§a+§8]§b=-§bItemFrameDupe Commands§b-=§8[§a+§8]=-' . "\n" .
			'§f/§nifd §uenable§7 - §vEnables the plugin' . "\n" .
			'§f/§nifd §udisable§7 - §vDisables the plugin' .
			"\n" .
			'§f/§nifd §ucooldown-set <seconds>§7 - §vSets global items dupe cooldown' . "\n" .
			'§f/§nifd §ucooldown-remove <item_name> <player>§7 - §vRemoves cooldown for specific item and player' . "\n" .
			'§f/§nifd §ucooldown-clear <player>§7 - §vClears all cooldowns for a player' . "\n" .
			'§f/§nifd §ucooldown-list <player>§7 - §vLists all cooldowns for a player' . "\n" .
			'§f/§nifd §uconfig <option> <true|false>§7 - §vConfigures plugin options' . "\n" .
			'§f/§nifd §uchance-set <rotation (0-7)> <chance (0-100)>§7 - §vSets chance for item frame rotation' . "\n" .
			'§f/§nifd §uhelp§7 - §vShows this help message' . "\n" .
			'§8-=[§a+§8]=- §8[ §3By §dHydro§6Games§8 ] -=[§a+§8]=-'
		);
	}
}