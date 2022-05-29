<?php

declare(strict_types=1);

namespace alvin0319\Jewelry\command;

use alvin0319\Jewelry\form\JewelryMainForm;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class JewelryCommand extends Command{

	public function __construct(){
		parent::__construct("보석");
		$this->setDescription("보석 UI를 엽니다.");
		$this->setPermission("jewelry.command.openui");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			OnixUtils::message($sender, "인 게임에서만 이용할 수 있습니다.");
			return false;
		}
		$sender->sendForm(new JewelryMainForm($sender));
		return true;
	}
}