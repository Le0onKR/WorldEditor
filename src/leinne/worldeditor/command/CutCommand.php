<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\CutBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class CutCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/cut", "선택한 영역을 잘라냅니다.");
        $this->setPermission("worldeditor.command.cutblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (!$selected->isValid()) {
                $sender->sendMessage(WorldEditor::$prefix . "영역을 설정해주세요.");
                return;
            }
            $this->plugin->resetCopy($sender);
            $sender->sendMessage(WorldEditor::$prefix . "블럭 잘라내기를 시작했습니다.");
            $this->plugin->getScheduler()->scheduleTask(new CutBlockTask(
                $sender,
                $selected->getMinPosition(),
                $selected->getMaxPosition(),
                $selected->getWorld()
            ));
        }
    }
}