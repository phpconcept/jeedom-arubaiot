<?php
/**
 * ArubaWssDevice CLass Extension for Jeedom
 *
 *
 */

  // ----- Override device PHP Class to be used as an extension of ArubaWssDevice class
  define('ARUBA_WSS_DEVICE_CLASS', 'ArubaWssDeviceJeedom');
  
  // ----- Override path because vendor/ directory is not at the same place in jeedom plugins  
  //define('ARUBA_WSS_THIRDPARTY_LOAD', __DIR__.'/../../../../../plugins/ArubaIot/3rparty/vendor/autoload.php');

  // ----- Jeedom ArubaIot plugin classes to be included
  require_once __DIR__.'/../../../../../core/php/core.inc.php';
  require_once __DIR__.'/../../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php';


  /**---------------------------------------------------------------------------
   * Class : ArubaWssDeviceJeedom
   * Description :
   *   This object is used to extend the generic Aruba Iot Websocket Server 
   *   to be used as a Jeedom plugin.
   * ---------------------------------------------------------------------------
   */
  class ArubaWssDeviceJeedom extends ArubaWssDevice  {
    protected $jeedom_object_id = '';

    /**---------------------------------------------------------------------------
     * Method : extension_log()
     * Description :
     * ---------------------------------------------------------------------------
     */
    static function extension_log($p_type, $p_sub_type, $p_level, $p_message) {
      static $s_fd = null;
      
      // ----- Filter debug type (too many messages)
      if ($p_type == 'debug') {
        return;
      }
      
      // ----- Open log file ig not yet opened
      if ($s_fd === null) {
        $s_fd = fopen('/var/www/html/log/ArubaIot_daemon', 'a');
      }
      
      // ----- Write message
      fwrite($s_fd, '['.date("Y-m-d H:i:s").'] ['.$p_type.']:'.$p_message."\n");
      
      // ----- No need to close, to lower the load to reopen
      //fclose($fd);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : extension_init()
     * Description :
     * ---------------------------------------------------------------------------
     */
    static function extension_init(&$p_args) {

      ArubaWssTool::log('info', "Initialize Jeedom Extension");
      
      // ----- Reset/create list of devices to load
      $v_list = array();
      
      // ----- Get all equipment of from plugin 'ArubaIot'
      //ArubaWssTool::log('info', "Learning allowed active devices");
      $v_eq_list = eqLogic::byType('ArubaIot');

      // ----- Adding all created device in the allowed list
      foreach($v_eq_list as $v_eq_device) {
        $v_mac = strtoupper($v_eq_device->getConfiguration('mac_address', ''));
        if ($v_mac != '') {
          $v_list[] = $v_mac;
        }
      }

      // ----- Prepare device list
      $p_args['devices_list'] = implode(',', $v_list);
      
      // ----- Get values from Jeedom configuration
      $p_args['server_ip'] = config::byKey('ws_ip_address', 'ArubaIot');
      $p_args['server_port'] = config::byKey('ws_port', 'ArubaIot');
      $p_args['reporters_key'] = trim(config::byKey('access_token', 'ArubaIot'));
      $p_args['reporters_list'] = trim(config::byKey('reporters_allow_list', 'ArubaIot'));

      $p_args['presence_timeout'] = trim(config::byKey('presence_timeout', 'ArubaIot'));
      $p_args['presence_min_rssi'] = trim(config::byKey('presence_min_rssi', 'ArubaIot'));
      $p_args['presence_rssi_hysteresis'] = trim(config::byKey('presence_rssi_hysteresis', 'ArubaIot'));
      $p_args['nearest_ap_hysteresis'] = trim(config::byKey('nearest_ap_hysteresis', 'ArubaIot'));
      $p_args['nearest_ap_timeout'] = trim(config::byKey('nearest_ap_timeout', 'ArubaIot'));
      $p_args['nearest_ap_min_rssi'] = trim(config::byKey('nearest_ap_min_rssi', 'ArubaIot'));

      $p_args['api_key'] = trim(jeedom::getApiKey('ArubaIot'));
                                 
      return;
    }
    /* -------------------------------------------------------------------------*/
    
    /**---------------------------------------------------------------------------
     * Method : createMe()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function createMe() {
    
      ArubaWssTool::log('debug', "Create a Jeedom Object for '".$this->mac_address."'");

      $v_jeedom_device = new ArubaIot();
      
      $v_name = '';
      if ($this->name != '') {
        $v_name = $this->name;
      }
      else {
        $v_name = $this->classname." ".$this->mac_address;
      }
      ArubaWssTool::log('debug', "Set name to : ".$v_name);
      $v_jeedom_device->setName($v_name);
      
      ArubaWssTool::log('debug', "Set jeedom class type to 'ArubaIot'");
      $v_jeedom_device->setEqType_name('ArubaIot');
      
      ArubaWssTool::log('debug', "Set properties.");
      $v_jeedom_device->setConfiguration('mac_address', $this->mac_address);
      //$v_jeedom_device->setConfiguration('class_type', $this->classname);
      $v_jeedom_device->setConfiguration('class_type', $this->vendor_id.':'.$this->model_id);
      $v_jeedom_device->setConfiguration('vendor_name', $this->vendor_name);
      $v_jeedom_device->setConfiguration('local_name', $this->local_name);
      $v_jeedom_device->setConfiguration('model',  $this->model);
      if ($this->battery_value < 101) {
        $v_jeedom_device->batteryStatus($this->battery_value);
      }      
      
      ArubaWssTool::log('debug', "Set enable.");
      $v_jeedom_device->setIsEnable(1);
      
      // ----- Here is a trick I need to control the jeedom not to send back api request on refresh
      $v_jeedom_device->setConfiguration('trick_save_from_daemon', 'on');
      $v_jeedom_device->save();
      $v_jeedom_device->setConfiguration('trick_save_from_daemon', 'off');
    
      // ----- Store the jeedom object ID for future use.
      $this->jeedom_object_id = $v_jeedom_device->getId();
          
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : loadMe()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function loadMe() {
    
      // ----- Reset jeedom id, will be reset if object found
      $this->jeedom_object_id = '';
      
      // ----- Search for Jeedom Id for this mac@
      $v_eq_list = eqLogic::byType('ArubaIot');
      foreach($v_eq_list as $v_eq_device) {
        $v_mac = strtoupper($v_eq_device->getConfiguration('mac_address', ''));
        if ($v_mac == $this->mac_address) {
          // ----- Store Jeedom object
          $this->jeedom_object_id = $v_eq_device->getId();
          
          // ----- Get name from Jeedom Human name
          // received name is : [parent][parent][name] : extract name
          $v_hname = $v_eq_device->getHumanName();
          ArubaWssTool::log('info', "Load device : ".$v_hname." (".$v_mac.")");
          $v_name_list = str_replace(']', '', $v_hname);
          $v_items = explode('[', $v_name_list);
          $v_name = $v_hname;
          if (sizeof($v_items) > 0) {
            $v_name = $v_items[sizeof($v_items)-1];
          }
          $this->setName($v_name);
          
          // ----- Get classname and vendor/model ids
          // TBC : to be modified
          $v_classname = $v_eq_device->getConfiguration('class_type', '');
          //ArubaWssTool::log('info', "Device classname : ".$v_classname." ");
          if ($v_classname == 'auto') {
            $this->classname = 'unclassified:unclassified';
            $this->classname_autolearn = true;
          }
          else if (strpos($v_classname, ':') !== false) {
            $v_item = explode(':', $v_classname);
            $this->vendor_id = $v_item[0];
            $this->model_id = $v_item[1];
            $this->classname_autolearn = false;
          }
          // ----- Need to extract old class name to new names
          // Here is the best way I found to update the device type ...
          else {
            if ($v_classname == 'generic') {
              $v_classname = 'unclassified';
            }
            $v_new_classname = ArubaWssTool::arubaClassToVendor($v_classname);
            $this->vendor_id = $v_new_classname['vendor_id'];
            $this->model_id = $v_new_classname['model_id'];
            $this->classname = $this->vendor_id.':'.$this->model_id;
            $this->classname_autolearn = false;
            ArubaWssTool::log('info', "New device classname : ".$this->classname." ");
            
            // ----- Save in Jeedom
            $v_eq_device->setConfiguration('trick_save_from_daemon', 'on');
            $v_eq_device->setConfiguration('class_type', $this->classname);
            $v_eq_device->save();
            $v_eq_device->setConfiguration('trick_save_from_daemon', 'off');
          }

          return(true);
        }
      }
      
      return(false);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : doPostActionTelemetry()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function doPostActionTelemetry($p_type='') {
      ArubaWssTool::log('debug:jeedom', "ArubaWssDeviceJeedom::doPostActionTelemetry()");
      
      $v_refresh_flag = false;
      $v_config_flag = false;
      
      // ----- Get associated Jeedom object
      $v_jeedom_object = eqLogic::byId($this->jeedom_object_id);
      if ($v_jeedom_object === null) {
        // Should not occur, but if in removing an object phase ...
        ArubaWssTool::log('debug', "-> Fail to find a Jeedom object with this ID ...");
        return;
      }
      
      // ----- Look for all changed flags
      foreach ($this->change_flag as $v_key => $v_flag) {
      /* si le flag est l� alors c'est qu'il est actif
        if (!$v_flag) {
          continue;
        }
        */
      
        switch ($v_key) {
          case 'presence' :
            $v_refresh_flag = $v_jeedom_object->createAndUpdateCmd('presence', $this->presence) || $v_refresh_flag;
            ArubaWssTool::log('debug', "-> Jeedom update presence");
          break;

          case 'nearest_ap' :
            $v_reporter_name = $this->nearest_ap_mac;
            if (($v_reporter = ArubaWssTool::getReporterByMac($this->nearest_ap_mac)) != null) {
              $v_reporter_name = $v_reporter->getName();
            }
            $v_refresh_flag = $v_jeedom_object->cmdUpdateNearestAP($this->nearest_ap_mac, $v_reporter_name) || $v_refresh_flag;
            $v_refresh_flag = $v_jeedom_object->checkAndUpdateCmd('rssi', $this->nearest_ap_rssi) || $v_refresh_flag;
            ArubaWssTool::log('debug', "-> Jeedom update nearest_ap and rssi (".$this->nearest_ap_rssi.")");
          break;

          case 'telemetry_value' :
            $v_refresh_flag = $this->doUpdateTelemetryValues($v_jeedom_object, $v_flag) || $v_refresh_flag;
            ArubaWssTool::log('debug', "-> Jeedom update telemetry value");
          break;

          case 'battery' :
            $v_jeedom_object->batteryStatus($this->battery_value);
            //$v_refresh_flag = true;
            ArubaWssTool::log('debug', "-> Jeedom update battery (no flag)");
          break;

          case 'classname' :
            $v_jeedom_object->setConfiguration('class_type', $this->classname);
            $v_config_flag = true;
            ArubaWssTool::log('debug', "-> Jeedom update classname");
          break;
          case 'vendor_name' :
            $v_jeedom_object->setConfiguration('vendor_name', $this->vendor_name);
            $v_config_flag = true;
            ArubaWssTool::log('debug', "-> Jeedom update vendor_name");
          break;
          case 'local_name' :
            $v_jeedom_object->setConfiguration('local_name', $this->local_name);
            $v_config_flag = true;
            ArubaWssTool::log('debug', "-> Jeedom update local_name");
          break;
          case 'model' :
            $v_jeedom_object->setConfiguration('model',  $this->model);
            $v_config_flag = true;
            ArubaWssTool::log('debug', "-> Jeedom update model");
          break;
          case 'rssi' :
            // TBC
          break;
          default :
            ArubaWssTool::log('debug', "-> Unexpected change flag called '".$v_key."'");
        }      
      }
      
      // ----- Look for need to save config values
      if ($v_config_flag) {
        // Here is a trick I need to control the jeedom not to send back api request on refresh
        $v_jeedom_object->setConfiguration('trick_save_from_daemon', 'on');
        $v_jeedom_object->save();
        $v_jeedom_object->setConfiguration('trick_save_from_daemon', 'off');
      }
      
      // ----- Look for need to update widget
      if ($v_refresh_flag) {
        $v_jeedom_object->refreshWidget();
      }

    }
    /* -------------------------------------------------------------------------*/



    /**---------------------------------------------------------------------------
     * Method : doUpdateTelemetryValues()
     * Description :
     * ---------------------------------------------------------------------------
     */
    private function doUpdateTelemetryValues(&$p_jeedom_object, $v_flag_list) {
      ArubaWssTool::log('debug', "ArubaWssDeviceJeedom::doUpdateTelemetryValues()");
      $v_refresh_flag = false;
      
      foreach ($v_flag_list as $v_name => $v_value) {
        ArubaWssTool::log('debug', " Look for '".$this->telemetry_value_list[$v_name]['name']."'");
        if (isset($this->telemetry_value_list[$v_name])) {
          ArubaWssTool::log('debug', " -> Update '".$this->telemetry_value_list[$v_name]['name']."'");
          $v_refresh_flag = $p_jeedom_object->createAndUpdateCmd($this->telemetry_value_list[$v_name]['name'], $this->telemetry_value_list[$v_name]['value']) || $v_refresh_flag;      
        }
      }
      
      return($v_refresh_flag);
    }
    /* -------------------------------------------------------------------------*/

  }
  /* -------------------------------------------------------------------------*/



?>
