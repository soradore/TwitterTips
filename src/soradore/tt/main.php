<?php



namespace soradore\tt;


use pocketmine\plugin\PluginBase;
use pocketmine\Player;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\Listener;

use pocketmine\utils\Config;
use pocketmine\utils\Utils;

use Abraham\TwitterOAuth\TwitterOAuth;





class main extends PluginBase implements Listener 
{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        if(!file_exists($this->getDataFolder())) mkdir($this->getDataFolder(), 0744, true);
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML,
                             [
                              "CONSUMER_KEY" => "",
                              "CONSUMER_SECRET" => "",
                              "ACCESS_TOKEN" => "",
                              "ACCESS_TOKEN_SECRET" => "",
                             ]);

        $consumer_key = $this->config->get("CONSUMER_KEY");
        $consumer_secret = $this->config->get("CONSUMER_SECRET");
        $access_token = $this->config->get("ACCESS_TOKEN");
        $access_token_secret = $this->config->get("ACCESS_TOKEN_SECRET");

        $this->api = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
        $this->max_players = $this->getServer()->getMaxPlayers();

    }


    function onJoin(PlayerJoinEvent $ev){
    	$player = $ev->getPlayer();
    	$name = $player->getName();
    	$skin = base64_encode($player->getSkin()->getSkinData());
        $skin = Utils::postURL("http://pocketmp.xyz/skin_maker.php", ['skin'=>$skin]);

        $path = $this->getDataFolder() . "tmp/" . $name . ".png";
        file_put_contents($path, $skin);
        $this->tweet($name, $path);
    }


    function tweet($name, $path){
    	$media_id = $this->api->upload("media/upload", ["media" => $path]);
        $parameters = [
    		'status' => $name . "がサーバーに参加しました \n Online : " . count($this->getServer()->getOnlinePlayers()) . " / " . $this->max_players,
    		'media_ids' => $media_id->media_id_string,
    	];
    	$result = $this->api->post('statuses/update', $parameters);
    	unlink($path);
    }
}


    