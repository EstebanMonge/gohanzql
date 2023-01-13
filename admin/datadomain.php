<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Data domain administration
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

use functions\MysqliDbClass;
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
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 * @var int $chkActive from prepend_adm.php -> Active checkbox
 * @var string $chkModus from prepend_adm.php -> Form work mode
 * @var int $chkDataId from prepend_adm.php -> Actual dataset id
 * @var string $chkSelModify from prepend_adm.php -> Modification selection value
 * @var int $hidSortBy from prepend_adm.php -> Sort data by
 * @var string $hidSortDir from prepend_adm.php -> Sort data direction (ASC, DESC)
 * @var int $chkLimit from prepend_adm.php / settings -> Data set count per page
 * @var array $SETS Settings array
 * @var int $intGlobalWriteAccess from prepend_content.php -> Global admin write access
 * @var int $intWriteAccessId from prepend_content.php -> Admin write access to actual dataset id
 * @var string $strAccess from prepend_content.php -> List of read access group id's for actual user
 * @var string $chkTfValue1 from prepend_content.php -> Domain name
 * @var string $chkTfValue2 from prepend_content.php -> Domain description
 * @var int $chkSelValue1 from prepend_content.php -> Configuration target domain
 * @var string $chkSelValue2 from prepend_content.php -> Nagios version
 * @var string $chkSelValue3 from prepend_content.php -> Use common domain selection
 * @var string $chkSelAccGr from prepend_content.php -> Access group selector
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
$prePageId = 35;
$preContent = 'admin/datadomain.htm.tpl';
$preListTpl = 'admin/datalist_common.htm.tpl';
$preTableName = 'tbl_datadomain';
$preKeyField = 'domain';
$preAccess = 1;
$preFieldvars = 1;
$strErrorMessage = '';
$strInfoMessage = '';
$strConsistMessage = '';
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
require $preBasePath . 'functions/prepend_content.php';
/*
Add or modify data
*/
if ((($chkModus === 'insert') || ($chkModus === 'modify')) && ($intGlobalWriteAccess === 0)) {
    if ($chkTfValue1 === 'common') {
        $chkSelValue1 = 0;
    }
    $strSQLx = "`$preTableName` SET `$preKeyField`='$chkTfValue1', `alias`='$chkTfValue2', `targets`=$chkSelValue1, "
        . "`version`=$chkSelValue2, `access_group`=$chkSelAccGr, `enable_common`=$chkSelValue3, "
        . "`active`='$chkActive', `last_modified`=NOW()";
    if ($chkModus === 'insert') {
        $strSQL = 'INSERT INTO ' . $strSQLx;
    } else {
        $strSQL = 'UPDATE ' . $strSQLx . ' WHERE `id`=' . $chkDataId;
    }
    if ($intWriteAccessId === 0) {
        if (($chkTfValue1 !== '') && ($chkTfValue2 !== '') && (($chkTfValue1 === 'common') || ($chkSelValue1 !== 0))) {
            $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
            if ($chkModus === 'insert') {
                $chkDataId = $intInsertId;
            }
            if ($intReturn === 1) {
                $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
            } else {
                $myVisClass->processMessage($myDataClass->strInfoMessage, $strInfoMessage);
                if ($chkModus === 'insert') {
                    $myDataClass->writeLog(translate('New Domain inserted:') . ' ' . $chkTfValue1);
                }
                if ($chkModus === 'modify') {
                    $myDataClass->writeLog(translate('Domain modified:') . ' ' . $chkTfValue1);
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
Single view
*/
if ($chkModus === 'add') {
    $intDataWarning = 0;
    $strDBWarning = '';
    /* Process configuration target selection fields */
    $intFieldId = $arrModifyData['targets'] ?? 0;
    $intReturn1 = $myVisClass->parseSelectSimple('tbl_configtarget', 'target', 'target', 0, $intFieldId);
    if ($intReturn1 !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
        $myVisClass->processMessage(translate('Attention, no configuration targets defined!'), $strDBWarning);
        $intDataWarning = 1;
    }
    /* Process acces group selection field */
    $intFieldId = $arrModifyData['access_group'] ?? 0;
    $intReturn2 = $myVisClass->parseSelectSimple('tbl_group', 'groupname', 'acc_group', 0, $intFieldId);
    if ($intReturn2 !== 0) {
        $myVisClass->processMessage($myVisClass->strErrorMessage, $strErrorMessage);
    }
    /* Initial add/modify form definitions */
    $myContentClass->addFormInit($conttp);
    if ($intDataWarning === 1) {
        $conttp->setVariable('WARNING', $strDBWarning . '<br>' . translate('Saving not possible!'));
    }
    $conttp->setVariable('TITLE', translate('Data domain administration'));
    $conttp->setVariable('FILL_ALLFIELDS', translate('Please fill in all fields marked with an *'));
    $conttp->setVariable('FILL_ILLEGALCHARS', translate('The following field contains illegal characters:'));
    $conttp->setVariable('ENABLE', translate('Enable'));
    $conttp->setVariable('DISABLE', translate('Disable'));
    /* Insert data from database in "modify" mode */
    if (isset($arrModifyData) && ($chkSelModify === 'modify')) {
        /* Process data */
        $myContentClass->addInsertData($conttp, $arrModifyData, 0, '');
        /* Nagios version */
        $conttp->setVariable('VER_SELECTED_' . $arrModifyData['version'], 'selected');
        /* Enable common domain */
        $conttp->setVariable('ENA_COMMON_SELECTED_' . $arrModifyData['enable_common'], 'selected');
        /* Domain localhost cannot be renamed */
        if ($arrModifyData['domain'] === 'localhost') {
            $conttp->setVariable('DOMAIN_DISABLE', 'readonly');
            $conttp->setVariable('LOCKCLASS', 'inputlock');
        } elseif ($arrModifyData['domain'] === 'common') {
            $conttp->setVariable('DOMAIN_DISABLE', 'readonly');
            $conttp->setVariable('COMMON_INVISIBLE', 'class="elementHide"');
            $conttp->setVariable('LOCKCLASS', 'inputlock');
        } else {
            $conttp->setVariable('CHECK_TARGETS', ',selValue1');
        }
    }
    $conttp->parse('datainsert');
    $conttp->show('datainsert');
}
/*
List view
*/
if ($chkModus === 'display') {
    $intLineCount = 0;
    /* Initial list view definitions */
    $myContentClass->listViewInit($mastertp);
    $mastertp->setVariable('TITLE', translate('Data domain administration'));
    $mastertp->setVariable('FIELD_1', translate('Data domain'));
    $mastertp->setVariable('FIELD_2', translate('Description'));
    /* Row sorting */
    $strOrderString = "ORDER BY `domain` $hidSortDir";
    if ($hidSortBy === 2) {
        $strOrderString = "ORDER BY `alias` $hidSortDir";
    }
    /* Count datasets */
    /** @noinspection SqlResolve */
    $strSQL = "SELECT count(*) AS `number` FROM `$preTableName` WHERE `access_group` IN ($strAccess)";
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
    $strSQL = 'SELECT `id`, `domain`, `alias`, `active`, `nodelete`, `access_group` '
        . "FROM `$preTableName` WHERE `access_group` IN ($strAccess) $strOrderString "
        . "LIMIT $chkLimit," . $SETS['common']['pagelines'];
    $booReturn2 = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
    if ($booReturn2 === false) {
        $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    }
    /* Process data */
    $myContentClass->listData($mastertp, $arrDataLines, $intDataCount, $intLineCount, $preKeyField, 'alias');
}
/* Show messages */
$myContentClass->showMessages($mastertp, $strErrorMessage, $strInfoMessage, $strConsistMessage, array(), '', 1);
/*
Process footer
*/
$myContentClass->showFooter($maintp, $setFileVersion);