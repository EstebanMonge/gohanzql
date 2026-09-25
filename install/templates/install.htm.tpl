<!-- (c) 2005-2022 by Martin Willisegger -->
<!-- (c) 2026 by Esteban Monge - Sempai Space -->
<!-- -->
<!-- Project   : Gohan ZQL -->
<!-- Component : Installer template -->
<!-- Website   : https://github.com/EstebanMonge/gohanzql -->
<!-- Version   : 4.0.0 -->
<!-- GIT Repo  : https://github.com/EstebanMonge/gohanzql -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{PAGETITLE}</title>
    <link href="css/install.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="page_margins">
    <div id="page">
        <div id="header">
            <div id="header-logo">
                <a href="index.php"><img src="images/nagiosql.png" border="0" alt="Gohan ZQL Logo" title="Gohan ZQL Logo"></a>
            </div>
            <div id="documentation">
                <a href="https://sourceforge.net/projects/nagiosql/faq.html"
                   target="_blank"><?php echo translate("Online Documentation"); ?></a>
            </div>
        </div>
        <div id="main">
            {CONTENT}
        </div>
        <div id="footer">
            <a href='https://github.com/EstebanMonge/gohanzql' target='_blank'>Gohan ZQL</a> <?php echo BASE_VERSION; ?>
        </div>
    </div>
</div>
</body>
</html>