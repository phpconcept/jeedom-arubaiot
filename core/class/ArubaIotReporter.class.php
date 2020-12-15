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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php';

class ArubaIotReporter extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

	public static function talkToWebsocket($p_event, $p_data) {

      $v_data = array();
      $v_data['api_version'] = "1.0";
      $v_data['api_key'] = jeedom::getApiKey('ArubaIotReporter');
      $v_data['event'] = array();
      $v_data['event']['name'] = $p_event;
      $v_data['event']['data'] = $p_data;
      $v_data_json = json_encode($v_data);

      //log::add('ArubaIotReporter', 'debug', 'json = ' . $v_data_json);

      $v_url = 'http://127.0.0.1:8081/api';
      $v_request_http = new com_http($v_url);
      $v_request_http->setNoSslCheck(true);
      $v_request_http->setNoReportError(true);
      $v_request_http->setPost($v_data_json);
      $v_return = $v_request_http->exec(15,2);
      if ($v_return === false) {
        log::add('ArubaIotReporter', 'debug', 'Unable to fetch ' . $v_url);
        return;
      } else {
        log::add('ArubaIotReporter', 'debug', 'Post ' . $v_url);
        log::add('ArubaIotReporter', 'debug', 'Result ' . $v_return);
      }



	}


	public static function getReportersForModal() {

      $v_data = array();

      $i=0;
      $v_data[$i]['name'] = "Reporter 1";
      $v_data[$i]['mac'] = "XX:XX:XX:XX:XX:XX";
      $v_data[$i]['ip'] = "10.10.10.10";
      $v_data[$i]['telemetry'] = 1;
      $v_data[$i]['rtls'] = 1;

      return($v_data);

	}




}
