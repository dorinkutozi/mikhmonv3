<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
// load session MikroTik
  $session = $_GET['session'];


// load config
  include('../include/config.php');
  include('../include/readcfg.php');

// routeros api
  include_once('../lib/routeros_api.class.php');
  include_once('../lib/formatbytesbites.php');
  $API = new RouterosAPI();
  $API->debug = false;
  $API->connect($iphost, $userhost, decrypt($passwdhost));

// get MikroTik system clock
  $getclock = $API->comm("/system/clock/print");
  $clock = $getclock[0];
  $timezone = $getclock[0]['time-zone-name'];
  date_default_timezone_set($timezone);

// get system resource MikroTik
  $getresource = $API->comm("/system/resource/print");
  $resource = $getresource[0];

// get routeboard info
  $getrouterboard = $API->comm("/system/routerboard/print");
  $routerboard = $getrouterboard[0];

// move hotspot log to disk
  $getlogging = $API->comm("/system/logging/print", array("?prefix" => "->", ));
  $logging = $getlogging[0];
  if ($logging['prefix'] == "->") {
  } else {
    $API->comm("/system/logging/add", array("action" => "disk", "prefix" => "->", "topics" => "hotspot,info,debug", ));
  }

// get hotspot log
  $getlog = $API->comm("/log/print", array("?topics" => "hotspot,info,debug", ));
  $log = array_reverse($getlog);
  $THotspotLog = count($getlog);

// get & counting hotspot users
  $countallusers = $API->comm("/ip/hotspot/user/print", array("count-only" => ""));
  if ($countallusers < 2) {
    $uunit = "item";
  } elseif ($countallusers > 1) {
    $uunit = "items";
  }

// get & counting hotspot active
  $counthotspotactive = $API->comm("/ip/hotspot/active/print", array("count-only" => ""));
  if ($counthotspotactive < 2) {
    $hunit = "item";
  } elseif ($counthotspotactive > 1) {
    $hunit = "items";
  }

  if($livereport == "disable"){
    $logh = "402px";
    $lreport = "style='display:none;'";
  }else{
    $logh = "300px";
    $lreport = "style='display:block;'";
// get selling report
  $thisD = date("d");
  $thisM = strtolower(date("M"));
  $thisY = date("Y");

  if (strlen($thisD) == 1) {
    $thisD = "0" . $thisD;
  } else {
    $thisD = $thisD;
  }

  $idhr = $thisM . "/" . $thisD . "/" . $thisY;
  $idbl = $thisM . $thisY;

  $getSRHr = $API->comm("/system/script/print", array(
    "?source" => "$idhr",
  ));
  $TotalRHr = count($getSRHr);
  $getSRBl = $API->comm("/system/script/print", array(
    "?owner" => "$idbl",
  ));
  $TotalRBl = count($getSRBl);

  for ($i = 0; $i < $TotalRHr; $i++) {

    $tHr += explode("-|-", $getSRHr[$i]['name'])[3];

  }
  for ($i = 0; $i < $TotalRBl; $i++) {

    $tBl += explode("-|-", $getSRBl[$i]['name'])[3];
  }
  }
// get traffic ether
  $getinterface = $API->comm("/interface/print");
  $interface = $getinterface[$iface - 1]['name'];
  $getinterfacetraffic = $API->comm("/interface/monitor-traffic", array(
    "interface" => "$interface",
    "once" => "",
  ));
  $ftx = formatBites($getinterfacetraffic[0]['tx-bits-per-second'], 1);
  $frx = formatBites($getinterfacetraffic[0]['rx-bits-per-second'], 1);

  $tx = $getinterfacetraffic[0]['tx-bits-per-second'];
  $rx = $getinterfacetraffic[0]['rx-bits-per-second'];
  if ($maxtx == "" || $maxtx == "0") {
    $mxtx = formatBites(100000000, 0);
    $maxtx = "100000000";
  } else {
    $mxtx = formatBites($maxtx, 0);
    $maxtx = $maxtx;
  }
  if ($maxrx == "" || $maxrx == "0") {
    $mxrx = formatBites(100000000, 0);
    $maxrx = "100000000";
  } else {
    $mxrx = formatBites($maxrx, 0);
    $maxrx = $maxrx;
  }
  $trff = "trff" . $session;
  if (empty($_SESSION[$trff])) {
    $_SESSION[$trff] = "0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0,0~0," . $tx . "~" . $rx;
  }

  $vtrff = explode(",", $_SESSION[$trff]);
  $ctrff = count($vtrff);

  $_SESSION[$trff] = $vtrff[1] . "," . $vtrff[2] . "," . $vtrff[3] . "," . $vtrff[4] . "," . $vtrff[5] . "," . $vtrff[6] . "," . $vtrff[7] . "," . $vtrff[8] . "," . $vtrff[9] . "," . $vtrff[10] . "," . $vtrff[11] . "," . $vtrff[12] . "," . $vtrff[13] . "," . $vtrff[14] . "," . $vtrff[15] . "," . $vtrff[16] . "," . $vtrff[17] . "," . $vtrff[18] . "," . $vtrff[19] . "," . $tx . "~" . $rx;

}
?>
    
