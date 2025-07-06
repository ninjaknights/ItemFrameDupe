<?php
declare(strict_types=1);

namespace NinjaKnights\ItemFrameDupe;

use NinjaKnights\ItemFrameDupe\CooldownManager;
use NinjaKnights\ItemFrameDupe\ItemFrameDupeCommand;
use pocketmine\block\ItemFrame;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\sound\ItemFrameRemoveItemSound;
use pocketmine\block\VanillaBlocks;

class Main extends PluginBase implements Listener{
	use SingletonTrait {
		setInstance as private;
		reset as private;
	}

	/**
	 * The prefix used for messages from this plugin.
	 * @var string
	 */
	public const PREFIX = "§8[§3ItemFrameDupe§8] §v> §r";

	/** @var Config|null */
	private ?Config $config = null;
	/** @var CooldownManager|null */
	private ?CooldownManager $cooldownManager = null;

	/** @var string[] */
	private array $whitelist = [];
	/** @var string[] */
	private array $blacklist = [];

	public function onLoad(): void{
		self::setInstance($this);
	}

	public function onEnable(): void{
		$this->loadFiles();
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->cooldownManager = new CooldownManager();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getCommandMap()->register("itemframedupe", new ItemFrameDupeCommand());
	}

	/**
	 * Gets the instance of the Config.
	 * @return Config|null
	*/
	public static function getData(): ?Config{
		return self::getInstance()->config ?? null;
	}

	/**
	 * Gets the instance of the CooldownManager.
	 * @return CooldownManager|null
	*/
	public static function getCooldownManager(): CooldownManager{
		return self::getInstance()->cooldownManager ?? null;
	}

	/**
	 * Loads the necessary files for the plugin.
	*/
	private function loadFiles(): void{
		$this->saveResource("config.yml", false);
		$this->saveResource("whitelist.txt");
		$this->saveResource("blacklist.txt");
		$this->saveResource("dupe.log");
		$whitelistPath = $this->getDataFolder() . "whitelist.txt";
		$blacklistPath = $this->getDataFolder() . "blacklist.txt";
		$this->whitelist = array_map('strtolower', array_filter(array_map('trim', explode("\n", file_get_contents($whitelistPath)))));
		$this->blacklist = array_map('strtolower', array_filter(array_map('trim', explode("\n", file_get_contents($blacklistPath)))));
	}

	/**
	 * Gets the chance of duplication based on the item frame's rotation.
	 * @param int $rotation
	 * @return int Chance percentage (0-100)
	*/
	private function getChanceByRotation(int $rotation): int{
		return (int) ($this->config->getNested("chances.rotation_" . $rotation) ?? 0);
	}

	/**
	 * Gets the cooldown for a specific item name.
	 * @param string $itemName
	 * @return int Cooldown in seconds
	*/
	private function getCooldownForItemName(string $itemName): int{
		$itemCooldowns = $this->config->get("item-cooldowns", []);
		return (int) ($itemCooldowns[strtolower($itemName)] ?? $this->config->get("default-cooldown"));
	}

	/**
	 * Logs the duplication event to a file.
	 * @param string $playerName
	 * @param string $worldName
	 * @param string $itemName
	 * @param int $rotation
	 * @param int $chance
	*/
	private function logDupe(string $playerName, string $worldName, string $itemName, int $rotation, int $chance): void{
		$line = sprintf(
			"[%s] Player: %s, World: %s, Item: %s, Rotation: %d, Chance: %d%%\n",
			date("Y-m-d H:i:s"),
			$playerName,
			$worldName,
			$itemName,
			$rotation,
			$chance
		);
		file_put_contents($this->getDataFolder() . "dupe.log", $line, FILE_APPEND);
	}

	/**
	 * Normalizes the item name to a consistent format.
	 * @param Item $item
	 * @return string
	 */
	public function getNormalizedName(Item $item): string{
		$normalizedItemId = strtolower($item->getVanillaName());
		$normalizedItemId = str_replace([" ", "-", "’", "'", ":"], "_", $normalizedItemId);
		return preg_replace('/[^a-z0-9_]/', '', $normalizedItemId);
	}

	/**
	 * Handles player interactions with item frames.
	 * @param PlayerInteractEvent $event
	 * @return void
	 */
	public function onPlayerInteract(PlayerInteractEvent $event): void{
		if(!$this->config->get("enabled", true)) return;
		$player = $event->getPlayer();
		$name = strtolower($player->getName());
		$block = $event->getBlock();
		if(
			$event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK &&
			$player->isSneaking() &&
			$block instanceof ItemFrame
		){
			if($this->config->get("need-permission", true) && !$player->hasPermission("iframedupe.use")){
				$player->sendActionBarMessage(self::PREFIX."§mYou don't have permission to use this feature.");
				return;
			}
			$item = $block->getFramedItem(); // Not using ItemFrame tile
			if($item === null || !$item instanceof Item || $item->isNull()){
				return;
			}
			
			$itemName = $this->getNormalizedName($item);
			if(in_array($itemName, $this->blacklist)){
				$player->sendActionBarMessage(self::PREFIX."§mThis item is not allowed for duping.");
				return;
			}
			if(!in_array("*", $this->whitelist) && !in_array($itemName, $this->whitelist)){
				return;
			}
			if(!$this->config->get("dupe-shulker", true)){
				if(
					$itemName === "shulker_box" ||
					$itemName === "minecraft:shulker_box" ||
					$itemName === VanillaBlocks::SHULKER_BOX()->getName()
				){
					$player->sendActionBarMessage(self::PREFIX."§mShulker Box duplication is disabled.");
					return;
				}
			}
			if(!$this->config->get("allow-enchanted", true) && $item->hasEnchantments()){
				$player->sendActionBarMessage(self::PREFIX."§mEnchanted Item duplication is disabled.");
				return;
			}
			if(!$player->hasPermission("iframedupe.bypass.cooldown") || !$player->hasPermission("iframedupe.admin")){
				$remaining = $this->cooldownManager->isOnCooldown($name, $itemName);
				if($remaining > 0){
					$remaining = ceil($remaining);
					$player->sendActionBarMessage(self::PREFIX."§mYou must wait §7{$remaining}§m seconds before duping on §v{$itemName}§m again.");
					return;
				}else{
					$this->cooldownManager->clearCooldown($name, $itemName);
				}
			}
			$chance = $this->getChanceByRotation($block->getItemRotation());
			if($chance <= 0){
				return;
			}
			if(mt_rand(1, 100) <= $chance){
				$world = $player->getWorld();
				$world->dropItem($block->getPosition()->add(0.5, 0.5, 0.5), clone $item->setCount(1));
				$world->addSound($block->getPosition(), new ItemFrameRemoveItemSound());
				$this->cooldownManager->setCooldown($name, $itemName, $this->getCooldownForItemName($itemName));
				if($this->config->get("log-dupes", true)){
					$this->logDupe($name, $world->getFolderName(), $itemName, $block->getItemRotation(), $chance);
				}
			}
		}
	}
}