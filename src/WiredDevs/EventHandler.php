<?php
namespace WiredDevs;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerJumpEve;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use pocketmine\command\defaults\EnchantCommand;
use pocketmine\nbt\tag\StringTag;
class EventHandler implements Listener {
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;

    }

    //*This stuff shouldnt need to be explained tbh.*//
    public function onJoin(PlayerJoinEvent $event) {
        $p = $event->getPlayer();
        $player = trim(strtolower($p->getName()));
        $p->sendMessage(TF::GRAY.TF::BOLD."███████████████\n".TF::AQUA."Welcome to BGPE Factions!\n".TF::YELLOW."Enjoy your stay!\n".TF::GRAY."███████████████");
        $this->plugin->addToUHC($p);
        //if($this->plugin->isGameActive()){
           //$p->kick(TF::RED."UHC is already running");
       // }
    }        

}