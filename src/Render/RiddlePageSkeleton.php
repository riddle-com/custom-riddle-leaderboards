<?php

/**
 * @since 1.0
 */

namespace Riddle\Render;

use Riddle\Core\RiddleApp;
use Riddle\Exception\BadConfigException;
use Riddle\Exception\FileNotFoundException;

class RiddlePageSkeleton
{

    private $app;

    private $head = '';
    private $body = '';
    private $footer = '';
    private $webhookTemplate;

    public function __construct(RiddleApp $app)
    {
        $this->app = $app;

        $this->_generateHead();
        $this->webhookTemplate = $this->app->getConfig()->getProperty('webhookTemplate');
    }

    /**
     * This method renders the whole skeleton and therefore the whole page.
     * 
     * @param $webhookTemplate default: false, pass a different template here if you don't want the default template but use the config if possible
     */
    public function print($webhookTemplate = false)
    {
        global $config;

        if (!$this->webhookTemplate && !$webhookTemplate) {
            throw new BadConfigException('Invalid webhook template - please take a look at your config and check if the property exists.');
        }

        $templatesPath = $this->app->getConfig()->getProperty('templatesPath');
        $webhookTemplate = $webhookTemplate ? $webhookTemplate : $this->webhookTemplate; // take a look at scr/Core/RiddleConfig.php
        $templatePath = $templatesPath . '/' . $webhookTemplate . '.php';

        if (!file_exists($templatePath)) {
            throw new BadConfigException('the template you set does not exist: ' . $webhookTemplate . ' (path: ' . $templatePath . ')');
        }

        ob_start();
        require $templatePath;

        return ob_get_clean();
    }


    private function _generateHead()
    {
        global $rootDir;

        $html = '';
        $this->_addStylesheets($html);
        $this->_addLocalStylesheets($html);

        return ($this->head = $html);

        $stylesheets = $this->app->getConfig()->getProperty('stylesheets');

        if (!$stylesheets) {
            return;
        }

        $html = '';

        foreach ($stylesheets as $stylesheet) {
            if (filter_var($stylesheet, FILTER_VALIDATE_URL)) {
                $html .= '<link rel="stylesheet" href="' . $stylesheet . '"/>';
            } else {
                $stylesheetPath = $rootDir . '/web/css/' . $stylesheet ;

                if (!file_exists($stylesheetPath)) {
                    continue;
                }

                $html .= '<style>' . \file_get_contents($stylesheetPath) . '</style>';
            }
        }

        $this->head = $html;
    }

    private function _addStylesheets(&$html)
    {
        $stylesheets = $this->app->getConfig()->getProperty('stylesheets');

        if (!$stylesheets) {
            return '';
        }

        foreach ($stylesheets as $stylesheet) {
            $html .= '<link rel="stylesheet" href="' . $stylesheet . '"/>';
        }
    }

    private function _addLocalStylesheets(&$html)
    {
        $localStylesheets = $this->app->getConfig()->getProperty('localStylesheets');

        if (!$localStylesheets) {
            return;
        }

        $html .= '<style>';

        foreach ($localStylesheets as $stylesheet) {
            if (!file_exists($stylesheet)) {
                throw new FileNotFoundException('Can\'t find the local stylesheet (path: ' . $stylesheet .')');
            }

            $html .= file_get_contents($stylesheet);
        }

        $html .= '</style>';
    }

    public function dieWithError($error) 
    {
        $this->body = $error;
        $this->print($this->app->getConfig()->getProperty('webhookErrorTemplate'));

        exit();
    }

    public function setWebhookTemplate(string $webhookTemplate)
    {
        $this->webhookTemplate = $webhookTemplate;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function setHead($head) 
    {
        $this->head = $head;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body) 
    {
        $this->body = $body;
    }

    public function getFooter()
    {
        return $this->footer;
    }

    public function setFooter($footer) 
    {
        $this->footer = $footer;
    }

}