<?php

declare(strict_types=1);

namespace leinne\worldeditor\task;

use leinne\worldeditor\WorldEditor;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;

class ReplaceBlockTask extends Task
{
    private Position $pos;

    private Vector3 $startPos;

    private Vector3 $endPos;

    private ?World $world;

    private Block $block;

    private Block $target;

    private bool $checkDamage;

    private ?Player $player;

    public function __construct(Vector3 $startPos, Vector3 $endPos, World $world, Block $block, Block $target, bool $checkDamage, ?Player $player = null)
    {
        $this->player = $player;
        $this->startPos = $startPos;
        $this->endPos = $endPos;
        $this->world = $world;
        $this->block = $block;
        $this->target = $target;
        $this->checkDamage = $checkDamage;
        $this->pos = Position::fromObject($startPos->asVector3(), $world);
    }

    public function onRun(): void
    {
        $count = 0;
        $worldEditor = WorldEditor::getInstance();
        while (true) {
            if ($count < $worldEditor->getBlockPerTick()) {
                $before = $this->world->getBlockAt($this->pos->x, $this->pos->y, $this->pos->z);
                if ($before->getTypeId() === $this->block->getTypeId()) {
                    if ((!$this->checkDamage || $before->getIdInfo()->getBlockTypeId() === $this->block->getIdInfo()->getBlockTypeId())) {
                        ++$count;
                        $worldEditor->saveUndo($before);
                        $worldEditor->setBlock($this->target, $this->pos);
                    }
                }
                if (++$this->pos->x > $this->endPos->x) {
                    $this->pos->x = $this->startPos->x;
                    if (++$this->pos->z > $this->endPos->z) {
                        $this->pos->z = $this->startPos->z;
                        if (++$this->pos->y > $this->endPos->y) {
                            if ($this->player !== null && !$this->player->isClosed()) {
                                $this->player->sendMessage(WorldEditor::$prefix . "블럭 변경이 완료되었습니다");
                            } else {
                                Server::getInstance()->getLogger()->info(WorldEditor::$prefix . ($this->player !== null ? "{$this->player->getName()}님이 명령햔 " : "") . "블럭 변경이 완료되었습니다");
                            }
                            break;
                        }
                    }
                }
            } else {
                $this->setHandler(null);
                $worldEditor->getScheduler()->scheduleDelayedTask($this, $worldEditor->getUpdateTick());
                break;
            }
        }
    }
}