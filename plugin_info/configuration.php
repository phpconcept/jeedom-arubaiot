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
                <option value="advanced">{{Mode avanc&eacute;}}</option>
                <option value="debug">{{Mode debug}}</option>
            </select>
        </div>
    </div>


    <fieldset>

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Equipements}}</div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label">{{Delai detection d'absence (sec)}}
            <sup><i class="fa fa-question-circle tooltips" title="{{Temps minimum d'attente avant de d&eacute;clarer l'objet absent (10 sec min).}}"></i></sup>
            </label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="presence_timeout" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label">{{Pr&eacute;sence minimum RSSI}}
            <sup><i class="fa fa-question-circle tooltips" title="{{Valeur minimal du RSSI pour qu'un &eacute;quipement soit d&eacute;clar&eacute; pr&eacute;sent.}}"></i></sup>
            </label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="presence_min_rssi" />
            </div>
        </div>

        <div class="form-group">
            <label class="col-lg-4 control-label">{{Pr&eacute;sence RSSI Interval Cycle d'hyst&eacute;r&eacute;sis}}
            <sup><i class="fa fa-question-circle tooltips" title="{{Valeur (en dBm) de l'interval du cycle d'hysteresis avant de passer en statut absent.}}"></i></sup>
            </label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="presence_rssi_hysteresis" />
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Rapporteurs}}</div>
            </div>
        </div>


        <div class="form-group">
            <label class="col-lg-4 control-label">{{Jeton d'acc&eacute;s}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="access_token" value="" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Liste d'autorisation}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="reporters_allow_list" value="" />
            </div>
        </div>

        <div id="show_avance" style="display:none;">

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Meilleur Rapporteur}}</div>
            </div>
        </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{RSSI minimum pour devenir le rapporteur le plus proche}}
              <sup><i class="fa fa-question-circle tooltips" title="{{Valeur RSSI minimum (en dBm) pour pouvoir devenir un rapporteur le plus proche. (recommand&eacute;e -85).}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="nearest_ap_min_rssi" value="" />
              </div>
          </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{Cycle d'hyst&eacute;r&eacute;sis RSSI du meilleur rapporteur}}
              <sup><i class="fa fa-question-circle tooltips" title="{{Valeur (en dBm) de l'interval du cycle d'hysteresis avant de changer de rapporteur le plus proche.}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="nearest_ap_hysteresis" value="" />
              </div>
          </div>

        <div class="form-group">
            <div class="col-sm-4"></div>
            <div class="col-sm-5">
                   <div style="background-color: #039be5; padding: 2px 5px; color: white; margin: 10px 0; font-weight: bold;">{{Triangulation}}</div>
            </div>
        </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{Nombre maximum de rapporteurs pour la triangulation}}
              <sup><i class="fa fa-question-circle tooltips" title="{{Nombre maximum de Rapporteurs &agrave; conserver dans le cache pour la triangulation. (minimum 3)}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="triangulation_max_ap" value="" />
              </div>
          </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{RSSI minimum pour la triangulation}}
              <sup><i class="fa fa-question-circle tooltips" title="{{RSSI minimum pour prendre en compte le rapporteur dans la triangulation}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="triangulation_min_rssi" value="" />
              </div>
          </div>

          <div class="form-group">
              <label class="col-lg-4 control-label">{{Timeout pour la triangulation}}
              <sup><i class="fa fa-question-circle tooltips" title="{{Temps maxium pendant lequel une valeur de RSSI est conserv&eacute;e}}"></i></sup>
              </label>
              <div class="col-lg-5">
                  <input class="configKey form-control" data-l1key="triangulation_timeout" value="" />
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
            <label class="col-lg-4 control-label">{{Adresse IP du Websocket}}</label>
            <div class="col-lg-5">
                <input class="configKey form-control" data-l1key="ws_ip_address" value="0.0.0.0"/> (0.0.0.0 {{pour adresse locale}})
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Port TCP du Websocket}}</label>
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

        </div>


</fieldset>
</form>

<?php include_file('desktop', 'ArubaIot_Configuration', 'js', 'ArubaIot'); ?>

