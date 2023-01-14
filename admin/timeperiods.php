<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Timeperiod definitions
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
 * @var int $intVersion from prepend_adm.php -> Nagios version
 * @var int $chkListId from prepend_adm.php -> Actual dataset id (list view)
 * @var array $SETS Settings array
 * @var int $intGlobalWriteAccess from prepend_content.php -> Global admin write access
 * @var int $intWriteAccessId from prepend_content.php -> Admin write access to actual dataset id
 * @var string $strAccess from prepend_content.php -> List of read access group id's for actual user
 * @var string $preSQLCommon1 from prepend_content.php -> Common SQL part 1
 * @var string $strDomainWhere from prepend_adm.php -> Domain selection SQL part with table name
 * @var string $chkTfValue1 from prepend_content.php -> Time period name
 * @var string $chkTfValue2 from prepend_content.php -> Time period description
 * @var string $chkTfValue3 from prepend_content.php -> Time period template name
 * @var array $chkMselValue1 from prepend_content.php -> Excluded time period
 * @var array $chkMselValue2 from prepend_content.php -> Included time period
 * @var int $intMselValue1 from prepend_content.php -> Excluded time period multiselect status value
 * @var int $intMselValue2 from prepend_content.php -> Included time period multiselect status value
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
$prePageId = 16;
$preContent = 'admin/timeperiods.htm.tpl';
$preListTpl = 'admin/datalist.htm.tpl';
$preSearchSession = 'timeperiod';
$preTableName = 'tbl_timeperiod';
$preKeyField = 'timeperiod_name';
$preAccess = 1;
$preFieldvars = 1;
$intNoTime = 0;
$strErrorMessage = '';
$strInfoMessage = '';
$strConsistMessage = '';
/*
 * Default values for form variables
*/
if (!isset($intMselValue1)) {
    $intMselValue1 = 0;
}
if (!isset($intMselValue2)) {
    $intMselValue2 = 0;
}
/*
Include preprocessing files
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Add or modify data
*/
if ((($chkModus === 'insert') || ($chkModus === 'modify')) && ($intGlobalWriteAccess === 0)) {
    $strSQLx = "`$preTableName` SET `$preKeyField`='$chkTfValue1', `alias`='$chkTfValue2', `exclude`=$intMselValue1, "
        . "`use_template`=$intMselValue2, `name`='$chkTfValue3', $preSQLCommon1";
    if ($chkModus === 'insert') {
        $strSQL = 'INSERT INTO ' . $strSQLx;
    } else {
        $strSQL = 'UPDATE ' . $strSQLx . ' WHERE `id`=' . $chkDataId;
    }
    if ($intWriteAccessId === 0) {
        if (($chkTfValue1 !== '') && ($chkTfValue2 !== '')) {
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
                    $myDataClass->writeLog(translate('New time period inserted:') . ' ' . $chkTfValue1);
                }
                if ($chkModus === 'modify') {
                    $myDataClass->writeLog(translate('Time period modified:') . ' ' . $chkTfValue1);
                }
                /*
                Insert/update relations
                */
                $intRet1 = 0;
                $intRet2 = 0;
                if ($chkModus === 'insert') {
                    if ($intMselValue1 !== 0) {
                        $intRet1 = $myDataClass->dataInsertRelation(
                            'tbl_lnkTimeperiodToTimeperiod',
                            $chkDataId,
                            $chkMselValue1
                        );
                    }
                    if (isset($intRet1) && ($intRet1 !== 0)) {
                        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                    }
                    if ($intMselValue2 !== 0) {
                        $intRet2 = $myDataClass->dataInsertRelation(
                            'tbl_lnkTimeperiodToTimeperiodUse',
                            $chkDataId,
                            $chkMselValue2
                        );
                    }
                    if (isset($intRet2) && ($intRet2 !== 0)) {
                        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                    }
                } elseif ($chkModus === 'modify') {
                    if ($intMselValue1 !== 0) {
                        $intRet1 = $myDataClass->dataUpdateRelation(
                            'tbl_lnkTimeperiodToTimeperiod',
                            $chkDataId,
                            $chkMselValue1
                        );
                    } else {
                        $intRet1 = $myDataClass->dataDeleteRelation('tbl_lnkTimeperiodToTimeperiod', $chkDataId);
                    }
                    if ($intRet1 !== 0) {
                        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                    }
                    if ($intMselValue2 !== 0) {
                        $intRet2 = $myDataClass->dataUpdateRelation(
                            'tbl_lnkTimeperiodToTimeperiodUse',
                            $chkDataId,
                            $chkMselValue2
                        );
                    } else {
                        $intRet2 = $myDataClass->dataDeleteRelation('tbl_lnkTimeperiodToTimeperiodUse', $chkDataId);
                    }
                    if ($intRet2 !== 0) {
                        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                    }
                }
                if (($intRet1 + $intRet2) !== 0) {
                    $strInfoMessage = '';
                }
                /*
                Insert/update time defintions
                */
                if ($chkModus === 'modify') {
                    $strSQL = "DELETE FROM `tbl_timedefinition` WHERE `tipId`=$chkDataId";
                    $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
                    if ($intReturn === 1) {
                        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                    }
                }
                if (isset($_SESSION['timedefinition']) && is_array($_SESSION['timedefinition']) &&
                    (count($_SESSION['timedefinition']) !== 0)) {
                    foreach ($_SESSION['timedefinition'] as $elem) {
                        if ((int)$elem['status'] === 0) {
                            if ($elem['definition'] !== 'use') {
                                $elem['range'] = str_replace(' ', '', $elem['range']);
                            }
                            $strSQL = 'INSERT INTO `tbl_timedefinition` (`tipId`,`definition`,`range`, '
                                . "`last_modified`) VALUES ($chkDataId,'" . $elem['definition'] . "',"
                                . "'" . $elem['range'] . "',now())";
                            $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
                            if ($intReturn === 1) {
                                $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                            }
                        }
                    }
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
if ($chkModus !== 'add') {
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
if ($chkModus === 'add') {
    $intDataWarning = 0;
    $strDBWarning = '';
    $conttp->setVariable('TITLE', translate('Time period definitions (timeperiods.cfg)'));
    /* Do not show modified time list */
    $intNoTime = 1;
    /* Process exclude selection fields */
    $intFieldId = $arrModifyData['exclude'] ?? 0;
    $intReturn1 = $myVisClass->parseSelectMulti(
        $preTableName,
        'timeperiod_name',
        'excludes',
        'tbl_lnkTimeperiodToTimeperiod',
        0,
        $intFieldId,
        $chkListId
    );
    if ($intReturn1 !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
    }
    /* Process include selection fields */
    $intFieldId = $arrModifyData['use_template'] ?? 0;
    $intReturn2 = $myVisClass->parseSelectMulti(
        $preTableName,
        'name',
        'uses',
        'tbl_lnkTimeperiodToTimeperiodUse',
        0,
        $intFieldId,
        $chkListId
    );
    if ($intReturn2 !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
    }
    /* Process acces group selection field */
    $intFieldId = $arrModifyData['access_group'] ?? 0;
    $intReturn3 = $myVisClass->parseSelectSimple('tbl_group', 'groupname', 'acc_group', 0, $intFieldId);
    if ($intReturn3 !== 0) {
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
    $conttp->setVariable('LANG_INSERT_ALL_TIMERANGE', translate('Please insert a time definition and a time range'));
    /* Insert data from database in "modify" mode */
    if (isset($arrModifyData) && ($chkSelModify === 'modify')) {
        /* Check relation information to find out locked configuration datasets */
        $intLocked = $myDataClass->infoRelation($preTableName, $arrModifyData['id'], $preKeyField);
        $myVisClass->processMessage($myDataClass->strInfoMessage, $strRelMessage);
        $strInfo = '<br><span class="redmessage">' . translate('Entry cannot be activated because it is used by '
                . 'another configuration') . ':</span>';
        $strInfo .= '<br><span class="greenmessage">' . $strRelMessage . '</span>';
        /* Process data */
        $myContentClass->addInsertData($conttp, $arrModifyData, $intLocked, $strInfo);
        $conttp->setVariable('TIP_ID', $arrModifyData['id']);
    }
    $conttp->parse('datainsert');
    $conttp->show('datainsert');
}
/*
List view
*/
if ($chkModus === 'display') {
    $strSearchWhere = '';
    // Initial list view definitions */
    $myContentClass->listViewInit($mastertp);
    $mastertp->setVariable('TITLE', translate('Time period definitions (timeperiods.cfg)'));
    $mastertp->setVariable('FIELD_1', translate('Time period'));
    $mastertp->setVariable('FIELD_2', translate('Description'));
    $mastertp->setVariable('FILTER_VISIBLE', 'visibility: hidden');
    /* Process filter string */
    if ($_SESSION['search'][$preSearchSession] !== '') {
        $strSearchTxt = $_SESSION['search'][$preSearchSession];
        $strSearchWhere = "AND (`$preKeyField` LIKE '%" . $strSearchTxt . "%' OR `alias` LIKE '%" . $strSearchTxt . "%' "
            . "OR `name` LIKE '%" . $strSearchTxt . "%')";
    }
    /* Row sorting */
    $strOrderString = "ORDER BY `config_id`, `$preKeyField` $hidSortDir";
    if ($hidSortBy === 2) {
        $strOrderString = "ORDER BY `config_id`, `alias` $hidSortDir";
    }
    /* Count datasets */
    $intLineCount = 0;
    /** @noinspection SqlResolve */
    $strSQL = "SELECT count(*) AS `number` FROM `$preTableName` WHERE $strDomainWhere $strSearchWhere "
        . "AND `access_group` IN ($strAccess)";
    $booReturn1 = $myDBClass->hasSingleDataset($strSQL, $arrDataLinesCount);
    if ($booReturn1 === false) {
        $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    } else {
        $intLineCount = (int)$arrDataLinesCount['number'];
        if ($intLineCount < $chkLimit) {
            $chkLimit = 0;
        }
    }
    /* Get datasets */
    $strSQL = "SELECT `id`, `$preKeyField`, `alias`, `register`, `active`, `config_id`, `access_group` "
        . "FROM `$preTableName` WHERE $strDomainWhere $strSearchWhere AND `access_group` IN ($strAccess) "
        . "$strOrderString LIMIT $chkLimit," . $SETS['common']['pagelines'];
    $booReturn2 = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
    if ($booReturn2 === false) {
        $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    }
    /* Process data */
    $myContentClass->listData($mastertp, $arrDataLines, $intDataCount, $intLineCount, $preKeyField, 'alias');
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