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
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Websocket IP Address}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="ws_ip_address" value="0.0.0.0"/> (0.0.0.0 {{pour adresse locale}})
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Websocket TCP Port}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="ws_port" value="8081" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Reporters Allow List}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="reporters_allow_list" value="8081" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Device Types Allow List}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="device_type_allow_list" value="8081" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Délais détection d'absence (sec)}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="presence_timeout" />
            </div>
        </div>
               <div class="form-group">
                 <label class="col-lg-4 control-label">{{Allow Automatic Device Insertion}}</label>
                 <div class="col-lg-4">
                     <input type="checkbox" class="configKey" data-l1key="auto_insertion" />
                 </div>
        </div>

</fieldset>
</form>

