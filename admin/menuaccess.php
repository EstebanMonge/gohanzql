<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Menu access administration
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
 * @var array $arrDescription from fieldvars.php -> Translated common strings
 * @var int $intGlobalWriteAccess from prepend_content.php -> Global admin write access
 * @var int $intWriteAccessId from prepend_content.php -> Admin write access to actual dataset id
 * @var int $chkSelValue1 from prepend_content.php -> Language selector
 * @var int $chkSelValue2 from prepend_content.php -> Standard domain selector
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
$prePageId = 34;
$preContent = 'admin/menuaccess.htm.tpl';
$preAccess = 1;
$preFieldvars = 1;
$preNoAccessGrp = 1;
$intFieldId = 0;
$strErrorMessage = '';
$strInfoMessage = '';
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Process data
*/
if (filter_input(INPUT_POST, 'subSave') && ($chkSelValue1 !== 0)) {
    $strSQL = "UPDATE `tbl_menu` SET `mnuGrpId`='$chkSelValue2' WHERE `mnuId`=$chkSelValue1";
    $booReturn = $myDBClass->insertData($strSQL);
    if ($booReturn === false) {
        $myVisClass->processMessage(translate('Error while inserting the data into the database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    } else {
        $myVisClass->processMessage(translate('Data were successfully inserted to the data base!'), $strInfoMessage);
        $myDataClass->writeLog(translate('Access group set for menu item:') . ' ' .
            $myDBClass->getFieldData("SELECT `mnuName` FROM `tbl_menu` WHERE `mnuId`=$chkSelValue1"));
    }
}
/*
Include content
*/
$conttp->setVariable('TITLE', translate('Define Menu Access Rights'));
foreach ($arrDescription as $elem) {
    $conttp->setVariable($elem['name'], $elem['string']);
}
$conttp->setVariable('LANG_ACCESSDESCRIPTION', translate('In order for a user to get access, he needs to be member '
    . 'of the group selected here.'));
/*
Read menu item
*/
$strSQL = 'SELECT A.`mnuId` , B.`mnuName` AS `mainitem`, A.`mnuName` AS `subitem`, A.`mnuGrpId` '
    . 'FROM `tbl_menu` AS A LEFT JOIN `tbl_menu` AS B ON A.`mnuTopId` = B.`mnuId` '
    . 'ORDER BY A.`mnuTopId`, A.`mnuOrderId`';
$booReturn = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
if ($booReturn === false) {
    $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
    $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
} else {
    $conttp->setVariable('SUBMENU_VALUE', '0');
    $conttp->setVariable('SUBMENU_NAME', '&nbsp;');
    $conttp->parse('submenu');
    foreach ($arrDataLines as $elem) {
        $conttp->setVariable('SUBMENU_VALUE', $elem['mnuId']);
        if (($elem['mainitem'] !== null) && ($elem['mainitem'] !== '')) {
            $conttp->setVariable('SUBMENU_NAME', translate($elem['mainitem']) . ' - ' . translate($elem['subitem']));
        } else {
            $conttp->setVariable('SUBMENU_NAME', translate($elem['subitem']));
        }
        if ($chkSelValue1 === (int)$elem['mnuId']) {
            $conttp->setVariable('SUBMENU_SELECTED', 'selected');
            $intFieldId = $elem['mnuGrpId'];
        }
        /* Bypass main site */
        if ((int)$elem['mnuId'] !== 1) {
            $conttp->parse('submenu');
        }
    }
    /* Process access group selection field */
    $intReturn = $myVisClass->parseSelectSimple('tbl_group', 'groupname', 'acc_group', 0, $intFieldId);
}
$conttp->setVariable('ERRORMESSAGE', $strErrorMessage);
$conttp->setVariable('INFOMESSAGE', $strInfoMessage);
/* Check access rights for adding new objects */
if ($intGlobalWriteAccess === 1) {
    $conttp->setVariable('DISABLE_SAVE', 'disabled="disabled"');
}
$conttp->parse('menuaccesssite');
$conttp->show('menuaccesssite');
/*
Process footer
*/
$maintp->setVariable('VERSION_INFO', "<a href='https://sourceforge.net/projects/nagiosql/' "
    . "target='_blank'>NagiosQL</a> $setFileVersion");
$maintp->parse('footer');
$maintp->show('footer');