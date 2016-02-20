<?php

/**
 * eBot - A bot for match management for CS:GO
 * @license     http://creativecommons.org/licenses/by/3.0/ Creative Commons 3.0
 * @author      Julien Pardons <julien.pardons@esport-tools.net>
 * @version     3.0
 * @date        21/10/2012
 */

namespace eBot\Plugins\Custom;

use eBot\Plugins\Plugin;
use eTools\Utils\Logger;
use eBot\Exception\PluginException;

/**
 * Description of PluginMatchScoreNotifier
 *
 * @author jpardons
 */
class PluginMatchScoreNotifier implements Plugin {

    private $url;
    private $token;

    public function init($config) {
        Logger::log("Init PluginMatchScoreNotifier");
        $this->url = $config["url"];
        $this->token = $config["token"];
        if ($this->url == "") {
            throw new PluginException("url null");
        }

        Logger::log("URL to perform: " . $this->url);
    }

    public function onEvent($event) {
        switch (get_class($event)) {
            case \eBot\Events\EventDispatcher::EVENT_ROUNDSCORED:
                if ($event->getMatch()->getIdentifier()) {

                    $roundResult = '';

                    $teamA = $event->getOption('teamA');
                    $teamB = $event->getOption('teamB');
                    $scoreA = $event->getOption('scoreA');
                    $scoreB = $event->getOption('scoreB');
                    $status = $event->getOption('status');

                    if ($scoreA>$scoreB){
                        $roundResult .= $teamA." won the round." . " round status : ".$status;
                    }
                    else{
                        $roundResult .= $teamB." won the round." . " round status : ".$status;
                    }

                    $serverIp = $event->getMatch()->getIp();
                    $service_url = $this->url.$serverIp. "/" .$this->token;
                    $curl = curl_init($service_url);
                    $curl_post_data = array(
                        'match_id' => $event->getMatch()->getIdentifier(),
                        'roundResult' => $roundResult,
                    );
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
                    $curl_response = curl_exec($curl);
                    if ($curl_response === false) {
                        Logger::error("Curl error");
                    }
                    curl_close($curl);
                    Logger::log($event->getMatch()->getCurrentMapId() . " - Perf $service_url");
                }
                break;
        }
    }

    public function onEventAdded($name) {

    }

    public function onEventRemoved($name) {

    }

    public function onReload() {
        Logger::log("Reloading " . get_class($this));
    }

    public function onStart() {
        Logger::log("Starting " . get_class($this));
    }

    public function onEnd() {
        Logger::log("Ending " . get_class($this));
    }

    public function getEventList() {
        return array(\eBot\Events\EventDispatcher::EVENT_ROUNDSCORED);
    }

}

?>
