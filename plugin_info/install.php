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

function ArubaIot_install() {


  // ----- Websocket - default parameters
  config::save('ws_ip_address', '0.0.0.0', 'ArubaIot');
  config::save('ws_port', 8081, 'ArubaIot');

  // ----- Reporters - defaults
  config::save('reporters_allow_list', '', 'ArubaIot');
  config::save('access_token', '', 'ArubaIot');

  config::save('nearest_ap_hysteresis', 5, 'ArubaIot');
  config::save('nearest_ap_min_rssi', -85, 'ArubaIot');

  config::save('triangulation_max_ap', 5, 'ArubaIot');
  config::save('triangulation_timeout', 3600, 'ArubaIot');
  config::save('triangulation_min_rssi', -90, 'ArubaIot');

  // ----- Devices - defaults
  config::save('presence_timeout', 60, 'ArubaIot');
  config::save('presence_min_rssi', -85, 'ArubaIot');
  config::save('presence_rssi_hysteresis', 5, 'ArubaIot');

  // ----- Internal flag
  config::save('include_mode', 0, 'ArubaIot');

      // ----- Internal flag
      // I had to make a trick by using a device attribute to flag not to send back an api to websocket when in inclusion mode,
      // because the global att do not seems to be updated here ...
  config::save('trick_save_from_daemon', 'off', 'ArubaIot');

  // ----- Start Daemon
  log::add('ArubaIot', 'info', 'Installing ArubaIot Websocket daemon');

  // ----- Define the daemon to the system
  exec(system::getCmdSudo() . 'cp '.dirname(__FILE__).'/../resources/ArubaIot-websocket.service /etc/systemd/system/ArubaIot-websocket.service');
  exec(system::getCmdSudo() . 'systemctl daemon-reload');
  exec(system::getCmdSudo() . 'systemctl start ArubaIot-websocket');
  exec(system::getCmdSudo() . 'systemctl enable ArubaIot-websocket');

  // ----- Check installation
  $active = trim(shell_exec('systemctl is-active ArubaIot-websocket'));
  $enabled = trim(shell_exec('systemctl is-enabled ArubaIot-websocket'));
  if ($active !== 'active' || $enabled !== 'enabled') {
      log::add('ArubaIot', 'error', "ArubaIot Websocket daemon is not fully installed ($active / $enabled)");
  }
  log::add('ArubaIot', 'info', "ArubaIot Websocket daemon installed ($active / $enabled)");

}

function ArubaIot_update() {
    
  // ----- Add new attributes
  $v_val = config::searchKey('triangulation_timeout', 'ArubaIot');
  if (sizeof($v_val) == 0) {
    config::save('triangulation_timeout', 3600, 'ArubaIot');
  }

  $v_val = config::searchKey('triangulation_min_rssi', 'ArubaIot');
  if (sizeof($v_val) == 0) {
    config::save('triangulation_min_rssi', -90, 'ArubaIot');
  }
  
  $v_val = config::searchKey('presence_min_rssi', 'ArubaIot');
  if (sizeof($v_val) == 0) {
    config::save('presence_min_rssi', -85, 'ArubaIot');
  }
  
  $v_val = config::searchKey('presence_rssi_hysteresis', 'ArubaIot');
  if (sizeof($v_val) == 0) {
    config::save('presence_rssi_hysteresis', 5, 'ArubaIot');
  }
  
  // ----- Restart Daemon
  log::add('ArubaIot', 'info', 'Updating (restart) ArubaIot Websocket daemon');
  exec(system::getCmdSudo() . 'systemctl restart ArubaIot-websocket');

}


function ArubaIot_remove() {

  log::add('ArubaIot', 'info', 'Removing ArubaIot Websocket daemon');
  exec(system::getCmdSudo() . 'systemctl disable ArubaIot-websocket');
  exec(system::getCmdSudo() . 'systemctl stop ArubaIot-websocket');
  exec(system::getCmdSudo() . 'rm /etc/systemd/system/ArubaIot-websocket.service');
  exec(system::getCmdSudo() . 'systemctl daemon-reload');
  log::add('ArubaIot', 'info', "ArubaIot Websocket daemon removed");

}

?>
