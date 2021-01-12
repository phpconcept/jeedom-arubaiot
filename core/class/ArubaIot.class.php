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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php';

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
        ArubaIotLog::log('ArubaIot', 'info', 'Starting ArubaIot daemon');
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
            ArubaIotLog::log('ArubaIot', 'error', 'Unable to start daemon');
            return false;
        }
    }

    public static function deamon_stop()
    {
        ArubaIotLog::log('ArubaIot', 'info', 'Stopping ArubaIot daemon');
        exec(system::getCmdSudo() . 'systemctl stop ArubaIot-websocket');
    }



	public static function talkToWebsocket($p_event, $p_data) {

      $v_data = array();
      $v_data['api_version'] = "1.0";
      $v_data['api_key'] = jeedom::getApiKey('ArubaIot');
      $v_data['event'] = array();
      $v_data['event']['name'] = $p_event;
      $v_data['event']['data'] = $p_data;
      $v_data_json = json_encode($v_data);

      //ArubaIotLog::log('ArubaIot', 'debug', 'json = ' . $v_data_json);
      $v_port = config::byKey('ws_port', 'ArubaIot');

      $v_url = 'http://127.0.0.1:'.$v_port.'/api';
      $v_request_http = new com_http($v_url);
      $v_request_http->setNoSslCheck(true);
      $v_request_http->setNoReportError(true);
      $v_request_http->setPost($v_data_json);
      $v_return = $v_request_http->exec(15,2);
      if ($v_return === false) {
        ArubaIotLog::log('ArubaIot', 'debug', 'Unable to fetch ' . $v_url);
        return('');
      } else {
        ArubaIotLog::log('ArubaIot', 'debug', 'Post ' . $v_url);
        ArubaIotLog::log('ArubaIot', 'debug', 'Result ' . $v_return);
        return($v_return);
      }

	}


	public static function changeIncludeState($p_state, $p_type='') {

      ArubaIotLog::log('ArubaIot', 'info',  "Change inclusion state to : ".$p_state);

      config::save('include_mode', $p_state, 'ArubaIot');

      if ($p_type != '') {
        $v_list = json_decode($p_type, true);
        $v_type_str = implode(',', $v_list);
        ArubaIotLog::log('ArubaIot', 'info',  "Classes to includes are : ".$v_type_str);
      }

      $v_data = array('state' => $p_state, 'type' => $v_type_str );
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

        None WiFi Types :
                "unclassified",
                "arubaBeacon",
                "iBeacon",
                "arubaTag",
                "zfTag",
                "enoceanSwitch",
                "enoceanSensor",
                "eddystone",
                "assaAbloy",
                "arubaSensor",
                "mysphera",
                "sBeacon"

     *
     */
	public static function supportedDeviceType($p_format='list' ) {

      $v_result = array();

      /*
      $v_class_list = array();
      $v_class_list['auto'] = 'Découvrir automatiquement';
      $v_class_list['enoceanSwitch'] = 'enoceanSwitch';
      $v_class_list['enoceanSensor'] = 'enoceanSensor';
      $v_class_list['arubaTag'] = 'arubaTag';
      $v_class_list['arubaBeacon'] = 'arubaBeacon';
      $v_class_list['iBeacon'] = 'iBeacon';
      $v_class_list['generic'] = 'generic';
        */


      $v_class_list = array('auto' => 'Découvrir automatiquement',
                            'enoceanSwitch' => 'enoceanSwitch',
                            'enoceanSensor' => 'enoceanSensor',
                            'arubaTag' => 'arubaTag',
                            'arubaBeacon' => 'arubaBeacon',
                            'iBeacon' => 'iBeacon',
                            'generic' => 'generic'
                            );

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
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : definedCommands()
     * Description :
     * Parameters :
     * ---------------------------------------------------------------------------
     */
    public static function definedCommands($p_format='list') {
      $v_result = array();

      $val = <<<JSON_EOT
[
  "presence": {
    "name": "Présence",
    "description": "",
    "type" : "info",
    "sub_type" : "binary",
    "visible" : true,
    "history" : true
  },
  "rssi": {
    "name": "RSSI",
    "description": "",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true,
    "min_value" : -110,
    "max_value" : -10
  },
  "illumination": {
    "name": "Illumination",
    "description": "light intensity (Lux)",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true,
    "min_value" : 0
  },
  "occupancy": {
    "name": "Occupancy",
    "description": "",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true,
    "min_value" : 0,
    "max_value" : 100
  },
  "nearest_ap": {
    "name": "Nearest AP",
    "description": "",
    "type" : "info",
    "sub_type" : "string",
    "visible" : true,
    "history" : false
  },
  "triangulation": {
    "name": "Triangulation",
    "description": "",
    "type" : "info",
    "sub_type" : "string",
    "visible" : false,
    "history" : false,
  },
  "temperatureC": {
    "name": "Temperature",
    "description": "temperature in degrees Celcius",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true,
    "min_value" : -127,
    "max_value" : 127
  },
  "humidity": {
    "name": "Humidité",
    "description": "Relative humidity (percentage)",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true,
    "min_value" : 0,
    "max_value" : 100
  },
  "voltage": {
    "name": "Voltage",
    "description": "system voltage level in V",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true
  },
  "resistance": {
    "name": "Résistance",
    "description": "electrical resistance in Ohm",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : false
  },
  "pressure": {
    "name": "Pression",
    "description": "pressure in hPa",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true
  },
  "pressure": {
    "name": "Pression",
    "description": "pressure in hPa",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : true,
    "history" : true
  },
  "VOC": {
    "name": "VOC",
    "description": "volatile organic compounds in ppm",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : false,
    "history" : false,
    "min_value" : 0
  },
  "CO": {
    "name": "CO",
    "description": "carbon monoxide level in ppm",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : false,
    "history" : false,
    "min_value" : 0
  },
  "CO2": {
    "name": "CO2",
    "description": "carbon dioxide level in ppm",
    "type" : "info",
    "sub_type" : "numeric",
    "visible" : false,
    "history" : false,
    "min_value" : 0
  }
]
JSON_EOT;

      $v_cmd_list = array('presence' => 'Présence',
                          'rssi' => 'RSSI',
                          'nearest_ap' => 'Nearest AP',
                          'illumination' => 'Illumination',
                          'occupancy' => 'Occupancy',
                          'triangulation' => 'Triangulation'
                            );

      if ($p_format == 'description') {
        $v_result = $v_cmd_list;
      }
      //else if ($p_format == 'list') {
      else {
        foreach ($v_cmd_list as $v_key => $v_class) {
          $v_result[] = $v_key;
        }
      }

      return($v_result);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : isAllowedCmdForClass()
     * Description :
     * Parameters :
     *   $p_cmd_id : Command ID
     *   $p_class_name : Name of the class
     * Returned value : true if accepted, false if not.
     *   If cmd_id is unknown or class_name is unknown, then it will return true.
     * ---------------------------------------------------------------------------
     */
    public static function isAllowedCmdForClass($p_cmd_id, $p_class_name) {

      // ----- Here is a code-static list of command than can not be
      // automatically added to an object of this class
      // For exemple : no sense to have a presence information for an enocean sensor
      $v_deny_list = array('auto' => '__none',
                           'enoceanSwitch' => 'presence,rssi,triangulation', // comma separated list
                           'enoceanSensor' => 'presence,triangulation',
                           'arubaTag' => '',
                           'arubaBeacon' => 'presence,triangulation',
                           'iBeacon' => '',
                           'generic' => '__none'
                           );

      if (isset($v_deny_list[$p_class_name])) {
        if ($v_deny_list[$p_class_name] == '__none')
          return(true);

        $v_list = explode(',', $v_deny_list[$p_class_name]);
        if (in_array($p_cmd_id, $v_list)) {
          return(false);
        }
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/






    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {

      // ----- Default values if configuration is empty
      if ($this->getConfiguration('mac_address', '') == '')
      {
        ArubaIotLog::log('ArubaIot', 'debug', 'MAC is empty, change to 00:00:00:00:00:00');
        $this->setConfiguration('mac_address', '00:00:00:00:00:00');
      }
      if ($this->getConfiguration('class_type', '') == '')
      {
        ArubaIotLog::log('ArubaIot', 'debug', 'class_type is empty, change to auto');
        $this->setConfiguration('class_type', 'auto');
      }

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

      // ----- Create default cmd (if needed) for this class
      $this->createAllCmd();

      $v_class_type = $this->getConfiguration('class_type');

      // ----- Call only if not in inclusion mode, because then the daemon is awware of all the new devices
      // I had to make a trick by using a device attribute to flag not to send back an api when in inclusion mode, because the
      // global att do not seems to be updated here ...
      $v_trick_save_from_daemon = $this->getConfiguration('trick_save_from_daemon');
      ArubaIotLog::log('ArubaIot', 'debug', "trick_save_from_daemon = ".$v_trick_save_from_daemon."");
      if ( ($v_trick_save_from_daemon == '') || ($v_trick_save_from_daemon == 'off') ) {
//      $v_include_mode = config::byKey('include_mode', 'ArubaIot');
//      if ($v_include_mode == 0) {
        ArubaIotLog::log('ArubaIot', 'debug', "trick_save_from_daemon = ".$v_trick_save_from_daemon.", send refresh api message");
        $v_id = $this->getId();
        $v_mac = $this->getConfiguration('mac_address');
        ArubaIotLog::log('ArubaIot', 'debug', "MAC is :".$v_mac);
        if (($v_mac != '00:00:00:00:00:00') && ($v_mac != '')) {
          $v_data = array('mac_address' => $v_mac, 'id' => $v_id );
          self::talkToWebsocket('device_refresh', $v_data);
        }
        else {
          ArubaIotLog::log('ArubaIot', 'debug', "MAC is null or empty, don't send refresh api message");
        }
      }
      else {
        ArubaIotLog::log('ArubaIot', 'debug', "trick_save_from_daemon = ".$v_trick_save_from_daemon.", don't send refresh api message");
      }

    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
      $v_mac = $this->getConfiguration('mac_address');
      $v_data = array('mac_address' => $v_mac );
      self::talkToWebsocket('device_remove', $v_data);
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

	public function getImage() {
        $v_class = $this->getConfiguration('class_type', '');
	  	$file = 'plugins/ArubaIot/desktop/images/'.$v_class.'.png';
		if(!file_exists(__DIR__.'/../../../../'.$file)){
			return 'plugins/ArubaIot/plugin_info/ArubaIot_icon.png';
		}
		return $file;
	}




    /*     * **********************Getteur Setteur*************************** */



    /**---------------------------------------------------------------------------
     * Method : createAllCmd()
     * Description :
     *   Create all needed "mandatory" cmd for an device. Depending of the device class.
     * Parameters :
     * Returned value : true if ok.
     * ---------------------------------------------------------------------------
     */
    public function createAllCmd() {

      // ----- Get class_name of object
      $v_class_name = $this->getConfiguration('class_type');

      switch ($v_class_name) {
        case 'arubaTag' :
          $this->createCmd('presence', 'Presence', 'info', 'binary', true);
          $this->createCmd('rssi', 'RSSI', 'info', 'numeric', true);
          $this->createCmd('nearest_ap', 'Nearest AP', 'info', 'string', true);
          $this->cmdCreateTriangulation();
        break;
        case 'arubaBeacon' :
          // Battery is by default;
        break;
        case 'enoceanSensor' :
          $this->createCmdIllumination();
          $this->createCmdOccupancy();
        break;
        case 'enoceanSwitch' :
          // will be learn depending of switch type
        break;
        default:
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : createCmd()
     * Description :
     *   Look of $p_cmd_name is allowed for the device class. If yes, then checks
     *   if the command is already created
     *   if not then will create the command and set the default attributes.
     * Parameters :
     *   $p_cmd_id : Command ID
     *   $p_cmd_name : Name of the command (in case of creation need)
     *   $p_cmd_type : 'info', 'action'
     *   $p_cmd_subtype :  'numeric', 'binary', 'string', ...
     *   $p_cmd_isHistorized : true, false
     * Returned value : true on changed value, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function createCmd($p_cmd_id, $p_cmd_name='', $p_cmd_type='info', $p_cmd_subtype='string', $p_cmd_isHistorized=false) {

      // ----- Get class_name of object
      $v_class_name = $this->getConfiguration('class_type');

      // ----- Look if this command is allowed for this class_name
      if (!ArubaIot::isAllowedCmdForClass($p_cmd_id, $v_class_name)) {
        ArubaIotTool::log('debug', "Command '".$p_cmd_id."' not allowed for this class_name '".$v_class_name."'. Look at settings.");
        return(false);
      }

      // ----- Look for existing command
      $v_cmd = $this->getCmd(null, $p_cmd_id);

      // ----- Look if command need to be created
      if (!is_object($v_cmd)) {
        ArubaIotLog::log('ArubaIot', 'debug', "Create Cmd '".$p_cmd_id."' for device.");
        $v_cmd = new ArubaIotCmd();
        $v_cmd->setName(__($p_cmd_name, __FILE__));

        $v_cmd->setLogicalId($p_cmd_id);
        $v_cmd->setEqLogic_id($this->getId());
        $v_cmd->setType($p_cmd_type);
        $v_cmd->setSubType($p_cmd_subtype);
        $v_cmd->setIsHistorized($p_cmd_isHistorized);
        $v_cmd->save();
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : createCmdIllumination()
     * Description :
     * Parameters :
     * Returned value : true on changed value, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function createCmdIllumination() {

      // ----- Look if this command is allowed for this class_name
      $p_cmd_id = 'illumination';
      $v_class_name = $this->getConfiguration('class_type');
      if (!ArubaIot::isAllowedCmdForClass($p_cmd_id, $v_class_name)) {
        ArubaIotTool::log('debug', "Command '".$p_cmd_id."' not allowed for this class_name '".$v_class_name."'. Look at settings.");
        return(false);
      }

      // ----- Look for existing command
      $v_cmd = $this->getCmd(null, 'illumination');

      // ----- Look if command need to be created
      if (!is_object($v_cmd)) {
        ArubaIotLog::log('ArubaIot', 'debug', "Create Cmd '".$p_cmd_id."' for device.");
        $v_cmd = new ArubaIotCmd();
        $v_cmd->setName(__("Illumination", __FILE__));

        $v_cmd->setLogicalId('illumination');
        $v_cmd->setEqLogic_id($this->getId());
        $v_cmd->setType('info');
        $v_cmd->setSubType('numeric');
        $v_cmd->setIsHistorized(true);
        $v_cmd->save();
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : createCmdOccupancy()
     * Description :
     * Parameters :
     * Returned value : true on changed value, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function createCmdOccupancy() {

      // ----- Look if this command is allowed for this class_name
      $p_cmd_id = 'occupancy';
      $v_class_name = $this->getConfiguration('class_type');
      if (!ArubaIot::isAllowedCmdForClass($p_cmd_id, $v_class_name)) {
        ArubaIotTool::log('debug', "Command '".$p_cmd_id."' not allowed for this class_name '".$v_class_name."'. Look at settings.");
        return(false);
      }

      // ----- Look for existing command
      $v_cmd = $this->getCmd(null, 'occupancy');

      // ----- Look if command need to be created
      if (!is_object($v_cmd)) {
        ArubaIotLog::log('ArubaIot', 'debug', "Create Cmd '".$p_cmd_id."' for device.");
        $v_cmd = new ArubaIotCmd();
        $v_cmd->setName(__("Occupancy", __FILE__));

        $v_cmd->setLogicalId('occupancy');
        $v_cmd->setEqLogic_id($this->getId());
        $v_cmd->setType('info');
        $v_cmd->setSubType('numeric');
        $v_cmd->setIsHistorized(true);
        $v_cmd->save();
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : createAndUpdateCmd()
     * Description :
     * Parameters :
     *   $p_cmd_id : Command ID
     *   $p_cmd_value : Value to be updated for the command
     *   $p_cmd_name : Name of the command (in case of creation need)
     *   $p_cmd_type : 'info', 'action'
     *   $p_cmd_subtype :  'numeric', 'binary', 'string', ...
     *   $p_cmd_isHistorized : true, false
     * Returned value : true on changed value, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function createAndUpdateCmd($p_cmd_id, $p_cmd_value, $p_cmd_name='', $p_cmd_type='info', $p_cmd_subtype='string', $p_cmd_isHistorized=false) {

      // ----- Look if cmd or crete it
      if (!$this->createCmd($p_cmd_id, $p_cmd_name, $p_cmd_type, $p_cmd_subtype, $p_cmd_isHistorized)) {
        return(false);
      }

      // ----- Set the value and update the flag
      $v_changed_flag = $this->checkAndUpdateCmd($p_cmd_id, $p_cmd_value);

      return($v_changed_flag);
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : cmdCreateTriangulation()
     * Description :
     * Parameters :
     * Returned value : true on created or already created, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function cmdCreateTriangulation() {

      // ----- Look if this command is allowed for this class_name
      $p_cmd_id = 'triangulation';
      $v_class_name = $this->getConfiguration('class_type');
      if (!ArubaIot::isAllowedCmdForClass($p_cmd_id, $v_class_name)) {
        ArubaIotTool::log('debug', "Command '".$p_cmd_id."' not allowed for this class_name '".$v_class_name."'. Look at settings.");
        return(false);
      }

      // ----- Look for existing command
      $v_cmd = $this->getCmd(null, 'triangulation');

      // ----- Look if command need to be created
      if (!is_object($v_cmd)) {
        ArubaIotLog::log('ArubaIot', 'debug', "Create Cmd '".$p_cmd_id."' for device.");
        $v_cmd = new ArubaIotCmd();
        $v_cmd->setName(__("Triangulation", __FILE__));

        $v_cmd->setLogicalId('triangulation');
        $v_cmd->setEqLogic_id($this->getId());
        $v_cmd->setType('info');
        $v_cmd->setSubType('string');
        $v_cmd->setIsHistorized(false);
        $v_cmd->setIsVisible(0);
        $v_cmd->save();
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : cmdUpdateTriangulation()
     * Description :
     * Parameters :
     * Returned value : true on changed value, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function cmdUpdateTriangulation($p_reporter_mac, $p_rssi, $p_timestamp) {

      // ----- Look if cmd or create it
      if (!$this->cmdCreateTriangulation()) {
        return(false);
      }

      // ----- Look for existing command
      $v_cmd = $this->getCmd(null, 'triangulation');

      // ----- Get latest value
      $v_latest_value = $v_cmd->execCmd();
      ArubaIotLog::log('debug', "Latest triangulation value is :".$v_latest_value);

      // ----- To array
      $v_triangulation = json_decode($v_latest_value, true);
      if (!is_array($v_triangulation)) {
        $v_triangulation = array();
      }

      if (isset($v_triangulation[$p_reporter_mac])) {
        if ($v_triangulation[$p_reporter_mac]['rssi'] == $p_rssi) {
          ArubaIotLog::log('debug', "Same RSSI value for this reporter :".$p_reporter_mac);
          return(false);
        }
        else {
          ArubaIotLog::log('debug', "Updating RSSI value for this reporter :".$p_reporter_mac);
        }
      }
      else {
        ArubaIotLog::log('debug', "New reporter for triangulation :".$p_reporter_mac);
      }
      $v_triangulation[$p_reporter_mac]['rssi'] = $p_rssi;
      $v_triangulation[$p_reporter_mac]['timestamp'] = $p_timestamp;

      // ----- Keep only X top best reporters, with best timestamp
      $v_target_max = config::byKey('triangulation_max_ap', 'ArubaIot');
      if ($v_target_max < 3) $v_target_max = 3;
      ArubaIotLog::log('debug', "Current number of reporters for triangulation :".sizeof($v_triangulation));
      ArubaIotLog::log('debug', "Maximum number of reporters configured :".$v_target_max);
      // TBC : keep 3 best
      if (sizeof($v_triangulation) > $v_target_max) {
        $v_rssi_list = array();
        foreach ($v_triangulation as $key => $item) {
          $v_rssi_list[$item['rssi']] = $key;
        }
        krsort($v_rssi_list);
        $v_triangulation_new = array();
        $i=0;
        foreach ($v_rssi_list as $item) {
          $v_triangulation_new[$item] = $v_triangulation[$item];
          $i++;
          if ($i >= $v_target_max)
            break;
        }
        $v_triangulation = $v_triangulation_new;
      }

      // ----- JSONify
      $v_value = json_encode($v_triangulation);
      ArubaIotLog::log('debug', "New triangulation value :".$v_value);

      // ----- Set the value and update the flag
      $v_changed_flag = $this->checkAndUpdateCmd('triangulation', $v_value);

      return($v_changed_flag);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : cmdUpdateNearestAP()
     * Description :
     * Parameters :
     * Returned value : true on changed value, false otherwise.
     * ---------------------------------------------------------------------------
     */
    public function cmdUpdateNearestAP($p_nearest_ap_mac) {

      // ----- Look for existing command
      $v_cmd = $this->getCmd(null, 'nearest_ap');

      // ----- Look if command need to be created
      if (!is_object($v_cmd)) {
        return(false);
      }

      // ----- Set the value and update the flag
      $v_changed_flag = $this->checkAndUpdateCmd('nearest_ap', $p_nearest_ap_mac);

      return($v_changed_flag);
    }
    /* -------------------------------------------------------------------------*/



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
        
        //ArubaIotLog::log('ArubaIot', 'info',  "Commande reçue !");
        ArubaIotLog::log('info', "Commande reçue !");

        if ($this->getType() != 'action') {
			return;
		}
		$eqLogic = $this->getEqlogic();

    }

    /*     * **********************Getteur Setteur*************************** */
}


