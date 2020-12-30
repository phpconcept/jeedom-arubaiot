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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>


<form class="form-horizontal">

    <div class="form-group">
        <div class="col-lg-2" style="right:15px; position: absolute;">
            <select onchange="fct_arubaiot_display_mode(); " class="configKey form-control" data-l1key="config_display_mode" id="arubaiot_display_mode">
                <option value="normal">{{Mode normal}}</option>
                <option value="advanced">{{Mode avancé}}</option>
                <option value="debug">{{Mode debug}}</option>
            </select>
        </div>
    </div>


    <fieldset>

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Devices}}</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label">{{Device Types Allow List}}
            <sup><i class="fa fa-question-circle tooltips" title="{{Types d'objects qui seront pris en compte lors du mode inclusion.}}"></i></sup>
            </label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="device_type_allow_list" value="" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Delais detection d'absence (sec)}}
            <sup><i class="fa fa-question-circle tooltips" title="{{Temps minimum d'attente avant de déclarer l'objet absent (10 sec min).}}"></i></sup>
            </label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="presence_timeout" />
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Reporters}}</div>
            </div>
        </div>


        <div class="form-group">
            <label class="col-lg-4 control-label">{{Reporters Access Token}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="access_token" value="" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Reporters Allow List}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="reporters_allow_list" value="" />
            </div>
        </div>

        <div id="show_avance" style="display:none;">

          <div class="form-group">
              <label class="col-lg-4 control-label">{{Nearest Reporter Timeout}}
              <sup><i class="fa fa-question-circle tooltips" title="{{Délais d'absence en secondes avant que le rapporteur le plus proche soit remis en jeux, quelque soit sa dernière valeure connue de RSSI.}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="nearest_ap_timeout" value="" />
              </div>
          </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{Nearest Reporter RSSI hysteresis interval}}
              <sup><i class="fa fa-question-circle tooltips" title="{{Valeur (en dBm) de l'interval du cycle d'hysteresis avant de changer de rapporteur le plus proche.}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="nearest_ap_hysteresis" value="" />
              </div>
          </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{AP transport interval}}</label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="ap_transport_interval" value="" />
              </div>
          </div>
          <div class="form-group">
              <label class="col-lg-4 control-label">{{AP aging time}}</label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="ap_aging_time" value="" />
              </div>
          </div>

        </div>

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Websocket}}</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label">{{Websocket IP Address}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="ws_ip_address" value="0.0.0.0"/> (0.0.0.0 {{pour adresse locale}})
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Websocket TCP Port}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="ws_port" value="8081" />
            </div>
        </div>

        <div id="show_debug" style="display:none;">

          <div class="form-group">
              <div class="col-sm-4"></div>
              <div class="col-sm-5">
                     <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Debug}}</div>
              </div>
          </div>


<?php if (0) { ?>
          <div class="form-group">
              <label class="col-lg-4 control-label">{{AP transport interval}}</label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="ap_transport_interval" value="" />
              </div>
          </div>
<?php /*endif (0) */ } ?>

        </div>


</fieldset>
</form>

<?php include_file('desktop', 'ArubaIot_Configuration', 'js', 'ArubaIot'); ?>

