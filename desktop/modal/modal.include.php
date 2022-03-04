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

/*
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

*/

?>

<script>
    
  $(document).ready(function () {
    // ----- Actions when displaying the modal

  });

  $("#btApply").click(function() {
    modal_include_apply();
  });

  $("#btCancel").click(function() {
    modal_include_cancel();
  });

</script>


  <form class="form-horizontal onsubmit="return false;"> 
    <label class="control-label" > {{Sélectionner le type d'équipement à inclure :}} </label> 

    <br><blockquote>
    <input type="checkbox" name="class_type" value="enoceanSwitch" checked /> {{enoceanSwitch}}<br>
    <input type="checkbox" name="class_type" value="enoceanSensor" /> {{enoceanSensor}}<br>
    <input type="checkbox" name="class_type" value="arubaTag" /> {{arubaTag}}<br>
    <input type="checkbox" name="class_type" value="arubaBeacon" /> {{arubaBeacon}}<br>
    <input type="checkbox" name="class_type" value="generic" /> {{generic}}<br>
    <blockquote>
    <input type="checkbox" name="generic_with_local" value="1" /> {{only with local info present}}<br>
    <input type="checkbox" name="generic_with_mac" value="1" /> {{filtered by mac prefix}} : <input type="text" id="mac_prefix" name="mac_prefix" value="XX:XX:XX" /><br>
    {{Limited to a maximum of}} <input type="text" id="max_devices" name="max_devices" value="3" style="width:50px;"/> {{generic devices}}<br>
    </blockquote>
    </blockquote>
    
    <a id="btApply" class="btn btn-success " ><i class="far fa-check-circle icon-white"></i> Lancer</a>    
    <a id="btCancel" class="btn btn-danger " ><i class="far fa-check-circle icon-white"></i> Annuler</a>    
  </form>



<?php include_file('desktop', 'modal_include', 'js', 'ArubaIot'); ?>
