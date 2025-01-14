<?php
require 'autoload.php';
$Config = new Config();
$update = $Config->checkUpdate();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1" /> 
        <title>eZ Server Monitor - <?php echo Misc::getHostname(); ?></title>
        <link rel="stylesheet" href="web/css/utilities.css" type="text/css">
        <link rel="stylesheet" href="web/css/frontend.css" type="text/css">
        <link rel="icon" type="image/x-icon" href="favicon.ico">
        <!--[if IE]>
        <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <script src="js/plugins/jquery-2.1.0.min.js" type="text/javascript"></script>
        <script src="js/plugins/jquery.knob.js" type="text/javascript"></script>
        <script src="js/esm.js" type="text/javascript"></script>
        <script src="web/js/chart.min.js"></script>
        <script>
            $(function () {
                $('.gauge').knob({
                    'fontWeight': 'normal',
                    'format': function (value) {
                        return value + '%';
                    }
                });

                $('a.reload').click(function (e) {
                    e.preventDefault();
                });

                esm.getAll();

<?php if ($Config->get('esm:auto_refresh') > 0): ?>
                    setInterval(function () {
                        esm.getAll();
                    }, <?php echo $Config->get('esm:auto_refresh') * 1000; ?>);
<?php endif; ?>
            });
        </script>
        
    </head>

    <body class="theme-<?php echo $Config->get('esm:theme'); ?>">

        <nav role="main">
            <div id="appname">
                <a href="index.php"><span class="icon-gauge"></span>eSM</a>
                <a href="<?php echo $Config->get('esm:website'); ?>"><span class="subtitle">eZ Server Monitor - v<?php echo $Config->get('esm:version'); ?></span></a>
            </div>

            <div id="hostname">
                <?php
                if ($Config->get('esm:custom_title') != '') {
                    echo $Config->get('esm:custom_title');
                }
                else {
                    echo Misc::getHostname() . ' - ' . Misc::getLanIP();
                }
                ?>
            </div>

            <?php if (!is_null($update)): ?>
                <div id="update">
                    <a href="<?php echo $update['fullpath']; ?>">New version available (<?php echo $update['availableVersion']; ?>) ! Click here to download</a>
                </div>
            <?php endif; ?>

            <ul>
                <li><a href="#" class="reload" onclick="esm.reloadBlock('all');"><span class="icon-cycle"></span></a></li>
            </ul>
        </nav>


        <div id="main-container">

            <div class="box" id="esm-load_average">
                <div class="box-header">
                    <h1>Load Average</h1>
                    <ul>
                        <li><a href="#" class="reload" onclick="esm.reloadBlock('load_average');"><span class="icon-cycle"></span></a></li>
                    </ul>
                </div>
                
                <div>
                    <canvas id="myLoadChart"></canvas>
                </div>

                <div class="box-content t-center">
                    <div class="f-left w33p">
                        <h3>1 min</h3>
                        <input type="text" class="gauge" id="load-average_1" value="0" data-height="100" data-width="150" data-min="0" data-max="100" data-readOnly="true" data-fgColor="#BED7EB" data-angleOffset="-90" data-angleArc="180">
                    </div>

                    <div class="f-left w33p">
                        <h3>5 min</h3>
                        <input type="text" class="gauge" id="load-average_5" value="0" data-height="100" data-width="150" data-min="0" data-max="100" data-readOnly="true" data-fgColor="#BED7EB" data-angleOffset="-90" data-angleArc="180">
                    </div>

                    <div class="f-left w33p">
                        <h3>15 min</h3>
                        <input type="text" class="gauge" id="load-average_15" value="0" data-height="100" data-width="150" data-min="0" data-max="100" data-readOnly="true" data-fgColor="#BED7EB" data-angleOffset="-90" data-angleArc="180">
                    </div>

                    <div class="cls"></div>
                </div>
            </div>

            <div class="cls"></div>

            <div class="box" id="esm-disk">
                <div class="box-header">
                    <h1>Disk usage</h1>
                    <ul>
                        <li><a href="#" class="reload" onclick="esm.reloadBlock('disk');"><span class="icon-cycle"></span></a></li>
                    </ul>
                </div>

                <div>
                    <canvas id="myChart"></canvas>
                </div>
                
                <div class="box-content">
                    <table>
                        <thead>
                            <tr>
                                <?php if ($Config->get('disk:show_filesystem')): ?>
                                    <th class="w10p filesystem">Filesystem</th>
                                <?php endif; ?>
                                <th class="w20p">Mount</th>
                                <th>Use</th>
                                <th class="w15p">Free</th>
                                <th class="w15p">Used</th>
                                <th class="w15p">Total</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box" id="esm-memory">
                <div class="box-header">
                    <h1>Memory</h1>
                    <ul>
                        <li><a href="#" class="reload" onclick="esm.reloadBlock('memory');"><span class="icon-cycle"></span></a></li>
                    </ul>
                </div>

                <div class="box-content">
                    <table class="firstBold">
                        <tbody>
                            <tr>
                                <td class="w20p">Used %</td>
                                <td><div class="progressbar-wrap"><div class="progressbar" style="width: 0%;">0%</div></div></td>
                            </tr>
                            <tr>
                                <td class="w20p">Used</td>
                                <td id="memory-used"></td>
                            </tr>
                            <tr>
                                <td class="w20p">Free</td>
                                <td id="memory-free"></td>
                            </tr>
                            <tr>
                                <td class="w20p">Total</td>
                                <td id="memory-total"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cls"></div>


            <div class="t-center">
                <div class="box" id="esm-last_login">
                    <div class="box-header">
                        <h1>Last login</h1>
                        <ul>
                            <li><a href="#" class="reload" onclick="esm.reloadBlock('last_login');"><span class="icon-cycle"></span></a></li>
                        </ul>
                    </div>

                    <div class="box-content">
                        <?php if ($Config->get('last_login:enable') == true): ?>
                            <table>
                                <tbody></tbody>
                            </table>
                        <?php else: ?>
                            <p>Disabled</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="box-content">
                    <table>
                        <tbody></tbody>
                    </table>
                </div>
            </div>


            <div class="cls"></div>

        </div>
     
    </body>
</html>




