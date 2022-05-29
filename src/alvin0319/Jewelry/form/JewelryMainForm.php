<?php

declare(strict_types=1);

namespace alvin0319\Jewelry\form;

use alvin0319\Jewelry\Jewelry;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function is_int;

class JewelryMainForm implements Form{
	/** @var Player */
	protected Player $player;

	public function __construct(Player $player){
		$this->player = $player;
	}

	public function jsonSerialize() : array{
		$common = Jewelry::getInstance()->getJewelry($this->player, Jewelry::JEWELRY_TYPE_COMMON);
		$rare = Jewelry::getInstance()->getJewelry($this->player, Jewelry::JEWELRY_TYPE_RARE);
		$uncommon = Jewelry::getInstance()->getJewelry($this->player, Jewelry::JEWELRY_TYPE_UNCOMMON);
		$legend = Jewelry::getInstance()->getJewelry($this->player, Jewelry::JEWELRY_TYPE_LEGEND);
		return [
			"type" => "form",
			"title" => "§lJewelry - Master",
			"content" => "§l§f현재 보유중인 §d일반 §f보석: §d{$common}§f개\n현재 보유중인 §d희귀 §f보석: §d{$rare}§f개\n현재 보유중인 §d보통 §f보석: §d{$uncommon}§f개\n현재 보유중인 §d전설 §f보석: §d{$legend}§f개",
			"buttons" => [
				["text" => "§l* 세션 종료하기"],
				["text" => "§l* 보석 조합하기"]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_int($data)){
			return;
		}

		switch($data){
			case 1:
				$player->sendForm(new JewelryMixForm());
				//OnixUtils::message($player, "현재 보석 조합은 오류가 있어 사용하실 수 없습니다.");
				break;
		}
	}
}