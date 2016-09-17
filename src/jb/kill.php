<?php

namespace jb;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;

class kill extends PluginBase implements Listener{

public function onEnable(){
    $this->klist=[];
		$this->elist=[];
		$this->cachec=[];
		$this->rep=1;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		@mkdir($this->getDataFolder());
       
		$this->c=new Config($this->getDataFolder()."cfg.yml",Config::YAML,array());
		$this->setting=new Config($this->getDataFolder()."setting.yml",Config::YAML,array());
    $this->cachec=$this->c->getAll();

			$this->getLogger()->info(TextFormat::WHITE . "插件已启用！");
		$this->getLogger()->info(TextFormat::BLUE . "===========================");
		$this->getLogger()->info(TextFormat::YELLOW . "本插件由@CreeperGo编写，谢谢chenxiaoyi 的创意和支持 部分更新來自RexRed6802");
		$this->getLogger()->info(TextFormat::BLUE  . "---------------------------");
      Entity::registerEntity(NPC::class);
}

	public function dop($entity,$event){
		$it=[];
		foreach($this->cachec[$entity->getNameTag()]["drops"] as $k){
			$tm=explode(":",$k);
			if(mt_rand(0,100)<$tm[2]){
			$it=array_merge(array(new Item($tm[0],0,$tm[1])),$it);
			}
		}
		$event->setDrops($it);
	}

public function onEntityDeath(EntityDeathEvent $event){
	$entity = $event->getEntity();
  $cause = $entity->getLastDamageCause();
  	if($cause instanceof EntityDamageByEntityEvent){
    	$killer = $cause->getDamager();
	  		if($killer instanceof Player){
         }else{
         $killer=$entity;
       }
	 	}
    if(isset($entity->namedtag->npc) and $entity->namedtag->npc=="true"){
		$this->getServer()->dispatchCommand(new ConsoleCommandSender,str_replace("{player}",$killer->getName(),$this->cachec[$entity->getNameTag()]["command"]));
		$this->dop($entity,$event);
		unset($entity->target);
		if($this->rep==1){
		$pe = $this->cachec[$entity->getNameTag()];
		$et = $this->spaw($pe,$entity->getLevel());
}
}
}

public function spaw(string $name,$level){
     $motion = new Vector3(0,0,0);
     $data = $this->cachec[$name];
     $nbt = new CompoundTag("", [
            "Pos" => new ListTag("Pos", [
                new DoubleTag("", $data["x"]),
                new DoubleTag("", $data["y"]),
                new DoubleTag("", $data["z"])
            ]),
            "Motion" => new ListTag("Motion", [
                new DoubleTag("", 0),
                new DoubleTag("", 0),
                new DoubleTag("", 0)
            ]),
            "Rotation" => new ListTag("Rotation", [
                new FloatTag("", 0),
                new FloatTag("", 0)
            ]),
			"spawnPos" => new ListTag("spawnPos", [
                new DoubleTag("", $data["x"]),
                new DoubleTag("", $data["y"]),
                new DoubleTag("", $data["z"])
            ]),
			"range" => new FloatTag("range",$data["range"] * $data["range"]),
			"attackDamage" => new FloatTag("attackDamage",$data["damage"]),
			"networkId" => new IntTag("networkId",63),
			"speed" => new FloatTag("speed",$data["speed"]),
			"skin" => new StringTag("skin",$data["skin"]),
      "heldItem"=> new StringTag("heldItem",$data["heldItem"])
            ]);
	 $entity=Entity::createEntity("NPC", $level->getChunk($x>>4, $z>>4),$nbt);
	$entity->setMaxHealth($this->cachec[$name]["health"]);
	$entity->setHealth($this->cachec[$name]["health"]);
   $entity->setNameTag($name);
	 $entity->spawnToAll();
	 return $entity;
}
 public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
		if($cmd == "new"){				
          $sender->sendMessage("test1");
          $held = $sender->getInventory()->getItemInHand();
					$this->c->set($args[0],array(
            "x"=>$sender->x,
            "y"=>$sender->y,
            "z"=>$sender->z,
            "level"=>$sender->level->getName(),
            "health"=>20,
            "range"=>10,
            "damage"=>1,
            "speed"=>1,
            "drops"=>"1;2;3",
            "heldItem"=>"{$held->getId()}:{$held->getDamage()}:{$held->getCount()}",
            "command"=>"/say player",
            "skin"=>bin2hex($sender->getSkinData())
            ));
          $sender->sendMessage("test2");
					$this->c->save();
					$this->spaw($args[0],$sender->level);
         $sender->sendMessage("test3");
					$sender->sendMessage("成功新增npc: $args[0]");
				}
}
        
}
