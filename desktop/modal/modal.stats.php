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
$v_result = ArubaIot::talkToWebsocket('get_stats', $v_data);

//var_dump($v_result);

ArubaIotLog::log('ArubaIot', 'debug', 'websocket Result ' . $v_result);

$v_result_array = json_decode($v_result, true);

if (isset($v_result_array['state'])
    && ($v_result_array['state'] == 'ok')
    && isset($v_result_array['response']['global'])) {
  $v_global = $v_result_array['response']['global'];
}
else {
  $v_global = array();
  $v_global['raw_data_total_size']=0;
  $v_global['msg_total_size']=0;
}

if (isset($v_result_array['state'])
    && ($v_result_array['state'] == 'ok')
    && isset($v_result_array['response']['reporters'])) {
  $v_reporters = $v_result_array['response']['reporters'];
}
else {
  $v_reporters = array();
}


?>


    <div id="stats" class="panel panel-primary" style="width: 100%; margin: -5px -5px 10px 5px;">
        <div class="panel-heading" >
            <h3 class="panel-title">{{Statistiques Globales}}
            </h3>
        </div>
        <div class="panel-body">

            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Total données reçues}} : </label>
                <div><?php echo $v_global['raw_data_total_size']; ?> {{bytes}}</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Bande passante moyenne consommée}} : </label>
                <div><?php echo round(($v_global['uptime'] != 0 ? $v_global['raw_data_total_size']/$v_global['uptime'] : 0), 2); ?> {{bytes/sec}}</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Total données télémetrie reçues}} : </label>
                <div><?php echo $v_global['msg_total_size']; ?> bytes</div>
            </div>

            <div>
                <br>&nbsp;
            </div>


        </div>
    </div>


<?php

  foreach ($v_reporters as $v_reporter) {

?>


    <div id="stats" class="panel panel-primary" style="width: 100%; margin: -5px -5px 10px 5px;">
        <div class="panel-heading" >
            <h3 class="panel-title">{{Statistiques du Rapporteur}} <?php echo $v_reporter['name']; ?>
            </h3>
        </div>
        <div class="panel-body">

            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Taille totale des données reçues}} : </label>
                <div><?php echo $v_reporter['stats']['msg_total_bytes']; ?> bytes</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Bande passante moyenne consommée}} : </label>
                <div><?php echo round(($v_reporter['uptime'] != 0 ? $v_reporter['stats']['msg_total_bytes']/$v_reporter['uptime'] : 0),2); ?> bytes/sec</div>
            </div>
            <div>
                &nbsp;
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Nombre de messages reçus}} : </label>
                <div><?php echo $v_reporter['stats']['msg_count']; ?></div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Taille moyenne d'un message}} : </label>
                <div><?php echo round($v_reporter['stats']['msg_size_average'], 0); ?> bytes</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Taille maximale d'un message}} : </label>
                <div><?php echo $v_reporter['stats']['msg_size_max']; ?> bytes</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Taille minimum d'un message}} : </label>
                <div><?php echo $v_reporter['stats']['msg_size_min']; ?> bytes</div>
            </div>
            <div>
                &nbsp;
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Interval moyen entre deux messages}} : </label>
                <div><?php echo round($v_reporter['stats']['msg_rate_average'],0); ?> secondes</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Interval maximal entre deux messages}} : </label>
                <div><?php echo $v_reporter['stats']['msg_rate_max']; ?> secondes</div>
            </div>
            <div>
                <label class="col-sm-3 control-label" style="margin-bottom: 0;">{{Interval minimal entre deux messages}} : </label>
                <div><?php echo $v_reporter['stats']['msg_rate_min']; ?> secondes</div>
            </div>

            <div>
                <br>&nbsp;
            </div>


        </div>
    </div>

<?php

  // end du foreach ($v_reporters as $v_reporter) {
  }
?>



<?php /*include_file('desktop', 'modal_reporters', 'js', 'ArubaIot');*/ ?>
