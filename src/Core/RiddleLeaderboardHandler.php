<?php

namespace Riddle\Core;

use Riddle\Core\RiddleApp;
use Riddle\Landingpage\RiddleData;
use Riddle\Render\RiddlePageRenderer;
use Riddle\Render\RiddlePageSkeleton;

class RiddleLeaderboardHandler
{

    private $app;

    public function __construct()
    {
        $this->app = new RiddleApp();
        $this->_loadUserConfig();
    }

    public function start()
    {
        $this->_authenticate();

        $riddleData = $this->_getRiddleData();

        if (!$this->_userSkippedLeadForm($riddleData)) { // the user skipped the form / hasn't sent anything
            $this->app->processData($riddleData);
        }

        $this->_render($riddleData);
    }

    private function _render($riddleData)
    {
        $viewName = $this->_userSkippedLeadForm($riddleData) ? 'renderNoDataView' : 'renderView';
        $viewName = $this->app->getConfig()->getProperty($viewName);

        $renderer = new RiddlePageRenderer($this->app, $viewName);
        $skeleton = new RiddlePageSkeleton($this->app);

        $skeleton->setBody(
            $renderer->render($riddleData ? $riddleData->getJsonData() : []) // render the template
        );
        echo $skeleton->printOut(); // print out the rendered html contents
    }

    private function _getRiddleData()
    {
        if (!isset($_REQUEST['data'])) {
            return null;
        }

        return new RiddleData($_REQUEST['data']);
    }

    private function _userSkippedLeadForm($riddleData) {
        return $riddleData === null || empty( (array) $riddleData->getLead());
    }

    /**
     * Checks if the user is permitted to see the page.
     * This functions uses the config property 'secret'.
     * 
     * This function kills (via die()) the page if no secret is set or the secret is not equals the secret the user has submitted.
     */
    public function _authenticate() {
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

    private function _loadUserConfig()
    {
        $configPath = APP_DIR . '/config/RiddleConfig.php';

        if (!file_exists($configPath)) {
            return false;
        }

        $this->app->getConfig()->addProperties($configPath);
    }

}