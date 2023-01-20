<?php
/* ----------------------------------------------------------------------------
 NagiosQL
-------------------------------------------------------------------------------
 (c) 2005-2023 by Martin Willisegger

 Project   : NagiosQL
 Component : Online version check
 Website   : https://sourceforge.net/projects/nagiosql/
 Version   : 3.5.0
 GIT Repo  : https://gitlab.com/wizonet/NagiosQL
-----------------------------------------------------------------------------*/

/**
 * Class and variable includes
 * @var string $setFileVersion from prepend_adm.php -> Application version string
 * @var string $setGITVersion from prepend_adm.php -> Application version string - GIT version
 * @var array $SETS Settings array
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
$preNoMain = 1;
$chkShow = filter_input(INPUT_GET, 'show', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
/*
Include preprocessing file
*/
require $preBasePath . 'functions/prepend_adm.php';
$strCommandLine = '&nbsp;';
$intCount = 0;
/*
Get database values
*/
if ($chkShow === 1) {
    $strInstallHash = $SETS['install']['hash'] ?? 'nohash';
    if (isset($setFileVersion) && ($setFileVersion !== '') && ($setFileVersion !== null)) {
        $strVersion = $setFileVersion;
    } else {
        $strVersion = '0.0.0';
    }
    if (isset($setGITVersion) && ($setGITVersion !== '') && ($setGITVersion !== null)) {
        $strGITVersion = $setGITVersion;
    } else {
        $strGITVersion = '0000-00-00';
    }
    /** @noinspection HttpUrlsUsage */
    $versionfeed = 'http://api.wizonet.ch/nagiosql/versioncheck.php?myversion=' . urlencode($strVersion) . '&mygit='
        . urlencode($strGITVersion) . '&myhash=' . urlencode($strInstallHash);
    $strError = '';
    if (isset($SETS['network']['proxy'], $SETS['network']['proxyserver']) && ($SETS['network']['proxy'] === '1') &&
        ($SETS['network']['proxyserver'] !== '')) {
        if (isset($SETS['network']['proxyuser'], $SETS['network']['proxypasswd']) && ($SETS['network']['proxyuser'] !== '')
            && ($SETS['network']['proxypasswd'] !== '')) {
            $strProxyAuth = base64_encode($SETS['network']['proxyuser'] . ':' . $SETS['network']['proxypasswd']);
            $aContext = array(
                'http' => array(
                    'proxy' => 'tcp://' . $SETS['network']['proxyserver'],
                    'request_fulluri' => true,
                    'header' => "Proxy-Authorization: Basic $strProxyAuth",
                    'timeout' => 1,
                ),
            );
        } else {
            $aContext = array(
                'http' => array(
                    'proxy' => 'tcp://' . $SETS['network']['proxyserver'],
                    'request_fulluri' => true,
                    'timeout' => 1,
                ),
            );
        }
        $intErrorReporting = error_reporting();
        error_reporting(0);
        $cxContext = stream_context_create($aContext);
    } else {
        $intErrorReporting = error_reporting();
        error_reporting(0);
        $cxContext = stream_context_create(array('http' => array('timeout' => 1)));
    }
    $arrFile = file($versionfeed, false, $cxContext);
    $arrError = error_get_last();
    error_reporting($intErrorReporting);
    if (isset($arrError['message']) && $arrError['message'] !== '') {
        $strError .= utf8_encode($arrError['message']) . ' (' . translate('check proxy settings') . ')';
    }
    $strInstalled = translate('Installed');
    $strAvailable = translate('Available');
    $strInformation = translate('Information');
    $strVersion = '';
    $strRelease = '';
    $strRelInfo = '';
    $strGIT = '';
    if (is_array($arrFile) && count($arrFile) !== 0) {
        foreach ($arrFile as $elem) {
            if (substr_count($elem, 'version') !== 0) {
                $strVersion = trim(strip_tags($elem));
            }
            if (substr_count($elem, 'git') !== 0) {
                $strGIT = trim(strip_tags($elem));
            }
            if (substr_count($elem, 'release_date') !== 0) {
                $strRelease = trim(strip_tags($elem));
            }
            if (substr_count($elem, 'error') !== 0) {
                $strError = trim(strip_tags($elem));
            }
            if (substr_count($elem, 'information') !== 0) {
                $strRelInfo = trim(strip_tags($elem));
            }
        }
    }
    $setFileAvailable = $strVersion;
    $setFileInformation = '';
    if (version_compare($strVersion, $setFileVersion, '==')) {
        if ($strGIT === $setGITVersion) {
            $setFileInformation = "<span class='greenmessage'>" . translate('You already have the latest version installed') .
                '</span>';
        } else {
            $setFileInformation = "<span class='greenmessage'>" . translate('You already have the latest version installed') .
                ' (' . translate('new GIT hotfix version available:') . ' ' . $strVersion . '-' . $strGIT . ')</span>';
        }

    } elseif (version_compare($strVersion, $setFileVersion, '>=')) {
        $setFileInformation = "<span class='redmessage'>" . translate('You are using an old NagiosQL version. Please ' .
                'update to the latest stable version') . '</span>: ';
        $setFileInformation .= '<a href="https://sourceforge.net/projects/nagiosql/files/" target="_blank">' .
            'NagiosQL on Sourceforge</a>';
    } elseif (version_compare($strVersion, $setFileVersion, '<=')) {
        $setFileInformation = "<span class='redmessage'>" . translate('You are using a newer development version ' .
                'without official support') . '</span>';
    }
    if (($strError !== 'none') && ($strError !== '')) {
        $setFileInformation = "<span class='redmessage'>" . $strError . '</span>';
    } ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html lang="en">
    <head>
        <title>Version check</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="<?php
        echo $_SESSION['SETS']['path']['base_url']; ?>config/main.css" rel="stylesheet" type="text/css">
        <link href="<?php
        echo $_SESSION['SETS']['path']['base_url']; ?>config/content.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            <!--
            body {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 10px;
                color: #000000;
                background-color: #FFFFFF;
                margin: 0;
                border: none;
            }

            -->
        </style>
    </head>
    <body>
    <table width="100%" border="0" class="content_listtable" style="padding:0; margin:0; top:3px;">
        <tr>
            <th style="text-align: center"><?php echo $strInstalled; ?></th>
            <th style="text-align: center"><?php echo $strAvailable; ?></th>
            <th style="text-align: left; padding-left: 30px;"><?php echo $strInformation; ?></th>
        </tr>
        <tr>
            <td class="tdmb" style="width:90px;vertical-align:top;padding-top:4px;"><?php
                echo $setFileVersion; ?></td>
            <td class="tdmb" style="width:90px;vertical-align:top;padding-top:4px;"><?php
                echo $setFileAvailable; ?></td>
            <td class="tdlb" style="width:470px;vertical-align:top;padding-top:4px;"><?php
                echo $setFileInformation; ?></td>
        </tr>
    </table>
    <script type="text/javascript">
        <!--
        parent.document.getElementById('vcheck').className = 'elementHide';
        parent.document.getElementById('versioncheck').className = 'elementShow';
        <?php
        if (($strError !== 'none') && ($strError !== '')) {
            echo "            parent.document.getElementById('versioncheck').height = '65';";
        }
        ?>
        //-->
    </script>
    </body>
    </html>
    <?php
} else {
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html lang="en">
    <head>
        <title>Commandline</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">
            <!--
            body {
                font-family: Verdana, Arial, Helvetica, sans-serif;
                font-size: 10px;
                color: #000000;
                background-color: #FFFFFF;
                margin: 0;
                border: none;
            }

            -->
        </style>
    </head>
    <body>
    <p><br>Loading...</p>
    </body>
    </html>
    <?php
}
?>