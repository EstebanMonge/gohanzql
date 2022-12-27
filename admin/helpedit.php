<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Help text editor
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

use functions\MysqliDbClass;
use functions\NagDataClass;
use functions\NagVisualClass;

/**
 * Class and variable includes
 * @var HTML_Template_IT $conttp Content template
 * @var HTML_Template_IT $maintp Main template
 * @var MysqliDbClass $myDBClass MySQL database class
 * @var NagVisualClass $myVisClass Visual content class
 * @var NagDataClass $myDataClass NagiosQL data class
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 * @var string $prePageKey from prepend_adm.php -> Menu group id
 * @var array $SETS Settings array
 * @var string $chkTfValue1 from prepend_content.php -> (hidden) Stored key value 1
 * @var string $chkTfValue2 from prepend_content.php -> (hidden) Stored key value 2
 * @var string $chkTfValue3 from prepend_content.php -> (hidden) Stored Nagios version
 * @var int $chkChbValue1 from prepend_content.php -> Load standard text
 * @var string $chkTaFileTextRaw from prepend_content.php -> Help text area
 * @var array $arrDescription from fieldvars.php -> Translated common strings
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
$prePageId = 39;
$preContent = 'admin/helpedit.htm.tpl';
$preAccess = 1;
$preFieldvars = 1;
$setSaveLangId = 'private';
$strErrorMessage = '';
$strInfoMessage = '';
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Process post parameters
*/
$chkHidVersion = filter_input(INPUT_POST, 'hidVersion', 513, array('options' => array('default' => 'all')));
$chkKey1 = filter_input(INPUT_POST, 'selInfoKey1', FILTER_DEFAULT, array('options' => array('default' => '')));
$chkKey2 = filter_input(INPUT_POST, 'selInfoKey2', FILTER_DEFAULT, array('options' => array('default' => '')));
$chkVersion = filter_input(INPUT_POST, 'selInfoVersion', FILTER_DEFAULT, array('options' => array('default' => '')));
/*
Security function for text fields
*/
$chkHidVersion = $myVisClass->tfSecure($chkHidVersion);
$chkKey1 = $myVisClass->tfSecure($chkKey1);
$chkKey2 = $myVisClass->tfSecure($chkKey2);
$chkVersion = $myVisClass->tfSecure($chkVersion);
/*
Add or modify data
*/
if (($chkTaFileTextRaw !== '') && ($chkTfValue3 === '1')) {
    $strSQL = "SELECT `infotext` FROM `tbl_info` WHERE `key1`='$chkTfValue1' AND `key2`='$chkTfValue2' "
        . "AND `version`='$chkHidVersion' AND `language`='$setSaveLangId'";
    $booReturn = $myDBClass->hasDataArray($strSQL, $arrData, $intDataCount);
    if ($intDataCount === 0) {
        $strSQL = 'INSERT INTO `tbl_info` (`key1`,`key2`,`version`,`language`,`infotext`) '
            . "VALUES ('$chkTfValue1','$chkTfValue2','$chkHidVersion','$setSaveLangId','$chkTaFileTextRaw')";
    } else {
        $strSQL = "UPDATE `tbl_info` SET `infotext` = '$chkTaFileTextRaw' WHERE `key1` = '$chkTfValue1' "
            . "AND `key2` = '$chkTfValue2' AND `version` = '$chkHidVersion' AND `language` = '$setSaveLangId'";
    }
    $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
    if ($intReturn !== 0) {
        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
    } else {
        $myVisClass->processMessage($myDataClass->strInfoMessage, $strInfoMessage);
    }
}
/*
Singe data form
*/
$conttp->setVariable('TITLE', translate('Help text editor'));
$conttp->setVariable('ACTION_INSERT', filter_input(INPUT_SERVER, 'PHP_SELF'));
$conttp->setVariable('MAINSITE', $_SESSION['SETS']['path']['base_url'] . 'admin.php');
foreach ($arrDescription as $elem) {
    $conttp->setVariable($elem['name'], $elem['string']);
}
$conttp->setVariable('INFOKEY_1', translate('Main key'));
$conttp->setVariable('INFOKEY_2', translate('Sub key'));
$conttp->setVariable('INFO_LANG', translate('Language'));
$conttp->setVariable('INFO_VERSION', translate('Nagios version'));
$conttp->setVariable('LOAD_DEFAULT', translate('Load default text'));
if ($chkChbValue1 === 1) {
    $conttp->setVariable('DEFAULT_CHECKED', 'checked');
}
/*
Get Key
*/
$arrData = array();
$strSQL = 'SELECT DISTINCT `key1` FROM `tbl_info` ORDER BY `key1`';
$booReturn = $myDBClass->hasDataArray($strSQL, $arrData, $intDataCount);
if ($intDataCount !== 0) {
    foreach ($arrData as $elem) {
        $conttp->setVariable('INFOKEY_1_VAL', $elem['key1']);
        if ($chkKey1 === $elem['key1']) {
            $conttp->setVariable('INFOKEY_1_SEL', 'selected');
            $conttp->setVariable('INFOKEY_1_SEL_VAL', $elem['key1']);
        }
        /** @noinspection DisconnectedForeachInstructionInspection */
        $conttp->parse('infokey1');
    }
}
if ($chkKey1 !== '') {
    $strSQL = "SELECT DISTINCT `key2` FROM `tbl_info` WHERE `key1` = '$chkKey1' ORDER BY `key1`";
    $booReturn = $myDBClass->hasDataArray($strSQL, $arrData, $intDataCount);
    if ($intDataCount !== 0) {
        foreach ($arrData as $elem) {
            $conttp->setVariable('INFOKEY_2_VAL', $elem['key2']);
            if ($chkKey2 === $elem['key2']) {
                $conttp->setVariable('INFOKEY_2_SEL', 'selected');
                $conttp->setVariable('INFOKEY_2_SEL_VAL', $elem['key2']);
            }
            /** @noinspection DisconnectedForeachInstructionInspection */
            $conttp->parse('infokey2');
        }
    }
}
if (($chkKey1 !== '') && ($chkKey2 !== '')) {
    $strSQL = 'SELECT DISTINCT `version` FROM `tbl_info` '
        . "WHERE `key1` = '$chkKey1' AND `key2` = '$chkKey2' ORDER BY `version`";
    $booReturn = $myDBClass->hasDataArray($strSQL, $arrData, $intDataCount);
    if ($intDataCount !== 0) {
        if (($intDataCount === 1) && ($chkVersion === '')) {
            $chkVersion = $arrData[0]['version'];
        }
        foreach ($arrData as $elem) {
            $conttp->setVariable('INFOVERSION_2_VAL', $elem['version']);
            if ($chkVersion === $elem['version']) {
                $conttp->setVariable('INFOVERSION_2_SEL', 'selected');
                $conttp->setVariable('INFOVERSION_2_SEL_VAL', $elem['version']);
            }
            /** @noinspection DisconnectedForeachInstructionInspection */
            $conttp->parse('infoversion');
        }
    }
}
/*
Insert content
*/
if (($chkKey1 !== '') && ($chkKey2 !== '') && ($chkVersion !== '')) {
    $strSQL = "SELECT `infotext` FROM `tbl_info` WHERE `key1`='$chkKey1' AND `key2`='$chkKey2' "
        . "AND `version`='$chkVersion' AND `language`='$setSaveLangId'";
    $strContentDB = $myDBClass->getFieldData($strSQL);
    if (($chkChbValue1 === 1) || ($strContentDB === '')) {
        $strSQL = "SELECT `infotext` FROM `tbl_info` WHERE `key1`='$chkKey1' AND `key2`='$chkKey2' "
            . "AND `version`='$chkVersion' AND `language`='default'";
        $strContentDB = $myDBClass->getFieldData($strSQL);
    }
    $conttp->setVariable('DAT_HELPTEXT', $strContentDB);
}
/* Messages */
if ($strErrorMessage !== '') {
    $conttp->setVariable('ERRORMESSAGE', $strErrorMessage);
}
if ($strInfoMessage !== '') {
    $conttp->setVariable('INFOMESSAGE', $strInfoMessage);
}
/* Check access rights for adding new objects */
if ($myVisClass->checkAccountGroup($prePageKey, 'write') !== 0) {
    $conttp->setVariable('ADD_CONTROL', 'disabled="disabled"');
}
$conttp->parse('helpedit');
$conttp->show('helpedit');
/*
Process footer
*/
$maintp->setVariable('VERSION_INFO', "<a href='https://sourceforge.net/projects/nagiosql/' "
    . "target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse('footer');
$maintp->show('footer');