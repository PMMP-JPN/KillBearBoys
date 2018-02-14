<?php

namespace KillBearBoys;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;
use pocketmine\event\Event;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;

/*
Logs
	13.8.18 1.0 Begin
	13.8.20 1.0.1
	13.8.24 1.0.2
	13.11.16 2.0
	-rewrite all things
	13.11.23 2.0.1
	-Fix bug
	14.3.1 2.0.2
	-Remove default timezone
        14.10.18 2.0.3 and 2.0.4
        -1.4Beta Support(ljy Thank you!)
	15.09.16 3.0.0
	-Database changed to sqlite3.(haniokasai)
 -thanks hmy2001! http://hmy2001.dip.jp/Blog/?m=201510
*/

class KillBearBoys extends PluginBase implements Listener, CommandExecutor{
	private $wands;
        public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$dbfile = $this->getDataFolder()."killb.db";
		if(!file_exists($this->getDataFolder())){
			@mkdir($this->getDataFolder(), 0744, true);
		}
		if(!file_exists($dbfile)){
			$this->db = new \SQLite3($dbfile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		}else{
			$this->db = new \SQLite3($dbfile, SQLITE3_OPEN_READWRITE);
		}
		$this->db->query("CREATE TABLE IF NOT EXISTS  block (xyz TEXT PRIMARY KEY, level TEXT , who TEXT ,ip TEXT, cid TEXT, action TEXT, time TEXT, blockname TEXT, blockid INT, meta INT)");

		$this->wands = array();
        }
		public function onDisable(){
		$this->db->close();
	}

        public function onQuit(PlayerQuitEvent $event){
		if(isset($this->wands[$event->getPlayer()->getName()])){
			unset($this->wands[$event->getPlayer()->getName()]);
		}
        }

        public function onBreak(BlockBreakEvent $event){
		if(!isset($this->wands[$event->getPlayer()->getName()])){		
			$eventname = "Break";
 			$this->regi($event,$eventname);               
		}else{
			$this->chk($event);
            $event->setCancelled(true);
        }
		}

        public function onPlace(BlockPlaceEvent $event){
		if(!isset($this->wands[$event->getPlayer()->getName()])){
				$eventname = "Place";
 				$this->regi($event,$eventname);        
			}else{
				$this->chk($event);
                $event->setCancelled(true);
			}		
        }

        public function onTouth(PlayerInteractEvent $event){
		if(!isset($this->wands[$event->getPlayer()->getName()])){
			$eventname = "Touch";
 			$this->regi($event,$eventname);        
			}else{
				$this->chk($event);
                $event->setCancelled(true);
}
	}

		public function regi($event,$eventname){
			$level = $event->getPlayer()->getLevel()->getName();
			$x = $event->getBlock()->x;
			$y = $event->getBlock()->y;
			$z = $event->getBlock()->z;
			$xyz =""."x"."$x"."y"."$y"."z"."$z"."";
			$player = $event->getPlayer();
			$who = $player->getName();
			$ip =  $player->getAddress();
			$cid = $player->getClientId(); 
			$action = $eventname;
			$time = date("Y/m/d-H:i:s", time());
			$blockname = $event->getBlock()->getName(); 
			$blockid = $event->getBlock()->getID();
			$meta = $event->getBlock()->getDamage();	
			$this->db->query("INSERT OR REPLACE INTO block VALUES(\"$xyz\", \"$level\",  \"$who\",  \"$ip\",  \"$cid\", \"$action\", \"$time\", \"$blockname\",  \"$blockid\",  \"$meta\")");
	
		}

		public function chk($event){
			$x = $event->getBlock()->x;
			$y = $event->getBlock()->y;
			$z = $event->getBlock()->z;
			$xyz =""."x"."$x"."y"."$y"."z"."$z"."";
			$player = $event->getPlayer();
			$result = $this->db->query("SELECT who , ip, cid, action, blockname, blockid, meta, time FROM block WHERE xyz = \"$xyz\"");
			 $rows = $result->fetchArray(SQLITE3_ASSOC);
				if($rows['who'] == null){
					$player->sendMessage("[KillBearBoys]There are no log.");
			}elseif($result){ 
			
			$player->sendMessage("[KillBearBoys]////////////////////");
			$player->sendMessage("[XYZ] ".$xyz);
			$player->sendMessage("[Name] ". $rows['who']);
			$player->sendMessage("[IP] ". $rows['ip']." [CID] ". $rows['cid']);
			$player->sendMessage("[Action]". $rows['action']);
			$player->sendMessage("[Block] ". $rows['blockname']." [ID] ". $rows['blockid']. ":".$rows['meta']);
			$player->sendMessage("[Date] ". $rows['time']);
			$player->sendMessage("////////////////////////////////");
	}
	}
        public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
                 if($sender instanceof Player){
			if(!isset($this->wands[$sender->getName()])){
				$this->wands[$sender->getName()] = true;
				$sender->sendMessage("[KillBearBoys] Enable.");
			}else{
				unset($this->wands[$sender->getName()]);
				$sender->sendMessage("[KillBearBoys] Disable.");
			}
                 }else{
			$sender->sendMessage("[KillBearBoys] Please run this command in-game.");
                 }
                 return true;
        }

}


