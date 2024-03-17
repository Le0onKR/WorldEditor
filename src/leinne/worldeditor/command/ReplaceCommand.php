<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\ReplaceBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ReplaceCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/replace", "선택한 영역의 일부 블럭을 원하는 블럭으로 교체합니다.");
        $this->setUsage("/{$this->getName()} [선택할 블럭] [바꿀 블럭] [meta체크 (true/false)]");
        $this->setPermission("worldeditor.command.replaceblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (count($args) < 2) {
                $sender->sendMessage(WorldEditor::$prefix . $this->getUsage());
                return;
            }

            if (!$selected->isValid()) {
                $sender->sendMessage(WorldEditor::$prefix . "영역을 선택해주세요.");
                return;
            }
            $source = $this->plugin->getStringToBlock($args[0]);
            $target = $this->plugin->getStringToBlock($args[1]);

            if ($source === null || $target === null) {
                $sender->sendMessage(WorldEditor::$prefix . "존재하지 않는 블럭입니다. [blockId : $args[0]]");
            } else {
                $sender->sendMessage(WorldEditor::$prefix . "블럭 변경이 시작되었습니다.");
                $this->plugin->getScheduler()->scheduleTask(new ReplaceBlockTask(
                    $selected->getMinPosition(),
                    $selected->getMaxPosition(),
                    $selected->getWorld(),
                    $source,
                    $target,
                    ($args[2] ?? "") === "true",
                    $sender
                ));
            }
        }
    }
}