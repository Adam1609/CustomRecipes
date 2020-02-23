<?php

namespace CustomRecipes\Recipes;

use pocketmine\inventory\ShapedRecipe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance; 
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;

use CustomRecipes\Main;

class Recipes{
	
	private $plugin;
	
	private $craftingDataCache;
	
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
		$this->init();
	}
	
	public function init(): void{
		$recipes = $this->getRecipes();
		
		$pk = new CraftingDataPacket();
		$pk->cleanRecipes = true;
		
		foreach($recipes as $recipe){
			$ing = array_map(function(array $data): Item{return Item::jsonDeserialize($data); }, $recipe['input']);
			$res = Item::get($recipe['result']['id'], $recipe['result']['meta'], $recipe['result']['count']);
			
			foreach($recipe['result']['enchantments'] as $key => $level){
				$enchantment = Enchantment::getEnchantmentByName($key);
				//Try to implements Custom Enchant, need some test
				//$cenchantment = CustomEnchantManager::getEnchantmentByName($key);
				$res->addEnchantment(new EnchantmentInstance($enchantment, $level));
				//$res->addEnchantment(new EnchantmentInstance($cenchantment, $level));
			}
			$pk->addShapedRecipe(new ShapedRecipe($recipe['shape'], $ing, [$res]));
			$this->plugin->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe($recipe['shape'], $ing, [$res]));
		}
		
		foreach($this->plugin->getServer()->getCraftingManager()->getShapedRecipes() as $list){
			foreach($list as $recipe){
				
				if($recipe instanceof ShapedRecipe){
					foreach($recipe->getResults() as $res){
						foreach($recipes as $rec){
							if($res->getId() == $rec['replace']['id']){
								break 3;
							}
						}
					}
				}
				
				$pk->addShapedRecipe($recipe);
			}
		}
		
		$batch = new BatchPacket();
		$batch->addPacket($pk);
		$batch->setCompressionLevel($this->plugin->getServer()->getInstance()->networkCompressionLevel);
		$batch->encode();
		
		$this->craftingDataCache = $batch;
	}
	
	public function getRecipes(): array{
		//Change the path anyway you like, will update this later
		return json_decode(file_get_contents($this->plugin->getServer()->getDataPath().'/plugins/CustomRecipes-master/src/CustomRecipes/Recipes/Recipes.json'), true);
	}
	
	public function getCraftingDataPacket(): BatchPacket{
		return $this->craftingDataCache;
	}
}
