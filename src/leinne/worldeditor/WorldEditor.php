<?php

declare(strict_types=1);

namespace leinne\worldeditor;

use leinne\worldeditor\command\CopyCommand;
use leinne\worldeditor\command\CutCommand;
use leinne\worldeditor\command\FirstPosCommand;
use leinne\worldeditor\command\PasteCommand;
use leinne\worldeditor\command\RedoCommand;
use leinne\worldeditor\command\ReplaceCommand;
use leinne\worldeditor\command\SecondPosCommand;
use leinne\worldeditor\command\SetCommand;
use leinne\worldeditor\command\SphereCommand;
use leinne\worldeditor\command\UndoCommand;
use leinne\worldeditor\command\UpCommand;
use leinne\worldeditor\command\WandCommand;
use leinne\worldeditor\listener\EventListener;
use pocketmine\block\Block;
use pocketmine\block\tile\Tile;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;

class WorldEditor extends PluginBase
{
    use SingletonTrait;

    /** @var string */
    public static string $prefix = '§l§a[!] §r§7';

    private Item $wand;

    private int $tick = 2, $blockPerTick = 200;

    /** @var SelectedArea[] */
    private array $selectedArea = [];

    /** @var Block[][] */
    public array $copy = [], $undo = [], $redo = [];

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
            new CopyCommand($this), new CutCommand($this), new PasteCommand($this), new RedoCommand($this), new ReplaceCommand($this),
            new SetCommand($this), new SphereCommand($this), new UndoCommand($this), new UpCommand(), new WandCommand(),
            new FirstPosCommand($this), new SecondPosCommand($this)
        ]);

        $this->saveDefaultConfig();
        $data = $this->getConfig()->getAll();

        $updateTick = $data["update-tick"] ?? null;
        if (is_numeric($updateTick)) {
            $this->tick = max((int)$updateTick, 1);
        }

        $blockPerTick = $data["block-per-tick"] ?? $data["limit-block"] ?? null;
        if (is_numeric($blockPerTick)) {
            $this->blockPerTick = max((int)$blockPerTick, 1);
        }
    }

    public function getCopy(Player $player): array
    {
        return $this->copy[$player->getName()] ?? [];
    }

    public function resetCopy(Player $player): void
    {
        $this->copy[$player->getName()] = [];
    }

    public function getUpdateTick(): int
    {
        return $this->tick;
    }

    public function getBlockPerTick(): int
    {
        return $this->blockPerTick;
    }

    public function getPosHash(Position $pos): string
    {
        return "{$pos->x}:{$pos->y}:{$pos->z}:{$pos->world->getFolderName()}";
    }

    public function getSelectedArea(Player $player): SelectedArea
    {
        return $this->selectedArea[spl_object_hash($player)] ??= new SelectedArea();
    }

    public function saveUndo(Block $block, ?Position $pos = null): void
    {
        if (!$block->getPosition()->isValid() && ($pos === null || !$pos->isValid())) return;

        if ($pos !== null) {
            $block->position($pos->world, $pos->x, $pos->y, $pos->z);
        }
        $this->undo[$this->getPosHash($block->getPosition())][] = $block;
    }

    public function saveRedo(Block $block, ?Position $pos = null): void
    {
        if (!$block->getPosition()->isValid() && ($pos === null || !$pos->isValid())) return;

        if ($pos !== null) {
            $block->position($pos->world, $pos->x, $pos->y, $pos->z);
        }
        $this->redo[$this->getPosHash($block->getPosition())][] = $block;
    }

    public function saveCopy(Player $player, Block $block, Vector3 $pos): bool
    {
        if ($block->isSameState(VanillaBlocks::AIR())) {
            return false;
        }
        $block->position($player->getWorld(), $pos->x, $pos->y, $pos->z);
        $this->copy[$player->getName()][] = $block;
        return true;
    }

    public function setBlock(Block $block, ?Position $pos = null): void
    {
        $pos ??= $block->getPosition();
        if ($pos === null || !$pos->isValid()) return;

        if ($pos !== null) {
            $block->position($pos->world, $pos->x, $pos->y, $pos->z);
        }

        $tile = $pos->world->getTile($block->getPosition());
        if ($tile instanceof Tile) {
            $tile->close();
        }

        if ($pos->world->loadChunk($pos->x >> 4, $pos->z >> 4) === null) {
            $pos->world->setChunk($pos->x >> 4, $pos->z >> 4, new Chunk([], false));
        }
        $pos->world->setBlockAt($pos->x, $pos->y, $pos->z, $block, false);
    }

    public function getStringToBlock(string $name): ?Block
    {
        try {
            $block = VanillaBlocks::{$name}();
        } catch (\Error $e) {
            try {
                $block = LegacyStringToItemParser::getInstance()->parse($name)->getBlock();
            } catch (\InvalidArgumentException $e) {
                return null;
            }
        }
        return $block;
    }
}