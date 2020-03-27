<?php

/**
 * @since 1.0
 * 
 * This class handles the page rendering of the webhook.
 */

namespace Riddle\Render;

use Riddle\Core\RiddleApp;
use Riddle\Exception\BadConfigException;
use Riddle\Tools\RiddleTools;
use Riddle\Landingpage\RiddleInjectedData;

class RiddlePageRenderer
{

    private $app;
    private $view;
    private $renderObject;
    private $data;
    private $injectedData;

    /**
     * Constructor of RiddlePageRenderer
     * 
     * @param $app (RiddleApp) the main RiddleApp object
     * @param $view (string) Name of the riddle view
     * @param $renderObject
     */
    public function __construct(RiddleApp $app, string $view, $renderObject = null)
    {
        $this->app = $app;
        $this->view = $view;
        $this->renderObject = $renderObject;
        $this->data = [];
    }

    /**
     * Renders the view.
     */
    public function render($data = null, $loadStore = true, $viewPath = null)
    {
        if (!$this->_viewExists()) {
            throw new BadConfigException('The landingpage view does not exist (path: ' . $this->getViewPath() . ').');
        }

        if (is_array($data)) {
            $this->setData($data);
        }

        if (!$this->app->getStore()->isLoaded() && $loadStore) {
            $this->app->getStore()->load();
        }

        return RiddleTools::getViewContents($viewPath ? $viewPath : $this->getViewPath(), [
            'renderer' => $this,
            'injected' => $this->injectedData,
        ]);
    }

    public function hasData()
    {
        return $this->app->hasData();
    }

    /**
     * BUILT-IN methods for all the views :)
     */
    public function renderModule(string $moduleName, array $options = [])
    {
        $leaderboard = $this->app->getLeaderboardModule();
            
        return $leaderboard->render($this);
    }

    public function renderBlock(string $blockName, array $args = []) :string
    {
        if (!method_exists($this->renderObject, 'renderBlock')) {
            throw new BadConfigException('The block ' . $blockName . ' does not support block rendering.');
        }

        return $this->renderObject->renderBlock($blockName, $args);
    }

    public function get(string $dataKey) 
    {
        return RiddleTools::getArrayElementFromInnerHtml($dataKey, $this->data);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    private function _viewExists()
    {
        return file_exists($this->getViewPath());
    }

    public function getViewPath($view = false)
    {
        $viewsPath = $this->app->getConfig()->getProperty('viewsPath');
        $view = $view ? $view : $this->view;

        return $viewsPath . '/' . $view . '.php';
    }

    public function injectData(RiddleInjectedData $data)
    {
        $this->injectedData = $data;
    }

    public function getInjectedData()
    {
        return $this->injectedData;
    }

}