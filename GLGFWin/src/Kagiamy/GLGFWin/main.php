<?php

/*
*     _    _ _        
*    | |  | (_)      
*    | |  | |_ _ __ 
*    | |/\| | | '_ \| 
*    \  /\  / | | | | 
*     \/  \/|_|_| |_|
*
* An easy to use API which counts and saves wins for each player.
* by Kagiamy
*/

namespace Kagiamy\GLGFWin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener{
     
     private $windata = [];
     private $data;
     
     public function onEnable(){
          $this->getServer()->getPluginManager()->registerEvents($this,$this);
          $this->getLogger()->info("GLGFWin by Kagiamy enabled!");
          $this->saveDefaultConfig();
          $this->data = new Config($this->getDataFolder(). "data.yml", Config::YAML, ["wins" => []]);
          $this->getwindata = (new Config($this->getDataFolder(). "data.yml", Config::YAML, ["wins" => []]))->getAll();
     }
     public function getMsg(string $msg, string $to_replace, $replace_with) : string{
          $output = $this->getConfig()->get($msg);
          $output = str_replace($to_replace, $replace_with, $output);
          return $output;
     }
     public function getWins($player){
          if($player instanceof Player){
               $player = $player->getName();
          }
          $player = strtolower($player);
          if(!isset($this->getwindata["wins"][$player])){
               return false;
          }else{
               return $this->getwindata["wins"][$player];
          }
     }
     public function addWins($player, $wins){
          if($player instanceof Player){
               $player = $player->getName();
          }
          $player = strtolower($player);
          if(!isset($this->getwindata["wins"][$player])){
               return false;
          }else{
               $currentwins = $this->getWins($player);
               $this->getwindata["wins"][$player] = $currentwins + $wins;
               $this->saveData();
               return true;
          }
     }
     public function addWin($player){
          $this->addWins($player, 1);
          return true;
     }
     public function setWins($player, $wins){
          if($player instanceof Player){
               $player = $player->getName();
          }
          $player = strtolower($player);
          if(!isset($this->getwindata["wins"][$player])){
               return false;
          }else{
               $currentwins = $this->getWins($player);
               $this->getwindata["wins"][$player] = $wins;
               $this->saveData();
               return true;
          }
     }
     public function saveData(){
          $this->data->setAll($this->getwindata);
          $this->data->save();
     }
     public function onJoin(PlayerJoinEvent $event){
          $player = $event->getPlayer();
          $player = strtolower($player->getName());
          if(!isset($this->getwindata["wins"][$player])){
               $this->getwindata["wins"][$player] = 0;
               $this->getLogger()->info("Player " . $player . " has never joined before. Creating new win data for him/her.");
               return true;
          }
     }
     public function onQuit(PlayerQuitEvent $qevent){
          $this->saveData();
     }
     public function onCommand(CommandSender $sender, Command $command, $label, array $args){
          switch($command->getName()){
               case "glgfwin":
                    $sender->sendMessage("Bạn dùng GLGFWinV1.0.0 Bởi GLGFDev Team!");
               case "mywins":
                    if($sender instanceof Player){
                         $wins = $this->getWins($sender);
                         $message = $this->getMsg("mywins", "{wins}", $wins);
                         $sender->sendMessage($message);
                         break;
                    }else{
                         $sender->sendMessage("Please use this command ingame!");
                         break;
                    }
               case "getwins":
                    if(!isset($args[0])){
                         return false;
                         break;
                    }else{
                         $player = $args[0];
                         if(($plr = $this->getServer()->getPlayer($player)) instanceof Player){
                              $player = $plr->getName();
                         }
                         $wins = $this->getWins($player);
                         if($wins === false){
                              $message = $this->getMsg("notfound", "{name}", $player);
                              $sender->sendMessage($message);
                              break;
                         }else{
                              $message = $this->getMsg("getwins", "{wins}", $wins);
                              $message = str_replace("{player}", $player, $message);
                              $sender->sendMessage($message);
                              break;
                         }
                         break;
                    }
               case "addwin":
                    if(!isset($args[0])){
                         return false;
                         break;
                    }else{
                         $player = $args[0];
                         if(($plr = $this->getServer()->getPlayer($player)) instanceof Player){
                              $player = $plr->getName();
                         }
                         $wins = $this->addWin($player);
                         if($wins === false){
                              $message = $this->getMsg("notfound", "{name}", $player);
                              $sender->sendMessage($message);
                              break;
                         }else{
                              $message = $this->getMsg("addwin", "{player}", $player);
                              $sender->sendMessage($message);
                              break;
                         }
                         break;
                    }
               case "addwins":
                    if(!isset($args[0]) || !isset($args[1])){
                         return false;
                         break;
                    }elseif(!is_numeric($args[1])){
                         return false;
                         break;
                    }else{
                         if(($plr = $this->getServer()->getPlayer($args[0])) instanceof Player){
                            $player = $plr->getName();
                         }
                         $wins = $this->addWins($player, $args[1]);
                         if($wins === false){
                            $sender->sendMessage($this->getMsg("not found", "{name}", $player));
                                   break;
                              }else{
                                   $sender->sendMessage($this->getMsg("addwins", "{player}", $player));
                                   break;
                              }
                    }
               case "setwins":
                    if(!isset($args[0]) || !isset($args[1])){
                         return false;
                         break;
                    }elseif(!is_numeric($args[1])){
                         return false;
                         break;
                    }else{
                         $player = $args[0];
                         if(($plr = $this->getServer()->getPlayer($player)) instanceof Player){
                              $player = $plr->getName();
                         }
                         $wins = $this->setWins($player, $args[1]);
                         if($wins === false){
                              $message = $this->getMsg("notfound", "{name}", $player);
                              $sender->sendMessage($message);
                              break;
                         }else{
                              $message = $this->getMsg("setwins", "{player}", $player);
                              $sender->sendMessage($message);
                              break;
                         }
                         break;
                    }
          }
          return true;
     }
     public function onDisable(){
          $this->saveData();
          $this->getLogger()->info("Data saved and disabled!");
     }
}
