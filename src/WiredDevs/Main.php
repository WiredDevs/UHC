<?php
namespace WiredDevs;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\particle\Particle;
use WiredDevs\EventHandler;
use pocketmine\utils\TextFormat as TF;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\sound;
use pocketmine\Server;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\nbt\NBT;
use pocketmine\level\Location;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\entity\Effect;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntArrayTag;
class Main extends PluginBase implements Listener {


    //*queues*//

    public $uhc = array();
    public $canMove = FALSE;
    public $players = array();

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventHandler($this), $this);
        $this->cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->cfg->set("Active", "false");
        $this->cfg->save();
    
    }

    public function onDisable() {
        $this->cfg->set("Active", "false");
        $this->cfg->save();
    }


    //*THIS IS WHERE ALL OF OUR MAIN FUNCTIONS SHOULD GO AND STUFF*//
    
    //*Is the player already in a queue?*//

    public function isInQueue($player) {
        if(in_array($player, $this->uhc)) {
            return true;
        } else {
            return false;
        }   
    }

    //*If everything thing is right, this function launches a countdown. The round will start once the task is done
    //

    public function isGameActive() {
        if($this->cfg->get("Active") === "true"){
            return true;
        }else {
            return false;
        }
    }

    public function launchCountdown() {
        $players = $this->uhc;
        $this->players = $players;
        $player1 = $players[0];
        $player2 = $players[2];
        $player3 = $players[3];
        $player4 = $players[4];
        $player5 = $players[5];
        $player6 = $players[6];
        $player7 = $players[7];
        $player8 = $players[8];
        $p = array($this->players[0], $this->players[1], $this->players[2], $this->players[3], $this->players[4], $this->players[5], $this->players[6], $this->players[7], $this->players[8]);
        foreach($p as $a){
            if(count($this->uhc) < 0){
                $player1->sendMessage(TF::RED."Currently waiting on ". count($this->uhc));
            }

        $this->cfg->set("Active", "true");
        $this->cfg->save();
        // Create a new countdowntask
        $task = new CountDownTask($this);
        $this->countdownTask = $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask($task, 20, 20);
        $player1->sendMessage("uhc starting....");   
        }

        
    }

    public static function randomizeCoordinates(int $range, Level $level){
        $x = mt_rand(-$range, $range);
        $z = mt_rand(-$range, $range);
        $y = 126;
        $pos = new Position($x, $y, $z, $level);
        if(!$level->isChunkLoaded($pos->x >> 4, $pos->y >> 4)){
            $level->loadChunk($pos->x >> 4, $pos->y >> 4);
        }
        do {
            $pos->y -= 1;
            $block = $pos->level->getBlock($pos);
        } while($pos->y > 0 and (!$block->isSolid() and $block->getId() !== Block::LAVA and $block->getId() !== Block::STILL_LAVA) and ($pos->level->getBlock($pos->add(0, 1))->isSolid() and $pos->level->getBlock($pos->add(0, 2))->isSolid()));
        if($pos->y > 0) {
            return $pos;
        }
        return self::randomizeCoordinates($range, $level);
    }

    public function prepPlayer($player) {
        $player->getInventory()->clearAll();
        $player->removeAllEffects();
        $player->extinguish();
        $player->setHealth($player->getMaxHealth());
        $player->setFood($player->getMaxFood());
        $player->setXpLevel(0);
        $player->setXpProgress(0);
    }

    public function scatter($player, int $range, Level $level){
        $position = $this->randomizeCoordinates($range, $level);
        $player->teleport($position);
    }

    //*How many players are in the queue.*//
    
    public function getNumberOfPlayersInQueue(){
        return count($this->uhc);
    }

    public function abortUHC() {
        $this->getScheduler()->cancelTask($this->countdownTask->getTaskId()); 
        $this->cfg->set("Active", "false");
        $this->cfg->save();
    }

    public function startUHC($player) {
        $level = $this->getServer()->getLevelByName("UHC");
        $this->scatter($player, 250, $level);

    }

    public function addToUHC($player) {
        if($this->isInQueue($player)) {
            $player->sendMessage(TF::RED."You are already in the queue to play");
            return false;
        }
        if($this->getNumberOfPlayersInQueue() == 8) {
            $player->sendMessage("8 people already queued in");
            return false;
        }

        array_push($this->uhc, $player);
        $this->launchCountdown();
        $player->sendMessage("added to uhc");
    }

    public function endUHC($player) {
        $this->cfg->set("Active", "false");
        $this->cfg->save();
    }
}

class CountDownTask extends PluginTask{
    
    const COUNTDOWN_DURATION = 30;
    
    private $countdownValue;
    
    public function __construct($plugin){     
        $this->countdownValue = 5;
        $this->plugin = $plugin;
        parent::__construct($plugin);
    }
    
    public function onRun($currentTick){
        $players = array($this->plugin->players[0], $this->plugin->players[1], $this->plugin->players[2], $this->plugin->players[3], $this->plugin->players[4], $this->plugin->players[5], $this->plugin->players[6], $this->plugin->players[7], $this->plugin->players[8]);
        //$players = array($this->plugin->players[0], $this->plugin->players[1]);
        foreach($players as $player){
            if(count($this->plugin->uhc) < 8){
                $player->sendMessage(TF::RED."Someone left, countdown cancelled");
                $this->Plugin->abortUHC();
            }else{     
                $player->sendTip(TextFormat::GOLD . TextFormat::BOLD . $this->countdownValue . TextFormat::RESET . " sec...");
                $this->countdownValue--;
                switch ($this->countdownValue) {
                    case "0":
                    $this->plugin->startUHC($player);
                    //need to start game task next should be a 15 minute game only!
                    return;
                    case "5":
                    $this->plugin->prepPlayer($player);
                    return;
                }
            }
        }
    }
}

