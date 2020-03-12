<?php

$config = [
    /**
     * Secret of your riddle extension.
     * DON'T share this secret. You only have to enter this secret along with the rest of the URL as a riddle custom landing page.
     * 
     * It's necessary to generate a new secret. Use this link to get one in 10 seconds:
     * https://www.random.org/strings/?num=1&len=16&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new
     */
    'secret' => false,
    'renderView' => 'landingpage',
    'renderNoDataView' => 'landingpage-skipped',
    'leaderboardLength' => 100000,

    'templatesPath' => APP_DIR . '/templates',
    'viewsPath' => APP_DIR . '/views',
    'renderedViewsPath' => APP_DIR . '/rendered-views',
    'dataPath' => APP_DIR . '/data',
    'styleSheetsPath' => WEB_DIR . '/css',
    'leadKey' => 'Email',

    /**
     * Which css stylesheets do you want to use?
     * In this case we use Bootstrap 4 + our own stylesheet with small tweaks.
     * 
     * Drop a file into the web/css directory to use it here or add an URL (=> e.g. Bootstrap CDN)
     */
    'stylesheets' => [
        'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css', 
        'https://fonts.googleapis.com/css?family=Montserrat:400,700&display=swap', // the font we're using
    ],

    'localStylesheets' => [
        WEB_DIR . '/css/riddle-webhook.css',
    ],
    
    'webhookTemplate' => 'riddle-webhook-template',
];