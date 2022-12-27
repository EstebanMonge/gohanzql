<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Group administration
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
 * @var int $chkListId from prepend_adm.php -> Actual dataset id (list view)
 * @var string $chkSelModify from prepend_adm.php -> Modification selection value
 * @var int $hidSortBy from prepend_adm.php -> Sort data by
 * @var string $hidSortDir from prepend_adm.php -> Sort data direction (ASC, DESC)
 * @var int $chkLimit from prepend_adm.php / settings -> Data set count per page
 * @var array $SETS Settings array
 * @var int $intWriteAccessId from prepend_content.php -> Admin write access to actual dataset id
 * @var string $chkTfValue1 from prepend_content.php -> Group name
 * @var string $chkTfValue2 from prepend_content.php -> Group description
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
$prePageId = 33;
$preContent = 'admin/group.htm.tpl';
$preListTpl = 'admin/datalist_common.htm.tpl';
$preSearchSession = 'group';
$preTableName = 'tbl_group';
$preKeyField = 'groupname';
$preAccess = 1;
$preFieldvars = 1;
$preNoAccessGrp = 1;
$arrDataLines = array();
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
if (($chkModus === 'insert') || ($chkModus === 'modify')) {
    $strSQLx = "`$preTableName` SET `groupname`='$chkTfValue1', `description`='$chkTfValue2', `active`='$chkActive', "
        . '`last_modified`=NOW()';
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
                if ($chkModus === 'insert') {
                    $myDataClass->writeLog(translate('A new group added:') . ' ' . $chkTfValue1);
                }
                if ($chkModus === 'modify') {
                    $myDataClass->writeLog(translate('User modified:') . ' ' . $chkTfValue1);
                }
                /*
                Insert/update user/group data from session data
                */
                if ($chkModus === 'modify') {
                    $strSQL = "DELETE FROM `tbl_lnkGroupToUser` WHERE `idMaster`=$chkDataId";
                    $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
                    if ($intReturn !== 0) {
                        $myVisClass->processMessage($myDataClass->strErrorMessage, $strErrorMessage);
                    }
                }
                if (isset($_SESSION['groupuser']) && is_array($_SESSION['groupuser']) &&
                    (count($_SESSION['groupuser']) !== 0)) {
                    foreach ($_SESSION['groupuser'] as $elem) {
                        if ((int)$elem['status'] === 0) {
                            $intRead = 0;
                            $intWrite = 0;
                            $intLink = 0;
                            if (substr_count($elem['rights'], 'READ') !== 0) {
                                $intRead = 1;
                            }
                            if (substr_count($elem['rights'], 'WRITE') !== 0) {
                                $intWrite = 1;
                            }
                            if (substr_count($elem['rights'], 'LINK') !== 0) {
                                $intLink = 1;
                            }
                            if ($intWrite === 1) {
                                $intRead = 1;
                                $intLink = 1;
                            }
                            if ($intRead === 1) {
                                $intLink = 1;
                            }
                            /* if ($intLink  === 1) $intRead = 1; */
                            $strSQL = 'INSERT INTO `tbl_lnkGroupToUser` (`idMaster`,`idSlave`,`read`,`write`,'
                                . "`link`) VALUES ($chkDataId," . $elem['user'] . ",'$intRead','$intWrite',"
                                . "'$intLink')";
                            $intReturn = $myDataClass->dataInsert($strSQL, $intInsertId);
                            if ($intReturn !== 0) {
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
Singe data form
*/
if ($chkModus === 'add') {
    /* Process data fields */
    $strSQL = 'SELECT * FROM `tbl_user` WHERE `id`<>1 ORDER BY `username`';
    $booReturn = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
    $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    if ($booReturn && ($intDataCount !== 0)) {
        foreach ($arrDataLines as $elem) {
            $conttp->setVariable('DAT_USER_ID', $elem['id']);
            $conttp->setVariable('DAT_USER', $elem['username']);
            /** @noinspection DisconnectedForeachInstructionInspection */
            $conttp->parse('users');
        }
    }
    /* Initial add/modify form definitions */
    $myContentClass->addFormInit($conttp);
    $conttp->setVariable('TITLE', translate('Group administration'));
    $conttp->setVariable('LANG_READ', translate('Read'));
    $conttp->setVariable('LANG_WRITE', translate('Write'));
    $conttp->setVariable('LANG_LINK', translate('Link'));
    $conttp->setVariable('DAT_ID', $chkListId);
    $conttp->setVariable('FILL_ALLFIELDS', translate('Please fill in all fields marked with an *'));
    $conttp->setVariable('FILL_ILLEGALCHARS', translate('The following field contains illegal characters:'));
    /* Insert data from database in "modify" mode */
    if (isset($arrModifyData) && ($chkSelModify === 'modify')) {
        /* Process data */
        $myContentClass->addInsertData($conttp, $arrModifyData, 0, '');
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
    $mastertp->setVariable('TITLE', translate('Group administration'));
    $mastertp->setVariable('FIELD_1', translate('Groupname'));
    $mastertp->setVariable('FIELD_2', translate('Description'));
    /* Row sorting */
    $strOrderString = "ORDER BY `groupname` $hidSortDir";
    if ($hidSortBy === 2) {
        $strOrderString = "ORDER BY `description` $hidSortDir";
    }
    /* Count datasets */
    $intLineCount = 0;
    /** @noinspection SqlResolve */
    $strSQL = "SELECT count(*) AS `number` FROM `$preTableName`";
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
    $strSQL = 'SELECT `id`, `groupname`, `description`, `active` '
        . "FROM `$preTableName` $strOrderString LIMIT $chkLimit," . $SETS['common']['pagelines'];
    $booReturn2 = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
    if ($booReturn2 === false) {
        $myVisClass->processMessage(translate('Error while selecting data from database:'), $strErrorMessage);
        $myVisClass->processMessage($myDBClass->strErrorMessage, $strErrorMessage);
    }
    /* Process data */
    $myContentClass->listData($mastertp, $arrDataLines, $intDataCount, $intLineCount, $preKeyField, 'description');
}
/* Show messages */
$myContentClass->showMessages($mastertp, $strErrorMessage, $strInfoMessage, $strConsistMessage, array(), '', 1);
/*
Process footer
*/
$myContentClass->showFooter($maintp, $setFileVersion);