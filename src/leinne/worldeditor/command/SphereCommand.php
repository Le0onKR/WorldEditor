<?php

declare(strict_types=1);

namespace leinne\worldeditor\command;

use leinne\worldeditor\task\MakeSphereTask;
use leinne\worldeditor\WorldEditor;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SphereCommand extends Command
{
    public function __construct(protected WorldEditor $plugin)
    {
        parent::__construct("/sphere", "선택한 영역을 원점으로 하는 구를 생성합니다.");
        $this->setUsage("/{$this->getName()} [blockId] [반지름] [채우기(true/false)]");
        $this->setPermission("worldeditor.command.createsphere");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof Player) {
            $selected = $this->plugin->getSelectedArea($sender);

            if (count($args) < 2) {
                $sender->sendMessage(WorldEditor::$prefix . $this->getUsage());
                return;
            }

            if ($selected->getWorld() === null || $selected->getMaxPosition()->y < 0) {
                $sender->sendMessage(WorldEditor::$prefix . "영역을 설정해야 합니다");
                return;
            }
            $block = $this->plugin->getStringToBlock($args[0]);

            if ($block === null) {
                $sender->sendMessage(WorldEditor::$prefix . "존재하지 않는 블럭입니다. [id: $args[0]]");
                return;
            }
            if (!is_numeric($args[1])) {
                $sender->sendMessage(WorldEditor::$prefix . "구의 반지름은 숫자여야 합니다.");
                return;
            }
            $pos = $selected->getFirstPosition();
            if ($pos->y < 0) {
                $pos = $selected->getSecondPosition();
            }
            $sender->sendMessage(WorldEditor::$prefix . "구 생성을 시작했습니다.");
            $this->plugin->getScheduler()->scheduleTask(new MakeSphereTask(
                $pos, $sender->getWorld(), $block, (int)$args[1], ($args[2] ?? "") !== "false", $sender
            ));
        }
    }
}