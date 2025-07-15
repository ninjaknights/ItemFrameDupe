<?php
declare(strict_types=1);

namespace NinjaKnights\ItemFrameDupe;

class CooldownManager{

	/** @var array<string, array<string, int>> */
	private array $cooldowns = [];

	/**
	 * Checks if a given name-item pair is currently on cooldown.
	 * @param string $name The name of the entity/user.
	 * @param string $itemName The name of the item/action.
	 * @return int Seconds remaining on cooldown. Returns 0 if not on cooldown.
	 */
	public function isOnCooldown(string $name, string $itemName): int{
		$expiry = $this->cooldowns[$name][$itemName] ?? 0;
		$remaining = $expiry - time();
		if($remaining > 0){
			return $remaining;
		}
		$this->clearCooldown($name, $itemName);
		return 0;
	}

	/**
	 * Sets a cooldown for a specific name-item pair.
	 * @param string $name The name of the entity/user.
	 * @param string $itemName The name of the item/action.
	 * @param int $seconds Cooldown duration in seconds.
	 * @return void
	 */
	public function setCooldown(string $name, string $itemName, int $seconds): void{
		$this->cooldowns[$name][$itemName] = time() + $seconds;
	}

	/**
	 * Clears the cooldown for a specific name-item pair.
	 * @param string $name The name of the entity/user.
	 * @param string $itemName The name of the item/action.
	 * @return void
	 */
	public function clearCooldown(string $name, string $itemName): void{
		if(isset($this->cooldowns[$name][$itemName])){
			unset($this->cooldowns[$name][$itemName]);
		}
	}

	/**
	 * Clears all cooldowns associated with a specific name.
	 * @param string $name The name of the entity/user.
	 * @return void
	 */
	public function clearAllCooldowns(string $name): void{
		if(isset($this->cooldowns[$name])){
			unset($this->cooldowns[$name]);
		}
	}

	/**
	 * Retrieves all active cooldowns for a given name.
	 * @param string $name The name of the entity/user.
	 * @return array<string, int> An associative array of item names to expiry timestamps.
	 */
	public function getCooldowns(string $name): array{
		return $this->cooldowns[$name] ?? [];
	}
}