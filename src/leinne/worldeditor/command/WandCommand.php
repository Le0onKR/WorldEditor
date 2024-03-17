<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

class WandCommand extends Command
{
    public function __construct()
    {
        parent::__construct("/wand", "월드에딧 도구를 지급합니다.");
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $item = VanillaItems::WOODEN_AXE();

            if ($sender->getInventory()->canAddItem($item)) {
                $sender->getInventory()->addItem($item);
            } else {
                $sender->sendMessage(WorldEditor::$prefix . "인벤토리에 공간이 부족합니다.");
            }
        }
    }
}