<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

// GET Route: Display the attendance page
$app->router("/manager/attendance", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Chấm công");

    echo $app->render('templates/home.html', $vars);
})->setPermissions(['attendance']);

