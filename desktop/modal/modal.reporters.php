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

$v_result_array = json_decode($v_result, true);

if (isset($v_result_array['websocket'])) {
  $v_websocket = $v_result_array['websocket'];
  $v_websocket['status'] = "Up";
}
else {
  $v_websocket = array();
  $v_websocket['status'] = 'Down';
  $v_websocket['ip_address'] = 'n/a';
  $v_websocket['tcp_port'] = 'n/a';
}

$v_list = (isset($v_result_array['reporters']) ? $v_result_array['reporters'] : array());


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
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">Status : </label>
<?php if ($v_websocket['status'] == "Up") {
                echo '<div><i style="color: green;" class="fa fa-check"></i>&nbsp;&nbsp;Up since '.date("d/m/Y H:i:s", $v_websocket['up_time']).'</div>';
 } else {
                echo '<div><i style="color: red;" class="fa fa-info-circle"></i>&nbsp;&nbsp;Down</div>';

 } ?>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">IP Address : </label>
                <div><?php echo $v_websocket['ip_address']; ?></div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">Port TCP : </label>
                <div><?php echo $v_websocket['tcp_port']; ?></div>
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
                    <label class="col-sm-3 control-label" style="margin-bottom: 0;">Nombre de Reporters : </label>
                    <div><?php echo sizeof($v_list); ?></div>
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
                        <th data-sort="int" class="scanTd" style="width:110px;"><span class="scanHender"><b class="caret"></b> {{ip}}</span></th>
                        <th data-sort="string" class="scanTd" style="text-align: center; width:100px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{Telemetry}}</span></th>
                        <th data-sort="string" style="text-align: center; width:100px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{RTLS}}</span></th>
                        <th data-sort="string" style=" width:150px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{Modèle}}</span></th>
                        <th data-sort="string" style=" width:200px;" class="scanTd"><span class="scanHender"><b class="caret"></b>{{Version}}</span></th>
                        <th data-sort="string" class="scanTd"><span class="scanHender"><b class="caret"></b> {{Commentaire}}</span></th>
                        <th data-sort="int" class="scanTd" style="width:170px;"><span class="scanHender"><b class="caret"></b> {{Date de mise à jour}}</span></th>
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
        <td class="scanTd "><span style="display:none;"></span></td>
        <td class="scanTd "><span style="display:none;"></span></td>
      </tr>

<?php

                }
?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    
    $("#btSaveList").click(function() {
        alert('For future use !');
    });
    

    $(document).ready(function ($) {
    var $table = $("#reporters_list").stupidtable(); 
    var $th_to_sort = $table.find("thead th").eq("<?php /*echo scan_ip_widget_network::getOrderBy($orderBy)*/ ?>");
    $th_to_sort.stupidsort();


});

</script>

<?php /*include_file('desktop', 'reporters_list', 'js', 'scan_ip'); */?>
<?php include_file('desktop', 'lib/stupidtable.min', 'js', 'ArubaIot');  ?>
