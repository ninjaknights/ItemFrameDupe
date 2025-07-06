<?php
declare(strict_types=1);

namespace NinjaKnights\ItemFrameDupe;

class CooldownManager{

	private array $cooldowns = [];

	public function isOnCooldown(string $name, string $itemName): int{
		$expiry = $this->cooldowns[$name][$itemName] ?? 0;
		$remaining = $expiry - time();
		if($remaining > 0){
			return $remaining;
		}
		$this->clearCooldown($name, $itemName);
		return 0;
	}

	public function setCooldown(string $name, string $itemName, int $seconds): void{
		$this->cooldowns[$name][$itemName] = time() + $seconds;
	}

	public function clearCooldown(string $name, string $itemName): void{
		if(isset($this->cooldowns[$name][$itemName])){
			unset($this->cooldowns[$name][$itemName]);
		}
	}

	public function clearAllCooldowns(string $name): void{
		if(isset($this->cooldowns[$name])){
			unset($this->cooldowns[$name]);
		}
	}

	public function getCooldowns(string $name): array{
		return $this->cooldowns[$name] ?? [];
	}
}