<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Variable definition list
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

use functions\MysqliDbClass;

/**
 * Class and variable includes
 * @var MysqliDbClass $myDBClass MySQL database class
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
$preAccess = 1;
$preNoMain = 1;
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
/*
Process post parameters
*/
$chkDataId = filter_input(INPUT_GET, 'dataId', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
$chkMode = filter_input(INPUT_GET, 'mode');
$chkDef = filter_input(INPUT_GET, 'def');
$chkRange = filter_input(INPUT_GET, 'range');
$chkLinkTab = filter_input(INPUT_GET, 'linktab');
/*
Get data
*/
if ($chkLinkTab !== '') {
    /** @noinspection SqlResolve */
    $strSQL = 'SELECT * FROM `tbl_variabledefinition` LEFT JOIN `' . $chkLinkTab . '` ON `id`=`idSlave` ' .
        "WHERE `idMaster`=$chkDataId ORDER BY `name`";
    $booReturn = $myDBClass->hasDataArray($strSQL, $arrDataLines, $intDataCount);
    /* Store data to session */
    if ($chkMode === null) {
        $arrTemp = array();
        $_SESSION['variabledefinition'] = array();
        if ($booReturn && ($intDataCount !== 0)) {
            foreach ($arrDataLines as $elem) {
                $arrTemp['id'] = $elem['id'];
                $arrTemp['definition'] = addslashes($elem['name']);
                $arrTemp['range'] = addslashes($elem['value']);
                $arrTemp['status'] = 0;
                $_SESSION['variabledefinition'][] = $arrTemp;
            }
        }
    }
}
/*
Add mode
*/
if ($chkMode === 'add') {
    $arrTemp = array();
    if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition'])) {
        $intCheck = 0;
        foreach ($_SESSION['variabledefinition'] as $key => $elem) {
            if (($elem['definition'] === $chkDef) && ((int)$elem['status'] === 0)) {
                $_SESSION['variabledefinition'][$key]['definition'] = $chkDef;
                $_SESSION['variabledefinition'][$key]['range'] = $chkRange;
                $intCheck = 1;
            }
        }
        if ($intCheck === 0) {
            $arrTemp['id'] = 0;
            $arrTemp['definition'] = $chkDef;
            $arrTemp['range'] = $chkRange;
            $arrTemp['status'] = 0;
            $_SESSION['variabledefinition'][] = $arrTemp;
        }
    } else {
        $arrTemp['id'] = 0;
        $arrTemp['definition'] = $chkDef;
        $arrTemp['range'] = $chkRange;
        $arrTemp['status'] = 0;
        $_SESSION['variabledefinition'][] = $arrTemp;
    }
}
/*
Deletion mode
*/
if ($chkMode === 'del' && isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition'])) {
    foreach ($_SESSION['variabledefinition'] as $key => $elem) {
        if (($elem['definition'] === $chkDef) && ((int)$elem['status'] === 0)) {
            $_SESSION['variabledefinition'][$key]['status'] = 1;
        }
    }
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>None</title>
    <link href="<?php echo $_SESSION['SETS']['path']['base_url']; ?>config/main.css" rel="stylesheet" type="text/css">
    <!--suppress JSUnresolvedVariable -->
    <script type="text/javascript">
        function b64DecodeUnicode(str) {
            return decodeURIComponent(atob(str).split('').map(function (c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));
        }

        function decodeHtml(html) {
            let txt = document.createElement("textarea");
            txt.innerHTML = html;
            return txt.value;
        }

        function doEdit(key, range) {
            parent.document.frmDetail.txtVariablename.value = decodeURIComponent(key);
            parent.document.frmDetail.txtVariablevalue.value = decodeHtml(b64DecodeUnicode(range));
        }

        function doDel(key) {
            let link;
            link = '<?php echo $_SESSION['SETS']['path']['base_url']; ?>';
            link = link + 'admin/variabledefinitions.php?dataId=<?php echo $chkDataId; ?>&mode=del&def=' + key;
            document.location.href = link;
        }
    </script>
</head>
<body style="margin:0">
<table cellpadding="0" cellspacing="0" border="0" width="100%">
    <?php
    if (isset($_SESSION['variabledefinition']) && is_array($_SESSION['variabledefinition']) &&
        (count($_SESSION['variabledefinition']) !== 0)) {
        $intCounter = 0;
        foreach ($_SESSION['variabledefinition'] as $elem) {
            if ((int)$elem['status'] === 0) {
                $intCounter++;
                ?>
                <tr>
                    <td class="tablerow" style="padding-bottom:2px; width:260px"><?php
                        echo htmlentities(stripslashes($elem['definition']), ENT_COMPAT, 'UTF-8'); ?></td>
                    <td class="tablerow" style="padding-bottom:2px; width:260px"><?php
                        echo $elem['range']; ?></td>
                    <td class="tablerow" style="width:50px" align="right"><img src="<?php
                        echo $_SESSION['SETS']['path']['base_url']; ?>images/edit.gif" width="18" height="18" alt="<?php
                        echo translate('Modify'); ?>" title="<?php echo translate('Modify'); ?>" onClick="doEdit('<?php
                        echo rawurlencode(stripslashes($elem['definition'])); ?>','<?php
                        echo base64_encode($elem['range']); ?>')" style="cursor:pointer">&nbsp;<img src="<?php
                        echo $_SESSION['SETS']['path']['base_url']; ?>images/delete.gif" width="18" height="18"
                                                                                                    alt="<?php
                                                                                                    echo translate('Delete'); ?>"
                                                                                                    title="<?php echo translate('Delete'); ?>"
                                                                                                    onClick="doDel('<?php
                                                                                                    echo rawurlencode(stripslashes($elem['definition'])); ?>')"
                                                                                                    style="cursor:pointer">
                    </td>
                </tr>
                <?php
            }
        }
        if ($intCounter === 0) {
            ?>
            <tr>
                <td class="tablerow"><?php echo translate('No data'); ?></td>
                <td class="tablerow">&nbsp;</td>
                <td class="tablerow" align="right">&nbsp;</td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td class="tablerow"><?php echo translate('No data'); ?></td>
            <td class="tablerow">&nbsp;</td>
            <td class="tablerow" align="right">&nbsp;</td>
        </tr>
        <?php
    }
    ?>
</table>
</body>
</html>