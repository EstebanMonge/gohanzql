<?php
/* ----------------------------------------------------------------------------
 Gohan ZQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger
 (c) 2026 by Esteban Monge - Sempai Space

 Project   : Gohan ZQL
 Component : Mutation dialog
 Website   : https://github.com/EstebanMonge/gohanzql
 Version   : 4.0.0
 GIT Repo  : https://github.com/EstebanMonge/gohanzql
-----------------------------------------------------------------------------*/

/**
 * Class and variable includes
 * @var functions\NagTemplateClass $conttp Content template
 * @var array $arrDescription from fieldvars.php -> Translated common strings
 * @var int $intVersion from prepend_adm.php -> Nagios version
 */
/*
Path settings
*/
$strPattern = '(admin/[^/]*.php)';
$preRelPath = preg_replace($strPattern, '', filter_input(INPUT_SERVER, 'PHP_SELF'));
$preBasePath = preg_replace($strPattern, '', filter_input(INPUT_SERVER, 'SCRIPT_FILENAME'));
/*
Define common variables
*/
$preContent = 'admin/mutdialog.htm.twig';
/*
Process post parameters
*/
$intExclude = filter_input(INPUT_GET, 'exclude', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
$chkObject = filter_input(INPUT_GET, 'object');
/*
Include preprocessing file
*/
$preAccess = 1;
$preFieldvars = 1;
$preNoMain = 1;
require $preBasePath . 'functions/prepend_adm.php';
/*
Include content
*/
$conttp->setVariable('BASE_PATH', $_SESSION['SETS']['path']['base_url']);
$conttp->setVariable('OPENER_FIELD', $chkObject);
$conttp->parse('header');
$conttp->show('header');
/*
Form
*/
foreach ($arrDescription as $elem) {
    $conttp->setVariable($elem['name'], $elem['string']);
}
$conttp->setVariable('OPENER_FIELD', $chkObject);
$conttp->setVariable('ACTION_INSERT', filter_input(INPUT_SERVER, 'PHP_SELF'));
$conttp->setVariable('IMAGE_PATH', $_SESSION['SETS']['path']['base_url'] . 'images/');
$conttp->setVariable('AVAILABLE', translate('Available'));
$conttp->setVariable('SELECTED', translate('Selected'));
if (($intExclude === 1) || ($intVersion < 3)) {
    $conttp->setVariable('DISABLE_HTML_BEGIN', '<!--');
    $conttp->setVariable('DISABLE_HTML_END', '-->');
}
$conttp->parse('datainsert');
$conttp->show('datainsert');