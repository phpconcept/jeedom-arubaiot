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
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class ArubaIot extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */

    public static function deamon_info()
    {
        $return = array();

        $status = trim(shell_exec('systemctl is-active ArubaIot-websocket'));
        $return['state'] = ($status === 'active') ? 'ok' : 'nok';

        $return['launchable'] = 'ok';
        if (!file_exists('/etc/systemd/system/ArubaIot-websocket.service')) {
            $return['launchable'] = 'nok';
            $return['launchable_message'] = __('Le démon n\'est pas installé ', __FILE__);
        }
        return $return;
    }

    public static function deamon_start($_debug = false)
    {
        log::add('ArubaIot', 'info', 'Starting ArubaIot daemon');
        exec(system::getCmdSudo() . 'systemctl restart ArubaIot-websocket');
        $i = 0;
        while ($i < 30) {
            $deamon_info = self::deamon_info();
            if ($deamon_info['state'] == 'ok') {
                break;
            }
            sleep(1);
            $i++;
        }
        if ($i >= 30) {
            log::add('ArubaIot', 'error', 'Unable to start daemon');
            return false;
        }
    }

    public static function deamon_stop()
    {
        log::add('ArubaIot', 'info', 'Stopping ArubaIot daemon');
        exec(system::getCmdSudo() . 'systemctl stop ArubaIot-websocket');
    }


    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

      // ----- Default values if configuration is empty
      if ($this->getConfiguration('mac_address', '') == '')
        $this->setConfiguration('mac_address', '00:00:00:00:00:00');
      if ($this->getConfiguration('class_type', '') == '')
        $this->setConfiguration('class_type', 'auto');

    }

    public function postInsert() {
        
    }

    public function preSave() {
      //message::add('ArubaIot',  'PreSave ...' );

      // ----- Change to uppercase
      // Should check the value and through en error.
      $v_val = $this->getConfiguration('mac_address', '');
	  $this->setConfiguration('mac_address', strtoupper($v_val));

    }

    public function postSave() {

      $v_class_type = $this->getConfiguration('class_type');

      // ----- Default cmd for all
      $info = $this->getCmd(null, 'rssi');
      if (!is_object($info)) {
        log::add('ArubaIot',  'debug', "Activate Cmd 'RSSI' for device.");
        $info = new arubacentralCmd();
        $info->setName(__('RSSI', __FILE__));

        $info->setLogicalId('rssi');
        $info->setEqLogic_id($this->getId());
        $info->setType('info');
        $info->setSubType('string');
        $info->save();
      }

      // ----- Look for commands to add depending of class_type
      if ($v_class_type == "enoceanSensor") {

        $info = $this->getCmd(null, 'illumination');
        if (!is_object($info)) {
          log::add('ArubaIot',  'debug', "Activate Cmd 'illumination' for device.");
          $info = new arubacentralCmd();
          $info->setName(__('Illumination', __FILE__));

          $info->setLogicalId('illumination');
          $info->setEqLogic_id($this->getId());
          $info->setType('info');
          $info->setSubType('numeric');
          $info->setIsHistorized(true);
          $info->save();
        }

        $info = $this->getCmd(null, 'occupancy');
        if (!is_object($info)) {
          log::add('ArubaIot',  'debug', "Activate Cmd 'occupancy' for device.");
          $info = new arubacentralCmd();
          $info->setName(__('Occupancy', __FILE__));

          $info->setLogicalId('occupancy');
          $info->setEqLogic_id($this->getId());
          $info->setType('info');
          $info->setSubType('numeric');
          $info->setIsHistorized(true);
          $info->save();
        }

      }

      // ----- Look for commands to add depending of class_type
      if (($v_class_type == "arubaTag") || ($v_class_type == "generic")) {

        $info = $this->getCmd(null, 'presence');
        if (!is_object($info)) {
          log::add('ArubaIot',  'debug', "Activate Cmd 'presence' for device.");
          $info = new arubacentralCmd();
          $info->setName(__('Presence', __FILE__));

          $info->setLogicalId('presence');
          $info->setEqLogic_id($this->getId());
          $info->setType('info');
          $info->setSubType('binary');
          $info->setIsHistorized(true);
          $info->save();
        }

      }

    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

     /*      A modifier en fonction des devices IoT
	public static function getImage() {
		$file = 'plugins/ArubaIot/core/config/devices/' . self::getImgFilePath($this->getConfiguration('device'));
		if(!file_exists(__DIR__.'/../../../../'.$file)){
			return 'plugins/ArubaIot/plugin_info/ArubaIot_icon.png';
		}
		return $file;
	}
    */




    /*     * **********************Getteur Setteur*************************** */

	public static function talkToWebsocket($p_event, $p_data) {

      $v_data = array();
      $v_data['api_version'] = "1.0";
      $v_data['api_key'] = jeedom::getApiKey('ArubaIot');
      $v_data['event'] = array();
      $v_data['event']['name'] = $p_event;
      $v_data['event']['data'] = $p_data;
      $v_data_json = json_encode($v_data);

      //log::add('ArubaIot', 'debug', 'json = ' . $v_data_json);

      $v_url = 'http://127.0.0.1:8081/api';
      $v_request_http = new com_http($v_url);
      $v_request_http->setNoSslCheck(true);
      $v_request_http->setNoReportError(true);
      $v_request_http->setPost($v_data_json);
      $v_return = $v_request_http->exec(15,2);
      if ($v_return === false) {
        log::add('ArubaIot', 'debug', 'Unable to fetch ' . $v_url);
        return;
      } else {
        log::add('ArubaIot', 'debug', 'Post ' . $v_url);
        log::add('ArubaIot', 'debug', 'Result ' . $v_return);
      }



	}


	public static function changeIncludeState($_state) {

      log::add('ArubaIot', 'info',  "Change inclusion state to : ".$_state);

      config::save('include_mode', $_state, 'ArubaIot');

      $v_data = array('state' => $_state, 'toto' => 'titi' );
      self::talkToWebsocket('include_mode', $v_data);

	}


    /*
     * $p_format :
     *   'list' : just the list of class type supported in an array
     *   'description' : a list with class as key and description of the class
     *
     *  Today defined class in Aruba protobuf description :
     *
        enum deviceClassEnum {
            unclassified                            = 0;     ==> generic
            arubaBeacon                             = 1;
            arubaTag                                = 2;
            zfTag                                   = 3;
            stanleyTag                              = 4;
            virginBeacon                            = 5;
            enoceanSensor                           = 6;
            enoceanSwitch                           = 7;
            iBeacon                                 = 8;
            allBleData                              = 9;
            RawBleData                              = 10;
            eddystone                               = 11;
            assaAbloy                               = 12;
            arubaSensor                             = 13;
            abbSensor                               = 14;
            wifiTag                                 = 15;
            wifiAssocSta                            = 16;
            wifiUnassocSta                          = 17;
            mysphera                                = 18;
            sBeacon                                 = 19;
        }
     *
     */
	public static function supportedDeviceType($p_format='list' ) {

      $v_result = array();
      $v_class_list = array();
      $v_class_list['auto'] = 'Découvrir automatiquement';
      $v_class_list['enoceanSwitch'] = 'enoceanSwitch';
      $v_class_list['enoceanSensor'] = 'enoceanSensor';
      $v_class_list['arubaTag'] = 'arubaTag';
      $v_class_list['arubaBeacon'] = 'arubaBeacon';
      $v_class_list['iBeacon'] = 'iBeacon';
      $v_class_list['generic'] = 'generic';

      if ($p_format == 'description') {
        $v_result = $v_class_list;
      }
      //else if ($p_format == 'list') {
      else {
        foreach ($v_class_list as $v_key => $v_class) {
          $v_result[] = $v_key;
        }
      }

      return($v_result);
	}

    /*
     * $p_device_type :
     *   can be a single string like 'arubaTag' or a comma separated list
     *   'arubaTab,arubaBeacon'. In this last case, a true will be returned
     *   if all the device types are valid
     */
	public static function isValidDeviceType($p_device_type) {
      $v_class_list = self::supportedDeviceType(description);
      $v_list = explode(',', $p_device_type);
      $v_result = false;
      foreach ($v_list as $v_item) {
        if ($v_item != '')
          $v_result = $v_result && isset($v_class_list[$v_item]);
      }
      return($v_result);
    }



}

class ArubaIotCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
        log::add('ArubaIot', 'info',  "Commande reçue !");

        if ($this->getType() != 'action') {
			return;
		}
		$eqLogic = $this->getEqlogic();

    }

    /*     * **********************Getteur Setteur*************************** */
}