<div id="reloadHome">

    <div class="row">
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
            <div class="box-group-icon"><i class="fa fa-calendar"></i></div>
              <div class="box-group-area">
                <span >System Date & Time<br>
                    <?php 
                    echo $clock['date'] . " " . $clock['time'] . "<br>
                    Uptime : " . formatDTM($resource['uptime']);
                    ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-info-circle"></i></div>
              <div class="box-group-area">
                <span >
                    <?php
                    echo "Board Name : " . $resource['board-name'] . "<br/>
                    Model : " . $routerboard['model'] . "<br/>
                    Router OS : " . $resource['version'];
                    ?>
                </span>
              </div>
            </div>
          </div>
        </div>
    <div class="col-4">
      <div class="box bmh-75 box-bordered">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-server"></i></div>
              <div class="box-group-area">
                <span >
                    <?php
                    echo "CPU Load : " . $resource['cpu-load'] . "%<br/>
                    Free Memory : " . formatBytes($resource['free-memory'], 2) . "<br/>
                    Free HDD : " . formatBytes($resource['free-hdd-space'], 2)
                    ?>
                </span>
                </div>
              </div>
            </div>
          </div> 
      </div>

        <div class="row">
          <div class="col-8">
            <div class="card">
              <div class="card-header"><h3><i class="fa fa-wifi"></i> Hotspot</h3></div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-3 col-box-6">
                      <div class="box bg-primary bmh-75">
                        <a href="./?hotspot=active&session=<?= $session; ?>">
                          <h1><?= $counthotspotactive; ?>
                              <span style="font-size: 15px;"><?= $hunit; ?></span>
                            </h1>
                          <div>
                            <i class="fa fa-laptop"></i> Hotspot Active
                          </div>
                        </a>
                      </div>
                    </div>
                    <div class="col-3 col-box-6">
                    <div class="box bg-success bmh-75">
                      <a href="./?hotspot=users&profile=all&session=<?= $session; ?>">
                            <h1><?= $countallusers; ?>
                              <span style="font-size: 15px;"><?= $uunit; ?></span>
                            </h1>
                      <div>
                            <i class="fa fa-users"></i> Hotspot Users
                          </div>
                      </a>
                    </div>
                  </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-warning bmh-75">
                      <a href="./?hotspot-user=add&session=<?= $session; ?>">
                        <div>
                          <h1><i class="fa fa-user-plus"></i>
                              <span style="font-size: 15px;">Add</span>
                          </h1>
                        </div>
                        <div>
                            <i class="fa fa-user-plus"></i> Hotspot User
                        </div>
                      </a>
                    </div>
                  </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-danger bmh-75">
                      <a href="./?hotspot-user=generate&session=<?= $session; ?>">
                        <div>
                          <h1><i class="fa fa-user-plus"></i>
                              <span style="font-size: 15px;">Generate</span>
                          </h1>
                        </div>
                        <div>
                            <i class="fa fa-user-plus"></i> Hotspot User
                        </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
            <div class="card">
              <div class="card-header"><h3><i class="fa fa-area-chart"></i> Traffic </h3></div>
                <div class="card-body">
                    <div class="row">
                    <div class="col-12">
                      <div class="box box-bordered">
                        <div><h3><?= $interface; ?></h3></div>
                          <div class="progress">
                            <div class="progress-bar-blue" style="width: <?= $getinterfacetraffic[0]['tx-bits-per-second'] / $maxtx * 100; ?>%"></div>
                          </div>
                            <span class="progress-description">
                              Tx : <?= $ftx . " / " . $mxtx; ?>
                            </span>
                          <div class="progress">
                            <div class="progress-bar-red" style="width: <?= $getinterfacetraffic[0]['rx-bits-per-second'] / $maxrx * 100; ?>%"></div>
                            </div>
                            <span class="progress-description">
                              Rx : <?= $frx . " / " . $mxrx; ?>
                            </span>
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="box box-bordered">
                          <table class="table table-bordered" style="table-layout: fixed; width: 100%; height: 100px;">
                            <tr>
                            <?php 
                            for ($i = 0; $i <= $ctrff - 1; $i++) {

                              $txf = explode("~", explode(",", $_SESSION[$trff])[$i])[0];
                              $rxf = explode("~", explode(",", $_SESSION[$trff])[$i])[1];

                              echo "<td style='vertical-align:bottom; padding:0px;'><div class='w-12 bg-blue' style='writing-mode:tb-rl;height:" . ($txf / $maxtx * 100) . "px;'></div></td><td style='vertical-align:bottom; padding:0px;'><div class='w-12 bg-red' style='writing-mode:tb-rl;height:" . ($rxf / $maxrx * 100) . "px;'></div></td>";
                            }

                            ?>
                            </tr>
                          </table>
                        </div>
                      </div>
                    </div>
                </div> 
              </div>
            </div>  
            <div class="col-4">
            <div class="row">
              <div <?= $lreport;?> class="box bmh-75 box-bordered">
                <div class="box-group">
                  <div class="box-group-icon"><i class="fa fa-money"></i></div>
                    <div class="box-group-area">
                      <span >
                        <div id="reloadLreport">
                          <?php 
                          if ($currency == in_array($currency, $cekindo['indo'])) {
                            echo "Pendapatan <br/>" . "
                          Hari ini " . $TotalRHr ."vcr : " . $currency . " " . number_format($tHr, 0, ",", ".") . "<br/>
                          Bulan ini ". $TotalRBl ."vcr : " . $currency . " " . number_format($tBl, 0, ",", ".");

                          } else {
                            echo "Income <br/>" . "
                          This day " . $TotalRHr ."vcr : " . $currency . " " . number_format($tHr, 2) . "<br/>
                          This month ". $TotalRBl ."vcr : " . $currency . " " . number_format($tBl, 2);
                          }
                          ?>
                        </div>
                    </span>
                </div>
              </div>
            </div>
            <div class="card">
              <div class="card-header">
                <h3><a href="./?hotspot=log&session=<?= $session; ?>" title="Open Hotspot Log" ><i class="fa fa-align-justify"></i> Hotspot Log</a></h3></div>
                  <div class="card-body">
                    <div style="padding: 5px; height: <?= $logh;?> ;" class="mr-t-10 overflow">
                      <table class="table table-sm table-bordered table-hover" style="font-size: 12px; td.padding:2px;">
                        <thead>
                          <tr>
                            <th>Time</th>
                            <th>User (IP)</th>
                            <th>Messages</th>
                          </tr>
                        </thead>
                        <tbody>
                    
