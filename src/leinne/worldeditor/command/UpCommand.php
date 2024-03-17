<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\WorldEditor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\world\World;

class UpCommand extends Command
{
    public function __construct()
    {
        parent::__construct("/up", "일정 y좌표 위로 올라가능 명령어 입니다.");
        $this->setUsage("/{$this->getName()} [count:int]");
        $this->setPermission(DefaultPermissions::ROOT_OPERATOR);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            if (!isset($args[0]) or !is_numeric($args[0]) or $args[0] >= World::Y_MAX) {
                $sender->sendMessage(WorldEditor::$prefix . $this->getUsage());
                return;
            }
            $pos = $sender->getPosition();
            $sender->teleport(new Vector3($pos->x, $pos->y + $args[0], $pos->z));
            $sender->getWorld()->setBlock(new Vector3($pos->x, $pos->y + $args[0] - 1, $pos->z), VanillaBlocks::GLASS());
            $sender->sendMessage(WorldEditor::$prefix . "§a{$args[0]}블럭§7위로 이동했습니다.");
        }
    }
}