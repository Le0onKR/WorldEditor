<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\RedoBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RedoCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/redo", "선택한 영역을 변경했던 상태로 되돌립니다.");
        $this->setPermission("worldeditor.command.redoblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (!$selected->isValid()) {
                $sender->sendMessage(WorldEditor::$prefix . "영역을 선택해주세요.");
                return;
            }
            $sender->sendMessage(WorldEditor::$prefix . "변경했던 블럭으로 다시 되돌리기를 시작했습니다.");
            $this->plugin->getScheduler()->scheduleTask(new RedoBlockTask(
                $selected->getMinPosition(),
                $selected->getMaxPosition(),
                $selected->getWorld(),
                $sender
            ));
        }
    }
}