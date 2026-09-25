<?php
/* ----------------------------------------------------------------------------
 Gohan ZQL
-------------------------------------------------------------------------------
 (c) 2005-2022 by Martin Willisegger
 (c) 2026 by Esteban Monge - Sempai Space

 Project   : Gohan ZQL
 Component : Start script
 Website   : https://github.com/EstebanMonge/gohanzql
 Version   : 4.0.0
 GIT Repo  : https://github.com/EstebanMonge/gohanzql
-----------------------------------------------------------------------------*/
/**
 * Class and variable includes
 * @var functions\NagTemplateClass $conttp
 * @var functions\NagTemplateClass $maintp
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 */
/*
Path settings
*/
$preRelPath = strstr(filter_input(INPUT_SERVER, 'PHP_SELF'), 'index.php', true);
$preBasePath = strstr(filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'), 'index.php', true);
/*
Destroy old session data
*/
session_start();
session_destroy();
/*
Define common variables
*/
$intPageID = 0;
$preContent = 'index.htm.twig';
/*
Redirect to installation wizard
*/
if (PHP_VERSION_ID < 70200) {
    header('Location: install/index.php');
}
/*
Include preprocessing file
*/
$preAccess = 0;
$preFieldvars = 0;
require 'functions/prepend_adm.php';
/*
Include Content
*/
$conttp->setVariable('TITLE', translate('Welcome to'));
$conttp->setVariable('TITLE_LOGIN', translate('Welcome'));
$conttp->setVariable('LOGIN_TEXT', translate('Please enter your username and password to access Gohan ZQL.<br>If '
    . 'you forgot one of them, please contact your Administrator.'));
$conttp->setVariable('USERNAME', translate('Username'));
$conttp->setVariable('PASSWORD', translate('Password'));
$conttp->setVariable('LOGIN', translate('Login'));
if (isset($_SESSION['strLoginMessage']) && ($_SESSION['strLoginMessage'] !== '')) {
    $conttp->setVariable('MESSAGE', $_SESSION['strLoginMessage']);
} else {
    $conttp->setVariable('MESSAGE', '&nbsp;');
}
$conttp->setVariable('ACTION_INSERT', filter_input(INPUT_SERVER, 'PHP_SELF'));
$conttp->setVariable('IMAGE_PATH', 'images/');
$conttp->parse('main');
$conttp->show('main');
/*
Include footer
*/
$maintp->setVariable('VERSION_INFO', "<a href='https://github.com/EstebanMonge/gohanzql' target='_blank'>Gohan ZQL</a> $setFileVersion");
$maintp->parse('footer');
$maintp->show('footer');