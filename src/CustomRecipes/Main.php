<?php

namespace CustomRecipes;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use CustomRecipes\Recipes\Recipes;
class Main extends PluginBase implements Listener{
	
	private $craft;
	
	public function onEnable(): void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->craft = new Recipes($this);
	}
	
	public function onJoin(PlayerJoinEvent $event){
		if($this->craft instanceof Recipes){
			$event->getPlayer()->dataPacket($this->craft->getCraftingDataPacket());
		}
	}
}
