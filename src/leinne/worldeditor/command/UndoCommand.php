<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\UndoBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class UndoCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/undo", "선택한 영역을 변경하기 전으로 되돌립니다.");
        $this->setPermission("worldeditor.command.undoblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (!$selected->isValid()) {
                $sender->sendMessage(WorldEditor::$prefix . "먼저 영역을 설정해야 합니다");
                return;
            }
            $sender->sendMessage(WorldEditor::$prefix . "블럭을 변경하기 이전으로 되돌리기를 시작했습니다");
            $this->plugin->getScheduler()->scheduleTask(new UndoBlockTask(
                $selected->getMinPosition(),
                $selected->getMaxPosition(),
                $selected->getWorld(),
                $sender
            ));
        }
    }
}