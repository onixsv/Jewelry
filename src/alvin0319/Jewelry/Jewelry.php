<?php
declare(strict_types=1);

namespace alvin0319\Jewelry;

use alvin0319\Jewelry\command\JewelryCommand;
use alvin0319\Jewelry\command\JewelrySetCommand;
use Content\event\ContentClearEvent;
use InvalidArgumentException;
use OnixUtils\OnixUtils;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Quest\event\QuestClearEvent;
use function array_rand;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function in_array;
use function json_decode;
use function json_encode;
use function mt_rand;
use function shuffle;
use function strtolower;

class Jewelry extends PluginBase implements Listener{
	use SingletonTrait;

	public const JEWELRY_TYPE_COMMON = "일반";

	public const JEWELRY_TYPE_RARE = "희귀";

	public const JEWELRY_TYPE_UNCOMMON = "보통";

	public const JEWELRY_TYPE_LEGEND = "전설";

	public const JEWELRY_TYPES = [
		self::JEWELRY_TYPE_COMMON,
		self::JEWELRY_TYPE_RARE,
		self::JEWELRY_TYPE_UNCOMMON,
		self::JEWELRY_TYPE_LEGEND
	];

	public const JEWELRY_MIX_SUCCESS_PERCENT = [
		self::JEWELRY_TYPE_RARE => 70,
		self::JEWELRY_TYPE_UNCOMMON => 50,
		self::JEWELRY_TYPE_LEGEND => 8
	];

	protected array $db = [];

	public static function convert($player) : string{
		return $player instanceof Player ? strtolower($player->getName()) : strtolower($player);
	}

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		if(file_exists($file = $this->getDataFolder() . "Jewelry.json")){
			$this->db = json_decode(file_get_contents($file), true);
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getServer()->getCommandMap()->registerAll("jewelry", [
			new JewelrySetCommand(),
			new JewelryCommand()
		]);
	}

	protected function onDisable() : void{
		file_put_contents($this->getDataFolder() . "Jewelry.json", json_encode($this->db));
	}

	public function addJewelry($player, string $jewelryType, int $amount) : void{
		if(!in_array($jewelryType, self::JEWELRY_TYPES)){
			throw new \RuntimeException("Unknown jewelry type $jewelryType");
		}
		if(!$this->hasData($player)){
			$this->createData($player);
		}

		$this->db[self::convert($player)][$jewelryType] += $amount;
	}

	public function reduceJewelry($player, string $jewelryType, int $amount) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		if($this->getJewelry($player, $jewelryType) - $amount >= 0){
			$this->db[self::convert($player)][$jewelryType] -= $amount;
		}
	}

	public function getJewelry($player, string $jewelryType) : int{
		return $this->db[self::convert($player)][$jewelryType] ?? -1;
	}

	public function setJewelry($player, string $jewelryType, int $amount) : void{
		if(!$this->hasData($player)){
			$this->createData($player);
		}
		$this->db[self::convert($player)][$jewelryType] = $amount;
	}

	public function hasData($player) : bool{
		return isset($this->db[self::convert($player)]);
	}

	public function createData($player) : void{
		if($this->hasData($player)){
			return;
		}
		$this->db[self::convert($player)] = [];
		foreach(self::JEWELRY_TYPES as $type){
			$this->db[self::convert($player)][$type] = 0;
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		if(!$this->hasData($player)){
			$this->createData($player);
		}
	}

	public function getRandomJewelry() : string{
		$res = [];
		for($i = 0; $i < 7; $i++)
			$res[] = self::JEWELRY_TYPE_COMMON;
		for($i = 0; $i < 5; $i++)
			$res[] = self::JEWELRY_TYPE_RARE;
		for($i = 0; $i < 2; $i++)
			$res[] = self::JEWELRY_TYPE_UNCOMMON;
		$res[] = self::JEWELRY_TYPE_LEGEND;
		shuffle($res);
		return $res[array_rand($res)];
	}

	public function getRandomJewelryNon() : string{
		$res = [];
		for($i = 0; $i < 7; $i++)
			$res[] = self::JEWELRY_TYPE_COMMON;
		for($i = 0; $i < 5; $i++)
			$res[] = self::JEWELRY_TYPE_RARE;
		shuffle($res);
		return $res[array_rand($res)];
	}

	public function onQuestClear(QuestClearEvent $event) : void{
		$this->addJewelry($event->getPlayer(), $this->getRandomJewelryNon(), 1);
	}

	public function onContentClear(ContentClearEvent $event) : void{
		$this->addJewelry($event->getPlayer(), $this->getRandomJewelryNon(), 1);
	}

	public function onPlayerDeath(PlayerDeathEvent $event) : void{
		$player = $event->getPlayer();
		$lastDamageCause = $player->getLastDamageCause();
		if($lastDamageCause instanceof EntityDamageByEntityEvent){
			$damager = $lastDamageCause->getDamager();
			if($damager instanceof Player){
				$this->addJewelry($damager, $this->getRandomJewelryNon(), 1);
			}
		}
	}

	public function tryMixJewelry(Player $player, string $jewelry, int $count) : void{
		$percent = self::JEWELRY_MIX_SUCCESS_PERCENT[$jewelry];

		$success = 0;

		$fail = 0;

		$bonus = 0;

		for($i = 0; $i < $count; $i++){
			if(mt_rand(0, 100) <= $percent){
				$success += 1;
				if(mt_rand(0, 10) === 5){
					$bonus += 1;
				}
			}else{
				$fail += 1;
			}
		}

		$total = $success + $bonus;

		//$this->reduceJewelry($player, $this->getPreviousJewelry($jewelry), $count);

		$this->addJewelry($player, $jewelry, $total);

		OnixUtils::message($player, "총 §d{$count}§f개를 조합하여 §d{$total}§f개를 성공했고 §d{$fail}§f개를 실패했습니다. (보너스 §d{$bonus}§f개)");
	}

	private function getPreviousJewelry(string $jewelry) : string{
		switch($jewelry){
			case self::JEWELRY_TYPE_RARE:
				return self::JEWELRY_TYPE_COMMON;
			case self::JEWELRY_TYPE_UNCOMMON:
				return self::JEWELRY_TYPE_RARE;
			case self::JEWELRY_TYPE_LEGEND:
				return self::JEWELRY_TYPE_UNCOMMON;
			default:
				throw new InvalidArgumentException("Unknwon jewelry type $jewelry");
		}
	}
}