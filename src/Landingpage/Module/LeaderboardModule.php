<?php

/**
 * @since 1.0
 */

namespace Riddle\Landingpage\Module;

use Riddle\Tools\RiddleTools;
use Riddle\Landingpage\Module\Block\LeaderboardLastLeadBlock;
use Riddle\Landingpage\Module\Block\LeaderboardLeadsBlock;
use Riddle\Landingpage\Module\Block\LeaderboardSpotLeadsBlock;

use Riddle\Landingpage\Module\Service\LeaderboardHelperService;
use Riddle\Landingpage\Module\Service\LeaderboardStoreService;

use Riddle\Landingpage\Module\Shortcode\LeaderboardBetterThanShortcode;
use Riddle\Landingpage\Module\Shortcode\LeaderboardMissedPlaceShortcode;
use Riddle\Landingpage\Module\Shortcode\LeaderboardPlacementShortcode;
use Riddle\Landingpage\Module\Shortcode\ModuleShortcodeManager;

use Riddle\Landingpage\RiddleData;
use Riddle\Exception\FileNotFoundException;
use Riddle\Render\RiddlePageRenderer;

class LeaderboardModule
{
    private $app;
    private $storeService;
    private $helperServie;
    private $shortcodeManager;

    public function __construct($app)
    {
        $this->app = $app;

        $this->storeService = new LeaderboardStoreService($this);
        $this->helperService = new LeaderboardHelperService($this);

        $this->shortcodeManager = new ModuleShortcodeManager();
        $this->shortcodeManager->add(new LeaderboardBetterThanShortcode($this));
        $this->shortcodeManager->add(new LeaderboardMissedPlaceShortcode($this));
        $this->shortcodeManager->add(new LeaderboardPlacementShortcode($this));
    }

    public function processData(RiddleData $data)
    {
        return $this->storeService->processAndStore($data);
    }

    public function render(RiddlePageRenderer $renderer)
    {
        $leads = $this->storeService->getEntries();

        if (empty($leads)) {
            return 'there are no leaderboard leads yet.';
        }

        return RiddleTools::getViewContents($this->_getViewPath(), [
            'module' => $this,
            'injected' => $renderer->getInjectedData(),
        ]);
    }

    public function renderShortcode(string $shortcodeName, array $args = [])
    {
        return $this->shortcodeManager->render($shortcodeName, $args);
    }

    public function renderBlock(string $blockName, array $args)
    {
        if ('leaderboard-leads' === $blockName) {
            $block = new LeaderboardLeadsBlock($this);
        }

        if ('spot-leaderboard-leads' === $blockName) {
            $block = new LeaderboardSpotLeadsBlock($this);
        }

        if ('last-leaderboard-lead' === $blockName) {
            $block = new LeaderboardLastLeadBlock($this);
        }

        if (!isset($block)) {
            return 'Whoops - unknown block: ' . $blockName;
        }

        return $block->render($args);
    }

    private function _getSpotRange($range)
    {
        return [
            'start' => $range[0] - 1,
            'end' => $range[1] - 1,
        ];
    }

    public function getLeaderboardLength()
    {
        return $this->app->getConfig()->getProperty('leaderboardLength');
    }

    public function getLeaderboardLeads()
    {
        return $this->storeService->getEntries();
    }

    public function getStoreService()
    {
        return $this->storeService;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function getHelperService()
    {
        return $this->helperService;
    }

    private function _getViewPath()
    {
        $viewsPath = $viewsPath = $this->app->getConfig()->getProperty('viewsPath');
        $path = $viewsPath . '/leaderboard-module.php';

        if (!$path) {
            throw new FileNotFoundException('The leaderboard module template does not exist (path: ' . $path . ')');
        }

        return $path;
    }

}