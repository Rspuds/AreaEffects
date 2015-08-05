<?php

namespace AreaEffects;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\level\Position;
use pocketmine\event\entity;
use pocketmine\entity\Effect;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{
    
    public $areas;
    private $pos1, $pos2;

    public function onLoad() {
        $this->getLogger()->info(TextFormat::GREEN."AreaEffects has been loaded!");
    }
    
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->areas = (new \pocketmine\utils\Config($this->getDataFolder()."areas.yml", Config::YAML))->getAll();
        $this->getLogger()->info(TextFormat::GREEN."AreaEffects has been loaded!");
    }
    
    public function onDisable(){
        $this->areas = new \pocketmine\utils\Config($this->getDataFolder()."areas.yml", Config::YAML, $this->areas);
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) { $this->configFile = new Config($this->getDataFolder()."areas.yml", Config::YAML, array());
        if ($command == "ae") {    
            switch ($args[0]){
                case "pos1":
                    if($sender instanceof Player){
                        $pos1x = $sender->getFloorX();
                        $pos1y = $sender->getFloorY();
                        $pos1z = $sender->getFloorZ();
                        $this->pos1 = new \pocketmine\math\Vector3($pos1x, $pos1y, $pos1z);
                        $sender->sendMessage(TextFormat::GREEN."[AreaEffects]Possition 1 set as x:".$pos1x." y:".$pos1y." z:".$pos1z);
                        return true;
                        break;
                        }
                
                case "pos2":
                    if($sender instanceof Player){
                        $pos2x = $sender->getFloorX();
                        $pos2y = $sender->getFloorY();
                        $pos2z = $sender->getFloorZ();
                        $this->pos2 = new \pocketmine\math\Vector3($pos2x, $pos2y, $pos2z);
                        $sender->sendMessage(TextFormat::GREEN."[AreaEffects]Possition 2 set as x:".$pos2x." y:".$pos2y." z:".$pos2z);
                        return true;
                        break;
                        }
                        
                case "create":
                    if($sender instanceof Player){
                        if(isset($args[1], $args[2])){
                            if(isset($this->pos1 , $this->pos2)){
                                $this->areas[$args[1]] = array(
                        'pos1' => array(
                            'x' => $this->pos1->x,
                            'y' => $this->pos1->y,
                            'z' => $this->pos1->z
                            ),
                        'pos2' => array(
                            'x' => $this->pos2->x,
                            'y' => $this->pos2->y,
                            'z' => $this->pos2->z
                            ),
                        'effect' => array(
                            'id' => $args[2],
                            'duration' => 10,//only editable in configs
                            'amplifier' => 0,//TODO editable by command
                            'show' => true,//   ^
                            )//TODO multipul effects per area
                            );
                        $sender->sendMessage(TextFormat::GREEN."[AreaEffects]Area created");
                        return true;
                        break;
                        }

                }
            }  else {
                $sender->sendMessage(TextFormat::RED."this command must be used in-game");
            
                }
            }
        }
    } 
    
    public function onMove(PlayerMoveEvent $event){
    if (isset($this->areas)){    
        $player = $event->getPlayer();
        if(empty($this->areas)) {return;}
        foreach($this->areas as $area){
            if($this->isInArea($player, $area)){$this->giveEffect($player ,$area);
                }
            }
        }
    }
    
    public function isInArea(Player $player, $area){
        
        if($player->getFloorX() >= $area['pos1'['x']] && $player->getFloorX() <= $area['pos2'['x']] && $player->getFloorY() >=$area['pos1'['y']] && $player->getFloorY() <= $area['pos2'['y']] && $player->getFloorZ() >= $area['pos1'['z']] && $player->getFloorZ() <= $area['pos2'['y']] && $area['level'] == $player->getLevel()) {return true;}
        else {return false;
        }
    }
        
    public function giveEffect($player, $area){
        if($player instanceof Player){
            $id = $area['effect'['id']];
            $effect = Effect::getEffect($id);
            $effect->setDuration($area['effect'['duration']]);
            $effect->setAmplifier($area['effect'['amplifier']]);
            $effect->setVisable($area['effect'['show']]);
            $player->addEffect($effect);
        }
    }
}
