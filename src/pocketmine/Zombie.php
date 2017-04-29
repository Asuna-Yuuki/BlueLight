<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\entity;

use pocketmine\entity\Attribute;
use pocketmine\entity\AI\EntityAISwimming;
use pocketmine\entity\AI\EntityAIAttackOnCollide;
use pocketmine\entity\AI\EntityAIMoveTowardsRestriction;
use pocketmine\entity\AI\EntityAIWatchClosest;
use pocketmine\entity\AI\EntityAIHurtByTarget;
use pocketmine\entity\AI\EntityAILookIdle;
use pocketmine\entity\AI\EntityAIWander;
use pocketmine\entity\AI\EntityAINearestAttackableTarget;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\Server;

class Zombie extends Monster{
	const NETWORK_ID = 32;

	const VIEWABLE_RANGE = 20;
	const ATTACK_RANGE = 2;

	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	public $maxhealth = 20;

	public function initEntity(){
		$this->getNavigator()->setBreakDoors(true);
		$this->tasks->addTask(0, new EntityAISwimming($this));
		//$this->tasks->addTask(2, new EntityAIAttackOnCollide($this, "Player", 1.0, false));//TODO 
		$this->tasks->addTask(5, new EntityAIMoveTowardsRestriction($this, 1.0));
		$this->tasks->addTask(7, new EntityAIWander($this, 1.0));
		$this->tasks->addTask(8, new EntityAIWatchClosest($this, "pocketmine\Player", 8.0));
		$this->tasks->addTask(8, new EntityAILookIdle($this));
		$this->targetTasks->addTask(1, new EntityAIHurtByTarget($this, true, ["pocketmine\entity\PigZombie"]));
		$this->targetTasks->addTask(2, new EntityAINearestAttackableTarget($this, "pocketmine\Player", true));
		//$this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(0.23000000417232513);
	}

	public function getName(){
		return "Zombie";
	}


	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Zombie::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);
		$this->isJumping = false;
		parent::spawnTo($player);
	}

	public function getDrops(){
		$drops = [
			ItemItem::get(ItemItem::FEATHER, 0, 1)
		];
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent and $this->lastDamageCause->getEntity() instanceof Player){
			if(mt_rand(0, 199) < 5){
				switch(mt_rand(0, 2)){
					case 0:
						$drops[] = ItemItem::get(ItemItem::IRON_INGOT, 0, 1);
						break;
					case 1:
						$drops[] = ItemItem::get(ItemItem::CARROT, 0, 1);
						break;
					case 2:
						$drops[] = ItemItem::get(ItemItem::POTATO, 0, 1);
						break;
				}
			}
		}

		return $drops;
	}

	public function onUpdate($currentTick) {
		if($this->closed){
			return false;
		}


		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0 and !$this->justCreated){
			return true;
		}
		$this->lastUpdate = $currentTick;

		$hasUpdate = $this->entityBaseTick($tickDiff);
		$this->updateMovement();
		return true;
	}

	public function attack($damage, EntityDamageEvent $source){
		parent::attack($damage, $source);
		$this->updateMovement();

	}
}