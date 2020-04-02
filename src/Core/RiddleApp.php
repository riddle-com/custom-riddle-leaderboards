<?php

/**
 * @since 1.0
 * 
 * This is the "heart" of the application. 
 * This class holds many objects and provides it to all the services this application needs. 
 */

namespace Riddle\Core;

use Riddle\Exception\BadConfigException;
use Riddle\Render\RiddlePageSkeleton;
use Riddle\Landingpage\Module\LeaderboardModule;
use Riddle\Landingpage\Store\RiddleJsonStore;
use Riddle\Landingpage\RiddleData;

class RiddleApp
{

    private $leaderboardHandler;

    /**
     * @var integer
     *  
     * The riddle id which the webhook received
     */
    private $riddleId;

    /**
     * @var RiddleData
     * 
     * Stores the riddle webhook data (if available)
     */
    private $data;

    private $config;
    private $store;
    private $skeleton;
    private $leaderboardModule;

    /**
     * Constructor of RiddleApp.
     */
    public function __construct(RiddleLeaderboardHandler $handler = null)
    {
        $this->leaderboardHandler = $handler;

        $this->config = new RiddleConfig();
        $this->skeleton = new RiddlePageSkeleton($this);
        $this->store = $this->_getRiddleStore();
        $this->leaderboardModule = new LeaderboardModule($this);
    }

    /**
     * This method processes the data which the webhook has received.
     * It sends the data to the RiddleStore and to all the modules which have been registered.
     * 
     * @param $data (RiddleData) webhook riddle data
     */
    public function processData(RiddleData $data)
    {
        $this->data = $data;
        $this->riddleId = $data->getId();

        if (!$this->getStore()->isLoaded()) {
            $this->getStore()->load();
        }

        $this->getStore()->addLead($data, $this);
        $this->getStore()->store();
        $this->leaderboardModule->processData($data);
    }

    private function _getRiddleStore()
    {
        return new RiddleJsonStore($this);
    }

    public static function getBaseUrl() 
    {
        $hostName = $_SERVER['HTTP_HOST'];
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== "off") ? "https" : "http";

        return $protocol . '://' . $hostName;
    }

    /**
     * GETTERS AND SETTERS
     */

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(RiddleConfig $config) 
    {
        $this->config = $config;
    }

    public function getStore()
    {
        return $this->store;
    }

    public function setStore(RiddleStore $store) 
    {
        $this->store = $store;
    }

    public function getRiddleId()
    {
        return $this->riddleId;
    }

    public function setRiddleId(int $riddleId)
    {
        $this->riddleId = $riddleId;
    }

    public function getSkeleton()
    {
        return $this->skeleton;
    }

    public function setSkeleton(RiddlePageSkeleton $skeleton)
    {
        $this->skeleton = $skeleton;
    }

    public function hasData()
    {
        return $this->data !== null;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData(RiddleData $data) 
    {
        $this->data = $data;
    }

    public function getLeaderboardModule()
    {
        return $this->leaderboardModule;
    }

    public function getLeaderboardHandler()
    {
        return $this->leaderboardHandler;
    }
    
}