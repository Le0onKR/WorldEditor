<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\CopyBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class CopyCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/copy", "선택한 영역을 복사합니다.");
        $this->setPermission("worldeditor.command.copyblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (!$selected->isValid()) {
                $sender->sendMessage(WorldEditor::$prefix . "영역을 선택해주세요.");
                return;
            }
            $this->plugin->resetCopy($sender);
            $sender->sendMessage(WorldEditor::$prefix . "블럭 복사를 시작했습니다.");
            $this->plugin->getScheduler()->scheduleTask(new CopyBlockTask(
                $sender,
                $selected->getMinPosition(),
                $selected->getMaxPosition(),
                $selected->getWorld()
            ));
        }
    }
}