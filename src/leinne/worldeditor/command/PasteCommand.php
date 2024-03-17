<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\PasteBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class PasteCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/paste", "복사했던 블럭들을 붙여넣습니다.");
        $this->setPermission("worldeditor.command.cutblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if ($selected->getWorld() === null || $selected->getMaxPosition()->y < 0) {
                $sender->sendMessage(WorldEditor::$prefix . "영역을 선택해주세요.");
                return;
            }
            $pos = $selected->getFirstPosition();

            if ($pos->y < 0) {
                $pos = $selected->getSecondPosition();
            }
            $sender->sendMessage(WorldEditor::$prefix . "블럭 붙여넣기를 시작했습니다.");
            $this->plugin->getScheduler()->scheduleTask(new PasteBlockTask($pos, $selected->getWorld(), $this->plugin->getCopy($sender), $sender->getHorizontalFacing(), $sender));
        }
    }
}