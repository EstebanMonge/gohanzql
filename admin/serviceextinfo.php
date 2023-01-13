<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Service extended information definition
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

use functions\MysqliDbClass;
use functions\NagConfigClass;
use functions\NagContentClass;
use functions\NagDataClass;
use functions\NagVisualClass;

/**
 * Class and variable includes
 * @var HTML_Template_IT $conttp Content template
 * @var HTML_Template_IT $maintp Main template
 * @var HTML_Template_IT $mastertp Master template (list view)
 * @var MysqliDbClass $myDBClass MySQL database class
 * @var NagVisualClass $myVisClass Visual content class
 * @var NagDataClass $myDataClass NagiosQL data class
 * @var NagContentClass $myContentClass NagiosQL content class
 * @var NagConfigClass $myConfigClass NagiosQL configuration class
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 * @var string $chkModus from prepend_adm.php -> Form work mode
 * @var int $chkDataId from prepend_adm.php -> Actual dataset id
 * @var string $chkSelModify from prepend_adm.php -> Modification selection value
 * @var int $hidSortBy from prepend_adm.php -> Sort data by
 * @var string $hidSortDir from prepend_adm.php -> Sort data direction (ASC, DESC)
 * @var int $chkLimit from prepend_adm.php / settings -> Data set count per page
 * @var int $chkDomainId from prepend_adm.php -> Configuration domain id
 * @var int $intVersion from prepend_adm.php -> Nagios version
 * @var array $SETS Settings array
 * @var int $intGlobalWriteAccess from prepend_content.php -> Global admin write access
 * @var int $intWriteAccessId from prepend_content.php -> Admin write access to actual dataset id
 * @var string $strAccess from prepend_content.php -> List of read access group id's for actual user
 * @var string $preSQLCommon1 from prepend_content.php -> Common SQL part 1
 * @var string $strSearchWhere from prepend_content.php -> SQL WHERE addon for data search
 * @var string $strDomainWhere from prepend_adm.php -> Domain selection SQL part with table name
 * @var string $chkTfValue1 from prepend_content.php -> Notes
 * @var string $chkTfValue2 from prepend_content.php -> Notes URL
 * @var string $chkTfValue3 from prepend_content.php -> Action URL
 * @var string $chkTfValue4 from prepend_content.php -> Icon image
 * @var string $chkTfValue5 from prepend_content.php -> Icon image alt text
 * @var int $chkSelValue1 from prepend_content.php -> Host
 * @var int $chkSelValue2 from prepend_content.php -> Service description
 * @var int $chkActive from prepend_adm.php -> Active checkbox
 * @var int $chkRegister from prepend_adm.php -> Register checkbox
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
$prePageId = 24;
$preContent = 'admin/serviceextinfo.htm.tpl';
$preListTpl = 'admin/datalist.htm.tpl';
$preSearchSession = 'serviceextinfo';
$preTableName = 'tbl_serviceextinfo';
$preKeyField = 'host_name';
$preAccess = 1;
$preFieldvars = 1;
$strErrorMessage = '';
$strInfoMessage = '';
$strConsistMessage = '';
$strDBWarning = '';
$intDataWarning = 0;
$intNoTime = 0;
/*
Include preprocessing files
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Add or modify data
*/
if ((($chkModus === 'insert') || ($chkModus === 'modify')) && ($intGlobalWriteAccess === 0)) {
    $strSQLx = "`$preTableName` SET `$preKeyField`='$chkSelValue1', `service_description`='$chkSelValue2', "
        . "`notes`='$chkTfValue1', `notes_url`='$chkTfValue2', `action_url`='$chkTfValue3', "
        . "`icon_image`='$chkTfValue4', `icon_image_alt`='$chkTfValue5', $preSQLCommon1";
    if ($chkModus === 'insert') {
        $strSQL = 'INSERT INTO ' . $strSQLx;
    } else {
        $strSQL = 'UPDATE ' . $strSQLx . ' WHERE `id`=' . $chkDataId;
    }
    if ($intWriteAccessId === 0) {
        if (($chkSelValue1 !== 0) && ($chkSelValue2 !== 0)) {
            $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
            if ($chkModus === 'insert') {
                $chkDataId = $intInsertId;
            }
            if ($intReturn === 1) {
                $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
            } else {
                $myVisClass->processMessage($myDataClass->strInfoMessage, $strInfoMessage);
                $myDataClass->updateStatusTable($preTableName);
                if ($chkModus === 'insert') {
                    $myDataClass->writeLog(translate('New service extended information inserted:') . ' ' . $chkSelValue1 .
                        '::' . $chkSelValue2);
                }
                if ($chkModus === 'modify') {
                    $myDataClass->writeLog(translate('Service extended information modified:') . ' ' . $chkSelValue1 .
                        '::' . $chkSelValue2);
                }
                /*
                Update Import HASH
                */
                $booReturn = $myDataClass->updateHash($preTableName, $chkDataId);
                if ($booReturn !== 0) {
                    $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                }
            }
        } else {
            $myVisClass->processMessage(
                translate('Database entry failed! Not all necessary data filled in!'),
                $strErrorMessage
            );
        }
    } else {
        $myVisClass->processMessage(translate('Database entry failed! No write access!'), $strErrorMessage);
    }
    $chkModus = 'display';
}
if (($chkModus !== 'add') && ($chkModus !== 'refresh')) {
    $chkModus = 'display';
}
/*
Get date/time of last database and config file manipulation
*/
$intReturn = $myConfigClass->lastModifiedFile($preTableName, $arrTimeData, $strTimeInfoString);
if ($intReturn !== 0) {
    $myVisClass->processMessage($myConfigClass->strErrorMessage, $strErrorMessage);
}
/*
Singe data form
*/
if (($chkModus === 'add') || ($chkModus === 'refresh')) {
    $conttp->setVariable('TITLE', translate('Define service extended information (serviceextinfo.cfg)'));
    /* Do not show modified time list */
    $intNoTime = 1;
    /* Refresh mode */
    $_SESSION['refresh']['se_host'] = $chkSelValue1;
    if ($chkModus !== 'refresh') {
        if (isset($arrModifyData[$preKeyField]) && ($arrModifyData[$preKeyField] !== 0)) {
            /** @noinspection SqlResolve */
            $strSQL = "SELECT `$preKeyField` FROM `$preTableName` WHERE `id` = " . $arrModifyData['id'];
            $booReturn = $myDBClass->hasDataArray($strSQL, $arrData, $intDC);
            if ($intDC !== 0) {
                $_SESSION['refresh']['se_host'] = $arrData[0][$preKeyField];
            }
        } else {
            /** @noinspection SqlResolve */
            $strSQL = 'SELECT `id` FROM `tbl_host` '
                . "WHERE `active`='1' AND `config_id`=$chkDomainId ORDER BY `$preKeyField`";
            $booReturn = $myDBClass->hasDataArray($strSQL, $arrData, $intDC);
            if ($intDC !== 0) {
                $_SESSION['refresh']['se_host'] = $arrData[0]['id'];
            }
        }
    }
    $myVisClass->arrSession = $_SESSION;
    /* Process host selection field */
    $intFieldId = $arrModifyData[$preKeyField] ?? 0;
    if (($chkModus === 'refresh') && ($chkSelValue1 !== 0)) {
        $intFieldId = $chkSelValue1;
    }
    $intReturn1 = $myVisClass->parseSelectSimple('tbl_host', $preKeyField, 'host', 0, $intFieldId);
    if ($intReturn1 !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
        $myVisClass->processMessage(translate('Attention, no hosts defined!'), $strDBWarning);
        $intDataWarning = 1;
    }
    /* Process service selection field */
    $intFieldId = $arrModifyData['service_description'] ?? 0;
    $intReturn1 = $myVisClass->parseSelectSimple(
        'tbl_service',
        'service_description',
        'service_extinfo',
        0,
        $intFieldId
    );
    if ($intReturn1 !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
    }
    /* Process access group selection field */
    $intFieldId = $arrModifyData['access_group'] ?? 0;
    $intReturn = $myVisClass->parseSelectSimple('tbl_group', 'groupname', 'acc_group', 0, $intFieldId);
    if ($intReturn !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
    }
    /* Initial add/modify form definitions */
    $myContentClass->addFormInit($conttp);
    if ($intDataWarning === 1) {
        $conttp->setVariable('WARNING', $strDBWarning . '<br>' . translate('Saving not possible!'));
    }
    if ($intVersion < 3) {
        $conttp->setVariable('VERSION_20_VALUE_MUST', 'mselValue1,');
    }
    if ($chkModus === 'refresh') {
        $conttp->setVariable('DAT_NOTES', $chkTfValue1);
        $conttp->setVariable('DAT_NOTES_URL', $chkTfValue2);
        $conttp->setVariable('DAT_ACTION_URL', $chkTfValue3);
        $conttp->setVariable('DAT_ICON_IMAGE', $chkTfValue4);
        $conttp->setVariable('DAT_ICON_IMAGE_ALT', $chkTfValue5);
        if ($chkActive !== 1) {
            $conttp->setVariable('ACT_CHECKED');
        }
        if ($chkRegister !== 1) {
            $conttp->setVariable('REG_CHECKED');
        }
        if ($chkDataId !== 0) {
            $conttp->setVariable('MODUS', 'modify');
            $conttp->setVariable('DAT_ID', $chkDataId);
        }
        /* Insert data from database in "modify" mode */
    } elseif (isset($arrModifyData) && ($chkSelModify === 'modify')) {
        /* Check relation information to find out locked configuration datasets */
        $intLocked = $myDataClass->infoRelation($preTableName, $arrModifyData['id'], $preKeyField);
        $myVisClass->processMessage($myDataClass->strInfoMessage, $strRelMessage);
        $strInfo = '<br><span class="redmessage">' . translate('Entry cannot be activated because it is used by '
                . 'another configuration') . ':</span>';
        $strInfo .= '<br><span class="greenmessage">' . $strRelMessage . '</span>';
        /* Process data */
        $myContentClass->addInsertData($conttp, $arrModifyData, $intLocked, $strInfo);
    }
    $conttp->parse('datainsert');
    $conttp->show('datainsert');
}
/*
List view
*/
if ($chkModus === 'display') {
    /* Initial list view definitions */
    $myContentClass->listViewInit($mastertp);
    $mastertp->setVariable('TITLE', translate('Define service extended information (serviceextinfo.cfg)'));
    $mastertp->setVariable('FIELD_1', translate('Hostname'));
    $mastertp->setVariable('FIELD_2', translate('Service'));
    $mastertp->setVariable('FILTER_VISIBLE', 'visibility: hidden');
    /* Process search string */
    if ($_SESSION['search'][$preSearchSession] !== '') {
        $strSearchTxt = $_SESSION['search'][$preSearchSession];
        $strSearchWhere = "AND (`tbl_host`.`$preKeyField` LIKE '%" . $strSearchTxt . "%' OR `$preTableName`.`notes` "
            . "LIKE '%" . $strSearchTxt . "%' OR `$preTableName`.`notes_url` LIKE '%" . $strSearchTxt . "%')";
    }
    /* Row sorting */
    $strOrderString = "ORDER BY `$preTableName`.`config_id`, `$preKeyField` $hidSortDir";
    if ($hidSortBy === 2) {
        $strOrderString = "ORDER BY `$preTableName`.`config_id`, `tbl_service`.`service_description` $hidSortDir";
    }
    /* Count datasets */
    $intLineCount = 0;
    /** @noinspection SqlResolve */
    $strSQL = "SELECT count(*) AS `number` FROM `$preTableName` "
        . "LEFT JOIN `tbl_host` ON `$preTableName`.`$preKeyField` = `tbl_host`.`id` "
        . "LEFT JOIN `tbl_service` ON `$preTableName`.`service_description` = `tbl_service`.`id` "
        . "WHERE $strDomainWhere $strSearchWhere AND `$preTableName`.`access_group` IN ($strAccess)";
    $booReturn = $myDBClass->hasSingleDataset($strSQL, $arrDataLinesCount);
    if ($booReturn === false) {
        $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    } else {
        $intLineCount = (int)$arrDataLinesCount['number'];
        if ($intLineCount < $chkLimit) {
            $chkLimit = 0;
        }
    }
    /* Get datasets */
    $strSQL = "SELECT `$preTableName`.`id`, `tbl_host`.`$preKeyField`, `tbl_service`.`service_description`, "
        . "`$preTableName`.`notes`, `$preTableName`.`register`, `$preTableName`.`active`, `$preTableName`.`config_id`, "
        . "`$preTableName`.`access_group` FROM `$preTableName` "
        . "LEFT JOIN `tbl_host` ON `$preTableName`.`$preKeyField` = `tbl_host`.`id` "
        . "LEFT JOIN `tbl_service` ON `$preTableName`.`service_description` = `tbl_service`.`id` "
        . "WHERE $strDomainWhere $strSearchWhere AND `$preTableName`.`access_group` IN ($strAccess) $strOrderString "
        . "LIMIT $chkLimit," . $SETS['common']['pagelines'];
    $booReturn = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
    if ($booReturn === false) {
        $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    }
    /* Process data */
    $myContentClass->listData(
        $mastertp,
        $arrDataLines,
        $intDataCount,
        $intLineCount,
        $preKeyField,
        'service_description'
    );
}
/* Show messages */
$myContentClass->showMessages(
    $mastertp,
    $strErrorMessage,
    $strInfoMessage,
    $strConsistMessage,
    $arrTimeData,
    $strTimeInfoString,
    $intNoTime
);
/*
Process footer
*/
$myContentClass->showFooter($maintp, $setFileVersion);