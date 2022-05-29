<?php
declare(strict_types=1);

namespace alvin0319\Jewelry\form;

use alvin0319\Jewelry\Jewelry;
use OnixUtils\OnixUtils;
use pocketmine\form\Form;
use pocketmine\player\Player;
use function count;
use function is_array;
use function is_numeric;

class JewelryMixForm implements Form{
	/** @var Player */
	protected Player $player;

	public function jsonSerialize() : array{
		return [
			"type" => "custom_form",
			"title" => "JewelrySystem - Master",
			"content" => [
				[
					"type" => "dropdown",
					"text" => "§l§d조합§f할 보석을 §d선택§f해주세요.",
					"options" => ["일반 -> 희귀", "희귀 -> 보통", "보통 -> 레전드"]
				],
				[
					"type" => "input",
					"text" => "조합할 보석의 양을 입력해주세요."
				]
			]
		];
	}

	public function handleResponse(Player $player, $data) : void{
		if(!is_array($data) || count($data) !== 2){
			return;
		}
		$options = [
			0 => Jewelry::JEWELRY_TYPE_RARE,
			1 => Jewelry::JEWELRY_TYPE_UNCOMMON,
			2 => Jewelry::JEWELRY_TYPE_LEGEND
		];

		$options2main = [
			0 => Jewelry::JEWELRY_TYPE_COMMON,
			1 => Jewelry::JEWELRY_TYPE_RARE,
			2 => Jewelry::JEWELRY_TYPE_UNCOMMON
		];

		$count = $data[1] ?? 0;

		if(!is_numeric($count) || ($count = (int) $count) < 1){
			OnixUtils::message($player, "개수는 0개 이상이어야 합니다.");
			return;
		}

		if($count > 100){
			OnixUtils::message($player, "보석 조합은 한 번에 100개 까지 가능합니다.");
			return;
		}

		if(Jewelry::getInstance()->getJewelry($player, $options2main[$data[0]]) < $count){
			OnixUtils::message($player, "보석 조합에 필요한 보석이 부족합니다.");
			return;
		}

		Jewelry::getInstance()->tryMixJewelry($player, $options[$data[0]], $count);
		Jewelry::getInstance()->reduceJewelry($player, $options2main[$data[0]], $count);
	}
}