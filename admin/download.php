<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Download config file
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

use functions\NagConfigClass;
use functions\NagDataClass;

/**
 * Class and variable includes
 * @var NagDataClass $myDataClass NagiosQL data class
 * @var NagConfigClass $myConfigClass NagiosQL configuration class
 */
/*
Path settings
*/
$strPattern = '(admin/[^/]*.php)';
$preRelPath = preg_replace($strPattern, '', filter_input(INPUT_SERVER, 'PHP_SELF'));
$preBasePath = preg_replace($strPattern, '', filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'));
/*
Version control
*/
session_cache_limiter('private_no_expire');
/*
Include preprocessing file
*/
$preNoMain = 1;
$preNoLogin = 1;
require $preBasePath . 'functions/prepend_adm.php';
/*
Process post parameters
*/
$chkTable = filter_input(INPUT_GET, 'table');
$chkConfig = filter_input(INPUT_GET, 'config');
$chkLine = filter_input(INPUT_GET, 'line', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
/*
Header output
*/
$arrConfig = $myConfigClass->getConfData();
if (isset($arrConfig[$chkTable])) {
    $strFile = $arrConfig[$chkTable]['filename'];
} else {
    $strFile = $chkConfig . '.cfg';
}
if ($strFile === '.cfg') {
    exit;
}
header('Content-Disposition: attachment; filename=' . $strFile);
header('Content-Type: text/plain');
/*
Get data
*/
if ($chkLine === 0) {
    $myConfigClass->createConfig($chkTable, 1);
} else {
    $myConfigClass->createConfigSingle($chkTable, $chkLine, 1);
}
$myDataClass->writeLog(translate('Download') . ' ' . $strFile);