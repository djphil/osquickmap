<?php
/**
 * osquickmap v0.2 by djphil (CC-BY-NC-SA 4.0)
 * Improvments, security, internationalisation, bugs fix, cleanup and design
**/
?>
<?php include_once("inc/configcheck.php"); ?>
<?php include_once("inc/config.php"); ?>
<?php include_once("inc/mysqli.php"); ?>
<?php include_once("inc/translator.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $title.' v'.$version ?> by djphil (CC-BY-NC-SA 4.0)</title>
    <meta name="description" content="<?php echo $title.' v'.$version ?> by djphil (CC-BY-NC-SA 4.0)" />
    <meta name="keywords" content="OpenSimulator, Free Coordinate, Map" />
    <meta name="author" content="Philippe Lemaire (djphil)" />
    <link rel="icon" href="img/favicon.ico">
    <link rel="author" href="inc/humans.txt" />
    <meta name="copyright" content="CC-BY-NC-SA 4.0" />
    <meta name="robots" content="nofollow" />

    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/osquickmap.css" rel="stylesheet">
    <link href="css/gh-fork-ribbon.min.css" rel="stylesheet">

    <?php if ($slate === TRUE): ?>
        <link href="css/slate.css" rel="stylesheet">
    <?php endif; ?>
    
    <?php if ($theme === TRUE): ?>
        <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <?php endif; ?>

    <style>
        <?php if ($water === TRUE): ?>
            body {background-color: #1D475F; color: #FFFFFF;}
        <?php endif; ?>
        .free {background-color: <?php echo $free; ?>;}
        .main {background-color: <?php echo $main; ?>; }
        .single {background-color: <?php echo $single; ?>;}
        .var {background-color: <?php echo $var; ?>;}
        .free, .main, .single, .var  {margin: <?php echo $margin; ?> 0px 0px <?php echo $margin; ?>; }
        .free, .main, .single, .var {height: <?php echo $maxi; ?>; width: <?php echo $maxi; ?>; border-radius: <?php echo $rounded; ?>;}
        .mini {height: <?php echo $mini; ?>; width: <?php echo $mini; ?>; border-radius: <?php echo $rounded; ?>; }
    </style>  
</head>

<body>

<div class="github-fork-ribbon-wrapper left">
    <div class="github-fork-ribbon">
        <a href="https://github.com/djphil/osquickmap" target="_blank">Fork me on GitHub</a>
    </div>
</div>
<?php
/**
 * Copyright (c) Metropolis Metaversum [ http://hypergrid.org ]
 *
 * The MetroTools are BSD-licensed. For more infornmations about BSD-licensed
 * Software use this link: http://www.wikipedia.org/BSD-License
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Metropolis Project nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
**/

$grid_x = 0;
$grid_y = 0;

if (isset($_POST['x']) && ($_POST['y']))
{
    $grid_x = mysqli_real_escape_string($db, $_POST['x']);
    $grid_y = mysqli_real_escape_string($db, $_POST['y']);
}

else
{
    if (isset($_GET['x']) && ($_GET['y']))
    {
        $grid_x = mysqli_real_escape_string($db, $_GET['x']);
        $grid_y = mysqli_real_escape_string($db, $_GET['y']);
    } 
}

if ($grid_x == 0) {$grid_x = $center_x;}
if ($grid_y == 0) {$grid_y = $center_y;}
if ($grid_y <= 30) {$grid_y = "100";}
if ($grid_x <= 30) {$grid_x = "100";}
if ($grid_x >=99999) {$grid_x = $center_x;}
if ($grid_y >=99999) {$grid_y = $center_y;}

$start_x = $grid_x - 12;
$start_y = $grid_y + 8;
$end_x = $grid_x + 12;
$end_y = $grid_y - 7;
$xx = 0;

$sql = mysqli_query($db, "
    SELECT uuid, regionName, locX, locY, serverURI, sizeX, sizeY, owner_uuid 
    FROM regions
") or die("Error: " . mysqli_error($db));

while($region = mysqli_fetch_array($sql))
{
    if ($region['sizeX'] == 0) {$region['sizeX'] = 256;}
    if ($region['sizeY'] == 0) {$region['sizeY'] = 256;}

    if ((($region['sizeX'] == 256) && ($region['sizeY'] == 256)) || (($region['sizeX'] == 256) && ($region['sizeY'] == 0)))
    {
        $work_reg = $region['uuid'].";".$region['regionName'].";".$region['locX'].";".$region['locY'].";".$region['serverURI'].";".$region['sizeX'].";".$region['sizeY'].";".$region['owner_uuid'].";SingleRegion";
        $region_sg[$xx] = $work_reg;
        $xx++;
    }

    else
    {
        $varreg_locx = ($region['locX'] / 256);
        $varreg_locy = ($region['locY'] / 256);
        $varreg_start_x = $varreg_locx;
        $varreg_start_y = $varreg_locy;
        $varreg_end_x = $varreg_locx + (($region['sizeX'] / 256) - 1);
        $varreg_end_y = $varreg_locy + (($region['sizeY'] / 256) - 1); 

        $varreg_work_x = $varreg_start_x;
        $varreg_work_y = $varreg_start_y;

        while (($varreg_work_y <= $varreg_end_y)&& ($varreg_work_x <= $varreg_end_x))
        {
            $varreg_key = $varreg_work_x."-".$varreg_work_y;

            $work_reg = $region['uuid'].";".$region['regionName'].";".$varreg_work_x.";".$varreg_work_y.";".$region['serverURI'].";".$region['sizeX'].";".$region['sizeY'].";".$region['owner_uuid'].";VarRegion";

            $region_sg[$xx] = $work_reg;
            $xx++;

            if ($varreg_work_y == $varreg_end_y)
            {
                $varreg_work_y = $varreg_start_y;
                $varreg_work_x++;
            }
            else
            {
                $varreg_work_y++;
            }
        }
    }
}
if ($slate === TRUE) $theme = FALSE;
?>

<center>
<div class="container-fluid">
    <h1><?php echo $title.' v'.$version ?> by djphil (CC-BY-NC-SA 4.0)</h1>              
    <div class="spacer"></div>
    <form class="form form-inline" name="submit" action="./" method="post">
        <a class="btn btn-primary" href="index.php?x=<?php echo $grid_x + 10; ?>&y=<?php echo $grid_y; ?>" target="_self">
            <span class="glyphicon glyphicon-arrow-left" alt="<?php echo $txt_west; ?>" title="<?php echo $txt_west; ?>"></span>
        </a>

        <a class="btn btn-primary" href="index.php?x=<?php echo $grid_x; ?>&y=<?php echo $grid_y + 10; ?>" target="_self">
            <span class="glyphicon glyphicon-arrow-up" alt="<?php echo $txt_north; ?>" title="<?php echo $txt_north; ?>"></span>
        </a>

        <a class="btn btn-info" href="index.php?x=<?php echo $center_x; ?>&y=<?php echo $center_y; ?>" target="_self">
            <span class="glyphicon glyphicon-home" alt="<?php echo $txt_center; ?>" title="<?php echo $txt_center; ?>"></span>
        </a>

        <a class="btn btn-primary" href="index.php?x=<?php echo $grid_x; ?>&y=<?php echo $grid_y - 10; ?>" target="_self">
            <span class="glyphicon glyphicon-arrow-down" alt="<?php echo $txt_south; ?>" title="<?php echo $txt_south; ?>"></span>
        </a>

        <a class="btn btn-primary" href="index.php?x=<?php echo $grid_x - 10; ?>&y=<?php echo $grid_y; ?>" target="_self">
            <span class="glyphicon glyphicon-arrow-right" alt="<?php echo $txt_east; ?>" title="<?php echo $txt_east; ?>"></span>
        </a>

        <div class="input-group">
            <div class="input-group-addon"><strong>X:</strong></div>
            <input type="text" class="form-control" name="x" size="5" value="<?php echo $grid_x;?>">
        </div>

        <div class="input-group">
            <div class="input-group-addon"><strong>Y:</strong></div>
            <input type="text" class="form-control" name="y" size="5" value="<?php echo $grid_y;?>">
        </div>

        <button class="btn btn-success" type="submit" name="submit" value="search">
            <span class="glyphicon glyphicon-search" alt="<?php echo $txt_search; ?>" title="<?php echo $txt_search; ?>"></span>
        </button>

        <a class="btn btn-primary" href="?lang=fr">fr</a>
        <a class="btn btn-primary" href="?lang=en">en</a>
        <a class="btn btn-primary" href="?lang=nl">nl</a>
    </form>

    <div class="spacer"></div>

    <div class="table-responsive">
        <table class="text-center">
        <?php 
        $y = $start_y;
        $x = $start_x;

        while ($y >= $end_y) {
            $x = $start_x; ?>
            <tr>
                <td>
                    <div class="badge"><?php if ($y <> $start_y) {echo $y;} ?></div>
                    <?php while ($x <= $end_x) {if ($y == $start_y) { ?>
                </td>

                <td>
                    <div class="badge">
                        <?php 
                        $xs = "a";
                        $xs = $x;
                        $z1 = ""; 
                        $z2 = ""; 
                        $z3 = ""; 
                        $z4 = ""; 
                        $z5 = ""; 
                        $z6 = "";
                        $z1 = substr($xs, '0', '1');
                        $z2 = substr($xs, '1', '1');
                        $z3 = substr($xs, '2', '1');
                        $z4 = substr($xs, '3', '1');
                        $z5 = substr($xs, '4', '1');
                        $z6 = substr($xs, '5', '1');

                        if ($z1) {print $z1;} else {print "<br />0";}
                        if ($z2) {print "<br />".$z2;} else {if ($x >= 10) {print "<br />0";}}
                        if ($z3) {print "<br />".$z3;} else {if ($x >= 100) {print "<br />0";}}
                        if ($z4) {print "<br />".$z4;} else {if ($x >= 1000) {print "<br />0";}}
                        if ($z5) {print "<br />".$z5;} else {if ($x >= 10000) {print "<br />0";}}
                        if ($z6) {print "<br />".$z6;} else {if ($x >= 100000) {print "<br />0";}}
                        ?>
                    </div>

                    <?php $x++; } else {
                    $count = count($region_sg);

                    for ($q = 0; $q < $count; $q++)
                    {
                        $region_value = $region_sg[$q];
                        $sim_new = 0;
                        list($region_uuid, $region_name, $region_locx, $region_locy, $region_serverip, $region_sizex, $region_sizey, $region_owner, $region_type) = explode(";",$region_value);

                        if ($region_sizey == 0) { $region_sizey = 256; }

                        if ($region_locx >= 100000)
                        {
                            $region_locx = $region_locx / 256;
                            $region_locy = $region_locy / 256;
                        }

                        if (($region_locx == $x) && ($region_locy == $y)) {$sim_new = 1; break;}
                    }

                    $sql = mysqli_query($db, "
                        SELECT FirstName, LastName 
                        FROM UserAccounts 
                        WHERE PrincipalID = '$region_owner'
                    ") or die("Error: " . mysqli_error($db));

                    $owner = mysqli_fetch_array($sql);
                    $firstname = $owner['FirstName'];
                    $lastname = $owner['LastName'];

                    if ($sim_new == 1)
                    {
                        if (($x == $center_x) && ($y == $center_y))
                        {
                            $region_dimension = ($region_sizex / 256)." x ".($region_sizey / 256)." Regions";
                            $region_total_size = $region_sizex * $region_sizey;
                            $region_total_size = number_format($region_total_size, 0, ",", ".")." sqm"; 
                            $tips  = 'RegionName: '.$region_name;
                            $tips .= '<br />RegionType: '.$region_type;
                            $tips .= '<br />Dimension: '.$region_dimension;
                            $tips .= '<br />Total size: '.$region_total_size; 
                            $tips .= '<br />X-Coordinate: '.$x;
                            $tips .= '<br />Y-Coordinate: '.$y;
                            $tips .= '<br />Status: OCCUPIED';
                            $tips .= '<br />Owner: '.$firstname.' '.$lastname;
                            ?>
                </td>

                <td>
                    <div class="main" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $tips; ?>"></div>
                    <?php $x++; }
                    else
                    {
                        if ($region_type == "SingleRegion") $class = "single";
                        if ($region_type == "VarRegion") $class = "var";

                        $region_dimension = ($region_sizex / 256)." x ".($region_sizey / 256)." Regions";
                        $region_totalsize = $region_sizex * $region_sizey;
                        $region_totalsize = number_format($region_totalsize, 0, ",", ".")." sqm"; 

                        $tips  = 'RegionName: '.$region_name;
                        $tips .= '<br />RegionType: '.$region_type;
                        $tips .= '<br />Dimension: '.$region_dimension;
                        $tips .= '<br />Total size: '.$region_totalsize; 
                        $tips .= '<br />X-Coordinate: '.$x;
                        $tips .= '<br />Y-Coordinate: '.$y;
                        $tips .= '<br />Status: OCCUPIED';
                        $tips .= '<br />Owner: '.$firstname.' '.$lastname;
                    ?>
                </td>

                <td><div class="<?php  echo $class; ?>" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $tips; ?>"></div></td> 

                <?php $x++; }}
                else {
                    $tips  = 'X-Coordinate: '.$x;
                    $tips .= '<br />Y-Coordinate: '.$y;
                    $tips .= '<br />Status: '.$txt_free;
                ?>

                <td><div class="free" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $tips; ?>"></div></td>
                <?php $x++; }}} $y--; } 
                mysqli_free_result($sql);
                mysqli_close($db);
                ?>
            </tr>
        </table>
    </div>

    <div class="spacer"></div>

    <table class="table-md">
        <tr>
            <td><div class="free mini" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $txt_free; ?>"></div></td>
            <td>&nbsp;<?php  echo $txt_free; ?></td>            
            <td><div class="main mini" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $txt_main; ?>"></div></td>
            <td>&nbsp;<?php  echo $txt_main; ?></td>
            <td><div class="single mini" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $txt_single; ?>"></div></td>
            <td>&nbsp;<?php  echo $txt_single; ?></td>
            <td><div class="var mini" data-toggle="tooltip" data-placement="top" data-html="true" title="<?php  echo $txt_var; ?>"></div></td>
            <td>&nbsp;<?php  echo $txt_var; ?></td>
        </tr>
    </table>

    <div class="spacer"></div>

    <p><?php echo $title.' v'.$version ?> by djphil <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/legalcode" class="label label-default" target="_blank">CC-BY-NC-SA 4.0</a></p>
</center>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>$(document).ready(function(){$('[data-toggle="tooltip"]').tooltip();});</script>

</body>
</html>
