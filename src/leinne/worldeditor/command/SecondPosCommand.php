<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SecondPosCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/pos2", "두번째 영역을 설정합니다");
        $this->setPermission("worldeditor.command.setpos");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $pos = $sender->getPosition();
            $item = $sender->getInventory()->getItemInHand();
            $sender->sendMessage($this->plugin->getSelectedArea($sender)->setSecondPosition($pos, $item));
        }
    }
}