<?php

if ($THotspotLog > 100) {
  $n = 100;
} elseif ($THotspotLog > 200) {
  $n = 200;
} elseif ($THotspotLog > 300) {
  $n = 300;
} elseif ($THotspotLog > 400) {
  $n = 400;
} elseif ($THotspotLog > 500) {
  $n = 500;
} else {
  $n = $THotspotLog;
}
for ($i = 0; $i < $n; $i++) {
  $mess = explode(":", $log[$i]['message']);
  $time = $log[$i]['time'];
  echo "<tr>";
  if (substr($log[$i]['message'], 0, 2) == "->") {
    echo "<td>" . $time . "</td>";
  //echo substr($mess[1], 0,2);
    echo "<td>";
    if (count($mess) > 6) {
      echo $mess[1] . ":" . $mess[2] . ":" . $mess[3] . ":" . $mess[4] . ":" . $mess[5] . ":" . $mess[6];
    } else {
      echo $mess[1];
    }
    echo "</td>";
    echo "<td>";
    if (count($mess) > 6) {
      echo str_replace("trying to", "", $mess[7] . " " . $mess[8] . " " . $mess[9] . " " . $mess[10]);
    } else {
      echo str_replace("trying to", "", $mess[2] . " " . $mess[3] . " " . $mess[4] . " " . $mess[5]);
    }
    echo "</td>";
  } else {
  }
  echo "</tr>";
}
?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              </div>
            </div>
</div>
</div>
