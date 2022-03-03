<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

require_once dirname(__FILE__) . "/../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php";

//$v_list = ArubaIotReporter::getReportersForModal();

$v_data = array('tbd1' => '', 'tbd2' => '' );
$v_result = ArubaIot::talkToWebsocket('reporter_list', $v_data);

//var_dump($v_result);

ArubaIotLog::log('ArubaIot', 'debug', 'websocket Result ' . $v_result);

$v_result_array = json_decode($v_result, true);

if (isset($v_result_array['status'])
    && ($v_result_array['status'] == 'success')
    && isset($v_result_array['data']['websocket'])) {
  $v_websocket = $v_result_array['data']['websocket'];
  $v_websocket['status'] = "Up";
  $v_list = (isset($v_result_array['data']['reporters']) ? $v_result_array['data']['reporters'] : array());
}
else {
  $v_websocket = array();
  $v_websocket['status'] = 'Down';
  $v_websocket['ip_address'] = 'n/a';
  $v_websocket['tcp_port'] = 'n/a';
  $v_list = array();
}



?>

<style>
    .scanTd{
        padding : 3px 0 3px 15px !important;
    }
    .scanHender{
        cursor: pointer !important;
        width: 100%;
    }
    .macPresentActif{
        color: green;
    }
    .macPresentInactif{
        color: #FF4500;
    }
    .macAbsent{
        color: grey;
    }
    .EnableScanIp{
        color: green;
    }
    .DisableScanIp{
        color: #FF4500;
    }
    .NoneScanIp{
        color: grey;
    }
    
</style>

<div class="col-md-6">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Websocket Server</h3>
        </div>
        <div class="panel-body">
            <div class=" control-label ">
                <label >Status : </label>
<?php if ($v_websocket['status'] == "Up") {
                echo '<span><i style="color: green;" class="fa fa-check"></i>&nbsp;&nbsp;Up since '.date("d/m/Y H:i:s", $v_websocket['up_time']).'</span>';
 } else {
                echo '<span><i style="color: red;" class="fa fa-info-circle"></i>&nbsp;&nbsp;Down</span>';

 } ?>
                <br>
                <label >IP Address : </label>
                <span><?php echo $v_websocket['ip_address']; ?></span>
                <br>
                <label >Port TCP : </label>
                <span><?php echo $v_websocket['tcp_port']; ?></span>
                                
            </div>

        </div>
        <br />
    </div>
</div>

<div class="col-md-6">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Résumé</h3>
        </div>
        <div class="panel-body">
                <div>
                    <label class=" control-label" >Nombre de Reporters : </label>
                    <span><?php echo sizeof($v_list); ?></span>
                </div>
        </div>
        <br />
    </div>
</div>

<div class="col-md-12">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Liste des Rapporteurs identifiés
            <a id="btSaveList" class="btn btn-success btn-xs pull-right" style="top: -2px !important; right: -6px !important;"><i class="far fa-check-circle icon-white"></i> {{Sauvegarder}}</a>
            </h3>
        </div>
        <div class="panel-body">
            <table class="table-bordered table-condensed" style="width: 100%; margin: -5px -5px 10px 5px;" id="reporters_list">
                <thead>
                    <tr style="background-color: grey !important; color: white !important;">
                        <th data-sort="string" class="scanTd" style="width:200px;"><span class="scanHender"><b class="caret"></b> {{Nom}}</span></th>
                        <th data-sort="string" style="width:200px;" class="scanTd"><span class="scanHender"><b class="caret"></b> {{Adresse MAC}}</span></th>
                        <th data-sort="string" class="scanTd" style="width:110px;"><span class="scanHender"><b class="caret"></b> {{IP Locale}}</span></th>
                        <th data-sort="string" class="scanTd" style="width:110px;"><span class="scanHender"><b class="caret"></b> {{IP Publique}}</span></th>
                        <th data-sort="string" class="scanTd" style="text-align: center; width:100px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{Telemetrie}}</span></th>
                        <th data-sort="string" style="text-align: center; width:100px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{RTLS}}</span></th>
                        <th data-sort="string" style=" width:150px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{Modèle}}</span></th>
                        <th data-sort="string" style=" width:200px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{Version}}</span></th>
                        <th data-sort="string" class="scanTd" style="width:170px;"><span class="scanHender"><b class="caret"></b> {{Date connexion}}</span></th>
                    </tr>
                </thead>
                <tbody>


<?php         
                foreach ($v_list as $v_reporter ) {
?>

      <tr>
        <td class="scanTd " style="text-overflow: ellipsis;"><span style="display:none;"></span><?php echo $v_reporter["name"]; ?></td>
        <td class="scanTd "><?php echo $v_reporter["mac"]; ?></td>
        <td class="scanTd "><span style="display:none;"></span><?php echo $v_reporter["local_ip"]; ?></td>
        <td class="scanTd "><span style="display:none;"></span><?php echo $v_reporter["remote_ip"]; ?></td>
<?php if ($v_reporter["telemetry"] == 1) { ?>
        <td class="scanTd" title="Telemetry connection is active" style="text-align:center;"><span style="display:none;"></span> <i  style="color: green;" class="fas fa-wifi"></i>  </td>
<?php } else { ?>
        <td class="scanTd" title="Telemetry connection is inactive" style="text-align:center;"><span style="display:none;"></span> <i  style="color: grey;" class="fas fa-times"></i>  </td>
<?php } ?>
<?php if ($v_reporter["rtls"] == 1) { ?>
        <td class="scanTd" title="RTLS connection is active" style="text-align:center;"><span style="display:none;"></span> <i  style="color: green;" class="fas fa-wifi"></i>  </td>
<?php } else { ?>
        <td class="scanTd" title="RTLS connection is inactive" style="text-align:center;"><span style="display:none;"></span> <i  style="color: grey;" class="fas fa-times"></i>  </td>
<?php } ?>
        <td class="scanTd" ><span style="display:none;"></span> <?php echo $v_reporter["model"]; ?> </td>
        <td class="scanTd " ><span style="display:none;"></span> <?php echo $v_reporter["version"]; ?> </td>
        <td class="scanTd "><span style="display:none;"></span> <?php echo date("d/m/Y H:i:s", $v_reporter["uptime"]); ?></td>
      </tr>

<?php

                }
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="col-md-12">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">Statistiques
            <a class="btn btn-success btn-xs pull-right btRefreshStats" style="top: -2px !important; right: -6px !important;"><i class="fas fa-sync icon-white"></i> {{Rafraichir}}</a>
            </h3>
        </div>
        <div class="panel-body">


    <div id="stats" >

<?php // loaded by javascript ?>

    </div>



        </div>
    </div>
</div>

<script>
    
    $("#btSaveList").click(function() {
        alert('For future use !');
    });
    

    $("#btRefreshStats").click(function() {
//        alert('For future use !');
      refreshStats();
    });


    $(".btRefreshStats").click(function() {
//        alert('For future use !');
      refreshStats();
    });


    $(document).ready(function ($) {


      var $table = $("#reporters_list").stupidtable();
      var $th_to_sort = $table.find("thead th").eq("<?php /*echo scan_ip_widget_network::getOrderBy($orderBy)*/ ?>");
      $th_to_sort.stupidsort();

      refreshStats();


    });

</script>

<?php include_file('desktop', 'modal_reporters', 'js', 'ArubaIot'); ?>
<?php include_file('desktop', 'lib/stupidtable.min', 'js', 'ArubaIot');  ?>
