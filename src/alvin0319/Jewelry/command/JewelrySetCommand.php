<?php
declare(strict_types=1);

namespace alvin0319\Jewelry\command;

use alvin0319\Jewelry\Jewelry;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use function in_array;
use function is_numeric;
use function trim;

class JewelrySetCommand extends Command{

	public function __construct(){
		parent::__construct("보석설정");
		$this->setDescription("플레이어가 가지고 있는 보석을 설정합니다.");
		$this->setPermission("jewelry.command.jewelryset");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(trim($args[0] ?? "") === ""){
			OnixUtils::message($sender, "사용법: /보석설정 [닉네임] [보석] [양] - 플레이어의 보석을 설정합니다.");
			return false;
		}
		if(!Jewelry::getInstance()->hasData($args[0])){
			OnixUtils::message($sender, "해당 플레이어는 이 서버에 접속한 적이 없습니다.");
			return false;
		}
		if(trim($args[1] ?? "") === ""){
			OnixUtils::message($sender, "보석의 이름을 입력해주세요.");
			return false;
		}
		if(!in_array($args[1], Jewelry::JEWELRY_TYPES)){
			OnixUtils::message($sender, "해당 이름의 보석은 존재하지 않습니다.");
			return false;
		}
		if(trim($args[2] ?? "") === ""){
			OnixUtils::message($sender, "설정할 보석의 양을 입력해주세요.");
			return false;
		}
		if(!is_numeric($args[2]) || ($count = (int) $args[2]) < 0){
			OnixUtils::message($sender, "설정할 보석의 양은 0보다 크거나 같아야합니다.");
			return false;
		}
		Jewelry::getInstance()->setJewelry($args[0], $args[1], $count);
		return true;
	}
}