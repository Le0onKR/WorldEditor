<?php

namespace leinne\worldeditor\listener;

use leinne\worldeditor\WorldEditor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\VanillaItems;

class EventListener implements Listener
{
    public function __construct(
        protected WorldEditor $plugin
    )
    {
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $block = $event->getBlock();

        if ($player->hasPermission("worldeditor.command.setpos") && $player->isCreative()) {
            if ($item->equals(VanillaItems::WOODEN_AXE())) {
                $event->cancel();
                $selected = $this->plugin->getSelectedArea($player);

                $player->sendMessage($selected->setFirstPosition($block->getPosition()));
            }
        }
    }

    public function onInter(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $block = $event->getBlock();

        if ($player->hasPermission("worldeditor.command.setpos") && $player->isCreative()) {
            if ($item->equals(VanillaItems::WOODEN_AXE())) {
                $event->cancel();
                $selected = $this->plugin->getSelectedArea($player);
                $position = $block->getPosition();

                $player->sendMessage($event->getAction() === $event::LEFT_CLICK_BLOCK ?
                    $selected->setFirstPosition($position, $item) :
                    $selected->setSecondPosition($position, $item)
                );
            }
        }
    }
}