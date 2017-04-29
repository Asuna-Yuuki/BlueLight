<?php
namespace pocketmine\entity\AI;

use pocketmine\entity\Attribute;

class EntityMoveHelper{

	protected $entity;
	protected $posX;
	protected $posY;
	protected $posZ;
	protected $speed;
	public $update = false;

	public function __construct($entitylivingIn){
		$this->entity = $entitylivingIn;
		$this->posX = $entitylivingIn->x;
		$this->posY = $entitylivingIn->y;
		$this->posZ = $entitylivingIn->z;
		$this->update = false;
	}

	public function isUpdating(){
		echo($this->update);
		return $this->update;
	}

	public function getSpeed(){
		return $this->speed;
	}

	public function setMoveTo($x, $y, $z, $speedIn){
		$this->posX = $x;
		$this->posY = $y;
		$this->posZ = $z;
		$this->speed = $speedIn;
		$this->update = true;
	}

	public function onUpdateMoveHelper(){
		$this->entity->setMoveForward(0.0);

		if ($this->update){
			$this->update = false;
			$i = floor($this->entity->getBoundingBox()->minY + 0.5);
			$d0 = $this->posX - $this->entity->x;
			$d1 = $this->posZ - $this->entity->z;
			$d2 = $this->posY - $i;
			$d3 = $d0 * $d0 + $d2 * $d2 + $d1 * $d1;

			if ($d3 >= 2.500000277905201E-7){
				$f = (atan2($d1, $d0) * 180.0 / M_PI) - 90.0;
				$this->entity->yaw = $this->limitAngle($this->entity->yaw, $f, 30.0);
				$this->entity->setAIMoveSpeed($this->speed * 0.25);//$this->entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getValue());

				if ($d2 > 0.0 && $d0 * $d0 + $d1 * $d1 < 1.0){
					$this->entity->getJumpHelper()->setJumping();
				}
			}
		}
	}

	public function wrapAngleTo180($value){
		$value = $value % 360.0;

		if ($value >= 180.0){
			$value -= 360.0;
		}

		if ($value < -180.0){
			$value += 360.0;
		}

		return $value;
	}

	protected function limitAngle($p_75639_1_, $p_75639_2_, $p_75639_3_){
		$f = self::wrapAngleTo180($p_75639_2_ - $p_75639_1_);

		if ($f > $p_75639_3_){
			$f = $p_75639_3_;
		}

		if ($f < -$p_75639_3_){
			$f = -$p_75639_3_;
		}

		$f1 = $p_75639_1_ + $f;

		if ($f1 < 0.0){
			$f1 += 360.0;
		}else if ($f1 > 360.0){
			$f1 -= 360.0;
		}

		return $f1;
	}

	public function getX(){
		return $this->posX;
	}

	public function getY(){
		return $this->posY;
	}

	public function getZ(){
		return $this->posZ;
	}
}