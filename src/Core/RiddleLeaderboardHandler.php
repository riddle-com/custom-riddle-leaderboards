<?php

namespace Riddle\Core;

use Riddle\Core\RiddleApp;
use Riddle\Landingpage\RiddleData;
use Riddle\Render\RiddlePageRenderer;
use Riddle\Render\RiddlePageSkeleton;

class RiddleLeaderboardHandler
{

    protected $app;
    protected $riddleFallbackId; // This riddle ID gets rendered when there's no data

    public function __construct(int $riddleFallbackId = -1)
    {
        $this->riddleFallbackId = $riddleFallbackId;

        $this->app = new RiddleApp();
        $this->_loadUserConfig();
    }

    public function start()
    {
        $this->_authenticate();
        
        $riddleData = $this->_getRiddleData();

        if (!$this->_userSkippedLeadForm($riddleData)) { // the user skipped the form / hasn't sent anything
            $this->app->processData($riddleData);
        } else {
            $this->app->setRiddleId($this->riddleFallbackId);
        }

        return $this->_render($riddleData);
    }

    private function _render($riddleData)
    {
        $renderer = $this->_getRenderer($riddleData);
        $skeleton = new RiddlePageSkeleton($this->app); // Splits the page into head, body & footer

        $leaderboardRender = $renderer->render($riddleData ? $riddleData->getJsonData() : null);
        $skeleton->setBody($leaderboardRender);

        return $skeleton->print(); // return the rendered html contents
    }

    private function _getRiddleData()
    {
        if (!isset($_REQUEST['data'])) {
            return null;
        }

        $data = json_decode($_REQUEST['data']);

        if ($data) {
            return new RiddleData($data);
        }

        // sometimes the json comes in an escaped format - let's fix that
        $data = json_decode(\stripslashes($_REQUEST['data']));

        if ($data) {
            return new RiddleData($data);
        }

        return null;
    }

    private function _userSkippedLeadForm($riddleData) {
        return $riddleData === null || empty((array) $riddleData->getLead());
    }

    protected function _getRenderer($riddleData)
    {
        $viewName = $this->_userSkippedLeadForm($riddleData) && -1 === $this->riddleFallbackId 
            ? 'renderNoDataView' // if the user skipped the form and no fallback riddle ID is defined
            : 'renderView';
        $viewName = $this->app->getConfig()->getProperty($viewName);
        $renderer = new RiddlePageRenderer($this->app, $viewName);

        return $renderer;
    }

    /**
     * Checks if the user is permitted to see the page.
     * This functions uses the config property 'secret'.
     * 
     * This function kills (via die()) the page if no secret is set or the secret is not equals the secret the user has submitted.
     */
    protected function _authenticate() {
        $secret = $this->app->getConfig()->getProperty('secret');

        if (!$secret || '' === trim($secret)) {
            http_response_code(403);
            die('Please initiate your secret in order to use your riddle extension.');
        }

        $userSecret = isset($_GET['secret']) ? urldecode($_GET['secret']) : false;
        
        if (!$secret || $userSecret !== $secret) {
            http_response_code(403);
            die('Access denied, check your secret.');
        }
    }

    /**
     * override this function if you want to load the main config in another way.
     * (we use that in our WP plugin)
     */
    protected function _loadUserConfig()
    {
        $configPath = APP_DIR . '/config/RiddleConfig.php';

        if (!file_exists($configPath)) {
            return false;
        }

        $this->app->getConfig()->addConfigFile($configPath);
    }

}