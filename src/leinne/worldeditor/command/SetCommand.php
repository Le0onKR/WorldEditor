<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\SetBlockTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SetCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/set", "선택한 영역을 원하는 블럭으로 변경합니다.");
        $this->setUsage("/{$this->getName()} [blockId]");
        $this->setPermission("worldeditor.command.setblock");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (!isset($args[0])) {
                $sender->sendMessage(WorldEditor::$prefix . $this->getUsage());
            }

            if (!$selected->isValid()) {
                $sender->sendMessage(WorldEditor::$prefix . "영역이 선택되지 않았습니다.");
                return;
            }
            $block = $this->plugin->getStringToBlock($args[0]);

            if ($block === null) {
                $sender->sendMessage(WorldEditor::$prefix . "존재하지 않는 블럭입니다. [id : $args[0]]");
            } else {
                $sender->sendMessage(WorldEditor::$prefix . "블럭 설정을 시작했습니다.");
                $this->plugin->getScheduler()->scheduleTask(new SetBlockTask(
                    $selected->getMinPosition(),
                    $selected->getMaxPosition(),
                    $selected->getWorld(),
                    $block,
                    $sender
                ));
            }
        }
    }
}