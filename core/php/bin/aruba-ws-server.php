<?php
/**
 * Aruba Websocket Demo Server
 *
 *
 */
  ini_set('display_errors', '1');

  // ----- Jeedom include
  require_once dirname(__FILE__) . '/../../../../../core/php/core.inc.php';
  require_once dirname(__FILE__) . '/../../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php';

  // ----- 3rd Part libraries includes
  $loader = require __DIR__ . '/../../../3rparty/vendor/autoload.php';
  $loader->addPsr4('aruba_telemetry\\', __DIR__);

  // ----- Namespaces
  use GuzzleHttp\Psr7\Response;
  use Ratchet\RFC6455\Handshake\PermessageDeflateOptions;
  use Ratchet\RFC6455\Messaging\MessageBuffer;
  use Ratchet\RFC6455\Messaging\MessageInterface;
  use Ratchet\RFC6455\Messaging\FrameInterface;
  use Ratchet\RFC6455\Messaging\Frame;
  use React\Socket\ConnectionInterface;

  ArubaIotTool::log('info', "----- Starting ArubaIot Websocket Server Daemon (".date("Y-m-d H:i:s").")'");

  /**---------------------------------------------------------------------------
   * Class : ArubaIotTool
   * Description :
   * A placeholder to group tool functions.
   * ---------------------------------------------------------------------------
   */
  class ArubaIotTool {

    /**---------------------------------------------------------------------------
     * Method : log()
     * Description :
     *   A placeholder to encapsulate log message, and be able do some
     *   troubleshooting locally.
     * ---------------------------------------------------------------------------
     */
    public function log($p_level, $p_message) {
      ArubaIotLog::log($p_level, 'websocket: '.$p_message);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : macToString()
     * Description :
     *   utility to format MAC@ from ArubaTelemetry protobuf format to string
     * ---------------------------------------------------------------------------
     */
    public function macToString($p_mac) {

      $v_size = $p_mac->getSize();
      if ($v_size != 6) {
        return "";
      }

      $v_data = $p_mac->getContents();
      $_val = unpack('C6parts', $v_data);

      $v_mac = sprintf("%02x:%02x:%02x:%02x:%02x:%02x",$_val['parts1'],$_val['parts2'],$_val['parts3'],$_val['parts4'],$_val['parts5'],$_val['parts6']);

      return filter_var(trim(strtoupper($v_mac)), FILTER_VALIDATE_MAC);
    }
    /* -------------------------------------------------------------------------*/

  }
  /* -------------------------------------------------------------------------*/


  /**---------------------------------------------------------------------------
   * Class : ArubaIotWebsocketReporter
   * Description :
   * ---------------------------------------------------------------------------
   */
  class ArubaIotWebsocketReporter {
    protected $mac_address;
    protected $connection_id_list;
    protected $status;
    protected $name;
    protected $remote_ip;
    protected $local_ip;
    protected $hardware_type;
    protected $software_version;
    protected $software_build;
    protected $date_created;
    protected $lastseen;

    public function __construct($p_mac) {
      $this->mac_address = filter_var(trim(strtoupper($p_mac)), FILTER_VALIDATE_MAC);
      $this->connection_id_list = array();
      $this->status = 'inactive';    // active:an active cnx, inactive: no active cnx
      $this->name = '';
      $this->remote_ip = '';
      $this->local_ip = '';
      $this->hardware_type = '';
      $this->software_version = '';
      $this->software_build = '';
      $this->date_created = date("Y-m-d H:i:s");
      $this->lastseen = 0;
    }

    public function setStatus($p_status) {
      // TBC should check valid values
      $this->status = $p_status;
    }

    public function getStatus() {
      return($this->status);
    }

    public function setName($p_name) {
      $this->name = $p_name;
    }

    public function getName() {
      return($this->name);
    }

    public function setMac($p_mac) {
      $this->mac_address = filter_var(trim(strtoupper($p_mac)), FILTER_VALIDATE_MAC);
    }

    public function getMac() {
      return($this->mac_address);
    }

    public function setLocalIp($p_ip) {
      $this->local_ip = filter_var(trim($p_ip), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    public function getLocalIp() {
      return($this->local_ip);
    }

    public function setRemoteIp($p_ip) {
      $this->remote_ip = filter_var(trim($p_ip), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    public function getRemoteIp() {
      return($this->remote_ip);
    }

    public function setHardwareType($p_hard) {
      $this->hardware_type = $p_hard;
    }

    public function getHardwareType() {
      return($this->hardware_type);
    }

    public function setSoftwareVersion($p_soft) {
      $this->software_version = $p_soft;
    }

    public function getSoftwareVersion() {
      return($this->software_version);
    }

    public function setSoftwareBuild($p_soft) {
      $this->software_build = $p_soft;
    }

    public function getSoftwareBuild() {
      return($this->software_build);
    }

    public function setLastSeen($p_time) {
      $this->lastseen = $p_time;
    }

    public function getLastSeen() {
      return($this->lastseen);
    }

    /**---------------------------------------------------------------------------
     * Method : hasTelemetryCnx()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function hasTelemetryCnx() {
      foreach ($this->connection_id_list as $v_connection) {
        if ($v_connection['type'] == 'telemetry') return(1);
      }
      return(0);
    }

    /**---------------------------------------------------------------------------
     * Method : hasRtlsCnx()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function hasRtlsCnx() {
      foreach ($this->connection_id_list as $v_connection) {
        if ($v_connection['type'] == 'rtls') return(1);
      }
      return(0);
    }

    /**---------------------------------------------------------------------------
     * Method : disconnect()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function disconnect(&$p_connection) {
      $v_id = $p_connection->my_id;
      if ($v_id == '') {
        ArubaIotTool::log('debug', "Invalid empty id for connection. Failed to attach connection to reporter.");
        return(false);
      }

      if ( isset($this->connection_id_list[$v_id]) ) {
        ArubaIotTool::log('info', "Removing connection '".$v_id."' for reporter '".$this->mac_address."'");
        unset($this->connection_id_list[$v_id]);
        $this->setRemoteIp('');
        $p_connection->my_reporter_id = '';
        return(true);
      }
      else {
        ArubaIotTool::log('debug', "Connection '".$p_connection_id."' is not attached to reporter '".$this->mac_address."'");
        return(false);
      }

    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : connect()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function connect(&$p_connection) {
      $v_id = $p_connection->my_id;
      if ($v_id == '') {
        ArubaIotTool::log('debug', "Invalid empty id for connection. Failed to attach connection to reporter.");
        return(false);
      }

      $v_type = $p_connection->my_type;
      if (($v_type != 'telemetry') && ($v_type != 'rtls')) {
        ArubaIotTool::log('debug', "Invalid connection type '".$v_type."'. Failed to attach connection to reporter.");
        return(false);
      }

      if (!isset($this->connection_id_list[$v_id])) {
        ArubaIotTool::log('info', "Adding new connection '".$v_id."' for reporter '".$this->mac_address."'");
        $this->connection_id_list[$v_id] = array();
        $this->connection_id_list[$v_id]['type'] = $v_type;
        $this->setRemoteIp($p_connection->my_remote_ip);
        $p_connection->my_reporter_id = $this->mac_address;
        return(true);
      }
      else {
        ArubaIotTool::log('debug', "Connection '".$v_id."' already attached to reporter '".$this->mac_address."'");
        return(false);
      }
    }
    /* -------------------------------------------------------------------------*/

  }
  /* -------------------------------------------------------------------------*/


  /**---------------------------------------------------------------------------
   * Class : ArubaIotWebsocket
   * Description :
   * ---------------------------------------------------------------------------
   */
  class ArubaIotWebsocket {
    // ----- Attributes from configuration
    protected $ip_address;
    protected $tcp_port;
    protected $up_time;

    // ----- Attributes to manage dynamic datas
    protected $connections_list;
    protected $reporters_list;
    protected $cached_devices;
    protected $devices_list;

    protected $device_type_allow_list;
    protected $reporters_allow_list;
    protected $include_mode;
    protected $access_token;
    protected $include_device_count;

    /**---------------------------------------------------------------------------
     * Method : init()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function __construct() {
      $this->connections_list = new \SplObjectStorage;
      $this->reporters_list = array();
      $this->cached_devices = array();
      $this->reporters_allow_list = array();
      $this->device_type_allow_list = array();
      $this->ip_address = "0.0.0.0";
      $this->tcp_port = "8081";
      $this->device_type_allow_list = '';
      $this->access_token = '';
      $this->include_mode = false;
      $this->up_time = 0;
      $this->include_device_count = 0;
    }
    /* -------------------------------------------------------------------------*/

    public function setIpAddress($p_ip_addess) {
      $p_ip_addess = filter_var(trim($p_ip_addess), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
      if ($p_ip_addess == '') {
        $p_ip_addess = "0.0.0.0";
      }
      $this->ip_address = $p_ip_addess;
    }

    public function getIpAddress() {
      return($this->ip_address);
    }

    public function setTcpPort($p_tcp_port) {
      $this->tcp_port = $p_tcp_port;
    }

    public function getTcpPort() {
      return($this->tcp_port);
    }

    public function getInterruptTimeout() {
      $v_timeout = 10;

      /*
      // Seems that an interrupt every 10 sec should be ok to validate the absence of devices ...
      $v_timeout = config::byKey('presence_timeout', 'ArubaIot');
      // ----- Min interrupt is 10 seconds
      if ($v_timeout < 10)
        $v_timeout = 10;

        */
      return($v_timeout);
    }

    public function getReporterByMac($p_mac) {
      if (isset($this->reporters_list[$p_mac])) {
          return($this->reporters_list[$p_mac]);
      }
    }

    public function getCachedDeviceByMac($p_mac) {
      if (isset($this->cached_devices[$p_mac])) {
          return($this->cached_devices[$p_mac]);
      }
      return(null);
    }

    public function getReporterByConnectionId($p_id) {
      foreach ($this->reporters_list as $v_reporter) {
        if ($v_reporter->connection_id == $p_id) {
          return($v_reporter);
        }
      }
      return(null);
    }

    public function getConnectionById($p_id) {
      foreach ($this->connections_list as $v_connection) {
        if ($v_connection->my_reporter_id == $p_id) {
          return($v_connection);
        }
      }
      return(null);
    }

    /**---------------------------------------------------------------------------
     * Method : init()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function init() {

      // ----- Store start time
      $this->up_time = time();

      // ----- Initialize ip/port/etc ... from plugin configuration
      $this->setIpAddress(config::byKey('ws_ip_address', 'ArubaIot'));
      $this->setTcpPort(intval(config::byKey('ws_port', 'ArubaIot')));

      $v_val = config::byKey('access_token', 'ArubaIot');
      ArubaIotTool::log('info', "Learning reporters access token : '".$v_val."'");
      $this->access_token = trim($v_val);

      $v_val = trim(config::byKey('reporters_allow_list', 'ArubaIot'));
      ArubaIotTool::log('info', "Learning reporters allowed list : '".$v_val."'");
      $v_list = explode(',', $v_val);
      $this->reporters_allow_list = array();
      foreach ($v_list as $v_item) {
        $v_mac = filter_var(trim(strtoupper($v_item)), FILTER_VALIDATE_MAC);
        if ($v_mac !== false) {
          ArubaIotTool::log('debug', "Allowed reporter : '".$v_mac."'");
          $this->reporters_allow_list[] = $v_mac;
        }
      }

      // ----- Get all equipment from plugin 'ArubaIot'
      $this->updateDeviceList();

      return;
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateDeviceList()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateDeviceList() {

      // ----- Get all equipment of from plugin 'ArubaIot'
      ArubaIotTool::log('info', "Learning allowed active devices");
      //$v_eq_list = eqLogic::all();              // pour tous les équipements
      $v_eq_list = eqLogic::byType('ArubaIot');

      // ----- Reset the list
      $this->cached_devices = array();

      // ----- Adding all created device in the allowed list
      foreach($v_eq_list as $v_eq_device)
      {
        ArubaIotTool::log('info', "Look at device : ".$v_eq_device->getHumanName());

        $v_mac = strtoupper($v_eq_device->getConfiguration('mac_address', ''));
        if ($v_mac == '') {
          // TBC : should not allow device with no mac ....
          ArubaIotTool::log('info', "  Device has missing MAC address, ignored");
        }
        else {
          $v_device = new ArubaIotDevice($v_mac);
          $v_device->setJeedomObjectId($v_eq_device->getId());
          $this->cached_devices[$v_mac] = $v_device;

          ArubaIotTool::log('info', "  Device of type '".$v_eq_device->getConfiguration('class_type', '')."' with MAC Address :".$v_mac." is ".($v_eq_device->getIsEnable()? 'enabled' : 'disabled')."");
        }
      }

      return;
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : onApiCall()
     * Description :
     * Waiting  for jason data :
     * {
     *   "api_version":"1.0",
     *   "api_key":"xxx",
     *   "event":{
     *     "name":"evant_name",
     *     "data": {
     *       "data_1":"value_1",
     *       "data_2":"value_2"
     *     }
     *   }
     * }
     * For future, multiple events would be :
     * {
     *   "api_version":"1.0",
     *   "api_key":"xxx",
     *   "events":[
     *     {
     *       "name":"event_name",
     *       "data": {
     *         "data_1":"value_1",
     *         "data_2":"value_2"
     *       }
     *     },
     *     {
     *       "name":"event_name_2",
     *       "data": {
     *         "data_1":"value_1",
     *         "data_2":"value_2"
     *       }
     *     }
     *   ]
     * }
     * ---------------------------------------------------------------------------
     */
    public function onApiCall(ConnectionInterface &$p_connection, $p_msg) {

      ArubaIotTool::log('debug', "New API Connection from ".$p_connection->my_id."");

      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      if (($v_data = json_decode($p_msg, true)) === null) {
        $v_response['response'] = "Missing or bad json data in API call";
        ArubaIotTool::log('debug', $v_response['response']);
        return(json_encode($v_response));
      }

      ArubaIotTool::log('trace', $v_data);

      // ----- Look for API version (for future use)
      // TBC

      // ----- Look for valid API Key
      if (!isset($v_data['api_key']) || ($v_data['api_key'] != jeedom::getApiKey('ArubaIot'))) {
        $v_response['response'] = "Bad API key";
        ArubaIotTool::log('debug', $v_response['response']);
        return(json_encode($v_response));
      }

      ArubaIotTool::log('debug', "Valid API key received");

      // ----- Look for missing event
      if (  !isset($v_data['event']) || !is_array($v_data['event'])
          || !isset($v_data['event']['name']) ) {
        $v_response['response'] = "Missing event info";
        ArubaIotTool::log('debug', $v_response['response']);
        return(json_encode($v_response));
      }

      ArubaIotTool::log('debug', "Receive event '".$v_data['event']['name']."'");

      // ----- Call method associated to event
      // By doing this generic call, adding a new event, just need to add the method formatted  apiEvent_<name_of_the_event>()
      $v_method = 'apiEvent_'.$v_data['event']['name'];
      if (method_exists($this, $v_method)) {
          $v_response = $this->$v_method((isset($v_data['event']['data'])?$v_data['event']['data']:array()));
          return($v_response);
      }
      else {
          // ----- Do nothing !
          $v_response['response'] = 'Unknown event';
          return(json_encode($v_response));
      }

    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_exeeemple()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_exeeemple($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      if (!isset($p_data['state'])) {
        $v_response['response'] = "Missing event data";
        ArubaIotTool::log('debug', $v_response['response']);
        return(json_encode($v_response));
      }

      $v_response['state'] = 'ok';
      $v_response['response'] = 'TBD';

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_include_device_count()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_include_device_count($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = array();

      $v_response['state'] = 'ok';
      $v_response['response']['count'] = $this->include_device_count;

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_device_remove()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_device_remove($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      if (!isset($p_data['mac_address'])) {
        $v_response['response'] = "Missing event data";
        ArubaIotTool::log('debug', $v_response['response']);
        return(json_encode($v_response));
      }

      ArubaIotTool::log('debug', "Remove device ".$p_data['mac_address']."");

      if (isset($this->cached_devices[$p_data['mac_address']])) {
        unset($this->cached_devices[$p_data['mac_address']]);
        ArubaIotTool::log('info', "Device '".$p_data['mac_address']."' was removed from cache.");
      }
      else {
        ArubaIotTool::log('debug', "Device '".$p_data['mac_address']."' is not found in the cache.");
      }

      $v_response['state'] = 'ok';
      $v_response['response'] = '';

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_device_refresh()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_device_refresh($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      if (!isset($p_data['mac_address']) || !isset($p_data['id'])
          || ($p_data['mac_address'] == '') || ($p_data['id'] == '')) {
        $v_response['response'] = "Missing event data";
        ArubaIotTool::log('debug', $v_response['response']);
        return($v_response);
      }

      ArubaIotTool::log('debug', "Refresh device '".$p_data['mac_address']."'");

      if ($p_data['mac_address'] == '00:00:00:00:00:00') {
        ArubaIotTool::log('debug', "Not yet a valid MAC@, waiting ...");

        $v_response['state'] = 'ok';
        $v_response['response'] = '';
        return($v_response);
      }

      if (isset($this->cached_devices[$p_data['mac_address']])) {
        ArubaIotTool::log('info', "Device '".$p_data['mac_address']."' is in the cache. Update.");
      }
      else {
        ArubaIotTool::log('info', "Device '".$p_data['mac_address']."' is not found in cache, create a new one.");
        $v_jeedom_object = eqLogic::byId($p_data['id']);
        if ($v_jeedom_object == null) {
          ArubaIotTool::log('debug', "Failed to find jeedom eq with this id : ".$p_data['id'].".");
        }
        else {
          $v_device = new ArubaIotDevice($p_data['mac_address']);
          $v_device->setJeedomObjectId($v_jeedom_object->getId());
          $this->cached_devices[$p_data['mac_address']] = $v_device;

          ArubaIotTool::log('info', "  Device of type '".$v_jeedom_object->getConfiguration('class_type', '')."' with MAC Address :".$p_data['mac_address']." is ".($v_jeedom_object->getIsEnable()? 'enabled' : 'disabled')."");
        }

      }

      $v_response['state'] = 'ok';
      $v_response['response'] = '';

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_reporter_list()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_reporter_list($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      ArubaIotTool::log('debug', "Send reporter list");

      $v_response_array = array();
      $v_response_array['websocket'] = array();
      $v_response_array['websocket']['ip_address'] = $this->ip_address;
      $v_response_array['websocket']['tcp_port'] = $this->tcp_port;
      $v_response_array['websocket']['up_time'] = $this->up_time;

      $v_response_array['reporters'] = array();
      $i = 0;
      foreach ($this->reporters_list as $v_reporter) {
        $v_item = array();
        $v_item['mac'] = $v_reporter->getMac();
        $v_item['name'] = $v_reporter->getName();
        $v_item['local_ip'] = $v_reporter->getLocalIp();
        $v_item['remote_ip'] = $v_reporter->getRemoteIp();
        $v_item['model'] = $v_reporter->getHardwareType();
        $v_item['version'] = $v_reporter->getSoftwareVersion();
        $v_item['telemetry'] = $v_reporter->hasTelemetryCnx();
        $v_item['rtls'] = $v_reporter->hasRtlsCnx();
        $v_item['lastseen'] = $v_reporter->getLastSeen();
        $v_response_array['reporters'][] = $v_item;
      }

      $v_response['state'] = 'ok';
      $v_response['response'] = $v_response_array;

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_debug_reporters()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_debug_reporters($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      if (!isset($p_data['state'])) {
        $v_response['response'] = "Missing event data";
        ArubaIotTool::log('debug', $v_response);
        return(json_encode($v_response));
      }

      foreach ($this->reporters_list as $v_reporter) {
        echo var_dump($v_reporter);
      }

      $v_response['state'] = 'ok';
      $v_response['response'] = 'Unknown error';

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : apiEvent_include_mode()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function apiEvent_include_mode($p_data) {
      $v_response = array();
      $v_response['state'] = 'error';
      $v_response['response'] = 'Unknown error';

      if (!isset($p_data['state'])) {
        $v_response['response'] = "Missing include_mode event data";
        ArubaIotTool::log('debug', $v_response);
        return(json_encode($v_response));
      }

      $this->include_mode = ($p_data['state'] == 1?true:false);

      // ----- Reset allow list
      $this->device_type_allow_list = array();

      ArubaIotTool::log('info', "Changing include mode to ".($this->include_mode ? 'true' : 'false'));
      if ($this->include_mode) {
        if (isset($p_data['type'])) {
          ArubaIotTool::log('debug', "Classes to include : ".$p_data['type']);
          $this->device_type_allow_list = explode(',', $p_data['type']);
        }
        else {
          ArubaIotTool::log('debug', "Missing classes to include ! ");
        }
      }

      // ----- Reset new device count
      $this->include_device_count = 0;

      $v_response['state'] = 'ok';
      $v_response['response'] = '';

      return(json_encode($v_response));
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : onOpen()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function onOpen(ConnectionInterface &$p_connection, $p_type='telemetry') {

      // ----- Get connection IP and TCP values to create an Id
      // Trick : I'm adding a internal ID for the connection, to optimize search later
      // I'm also adding an internal status
      // And a link to the reporter
      $v_address = $p_connection->getRemoteAddress();
      $v_ip = trim(parse_url($v_address, PHP_URL_HOST), '[]');
      $v_port = trim(parse_url($v_address, PHP_URL_PORT), '[]');
      $v_id = $v_ip.':'.$v_port;

      // ------ Look for an existing connection with this ID
      // TBC : should check that an already active connection is not here, or reactivate an old connection ??

      // ----- Add my own attributes to the connection object ....
      $p_connection->my_id = $v_id;
      $p_connection->my_type = $p_type;
      $p_connection->my_remote_ip = $v_ip;
      $p_connection->my_status = 'initiated';      // initiated=valid conx, active=reporter identifed
      $p_connection->my_reporter_id = '';

      // ----- Attach connection in the list
      $this->connections_list->attach($p_connection);

      ArubaIotTool::log('info', "New Connection from ".$p_connection->my_id."");

    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : onClose()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function onClose(ConnectionInterface &$connection) {

      ArubaIotTool::log('info', "Closing Connection '".$connection->my_id."' (".date("Y-m-d H:i:s").")");

      // ----- Remove cross-links between connection and reporter
      // ----- Get reporter
      $v_reporter = $this->getReporterByMac($connection->my_reporter_id);
      if ($v_reporter !== null) {
        $v_reporter->disconnect($connection);
      }

      // ----- Remove from connection list
      $this->connections_list->detach($connection);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : onMsgTelemetry()
     * Description :
     * ---------------------------------------------------------------------------
     */
    //public function onMsgTelemetry(ConnectionInterface &$p_connection, $v_at_telemetry_msg) {
    public function onMsgTelemetry(&$p_reporter, $v_at_telemetry_msg) {

      ArubaIotTool::log('debug',  "Received telemetry message from ".$p_reporter->getName()."");

      // ----- Look if there is a list of reported device
      if ($v_at_telemetry_msg->hasReportedList()) {
        $v_col = $v_at_telemetry_msg->getReportedList();

        ArubaIotTool::log('debug', "+-----------------------------------------------------------------+");
        ArubaIotTool::log('debug', "| MAC@ Address      | Class List          | Model      | RSSI     |");

        // ----- Look at each reported device
        foreach ($v_col as $v_object) {

          // ----- Extract the class of the object
          // The object can have several class, so concatene in a single string
          $v_class_list = array();
          if ($v_object->hasDeviceClassList()) {
            foreach ($v_object->getDeviceClassList() as $v_class) {
              $v_class_list[] = $v_class->name();
            }
          }
          sort($v_class_list);
          $v_class_name = trim(implode(' ', $v_class_list));
          // ----- Remove double names
          if ($v_class_name == 'arubaBeacon iBeacon')
            $v_class_name = 'arubaBeacon';
          // ----- Change name to 'generic'
          if ($v_class_name == 'unclassified')
            $v_class_name = 'generic';

          // ----- Debug display
          ArubaIotTool::log('debug', "+-------------------------------------------------------------------------------+");
          $v_msglog = "|";
          $v_msglog .= sprintf(" %17s ", ($v_object->hasMac() ? ArubaIotTool::macToString($v_object->getMac()) : ' '));
          $v_msglog .= "|";
          $v_msglog .= sprintf("%20s ", $v_class_name);
          $v_msglog .= "|";
          $v_msglog .= sprintf(" %10s ", ($v_object->hasModel() ? $v_object->getModel() : ' '));
          $v_msglog .= "|";
          $v_msglog .= sprintf(" %8s ", ($v_object->hasRSSI() ? trim($v_object->getRSSI()) : ' '));

          // ----- Get device mac @
          $v_device_mac = ($v_object->hasMac() ? ArubaIotTool::macToString($v_object->getMac()) : '');

          if ($v_device_mac == '') {
            ArubaIotTool::log('debug', $v_msglog."|");
            ArubaIotTool::log('debug',"Received a device with malformed MAC@, skip telemetry data.");
            continue;
          }

          // ----- Look for an allowed device in the cache with this MAC@
          $v_device = $this->getCachedDeviceByMac($v_device_mac);

          // ----- Create new device if allowed class and inclusion mode on
          if (($v_device == null) && $this->include_mode && in_array($v_class_name, $this->device_type_allow_list)) {
            ArubaIotTool::log('info', "Inclusion of a new device.");
            ArubaIotTool::log('debug', "Create a new device.");
            $v_msglog .= "|         new ";

    		$v_jeedom_device = new ArubaIot();
            ArubaIotTool::log('debug', "Set name.");
    		$v_jeedom_device->setName($v_class_name." ".$v_device_mac);
    		//$eqLogic->setLogicalId('reporter');
            ArubaIotTool::log('debug', "Set class type.");
    		$v_jeedom_device->setEqType_name('ArubaIot');

            ArubaIotTool::log('debug', "Set properties.");
            $v_jeedom_device->setConfiguration('mac_address', $v_device_mac);
            $v_jeedom_device->setConfiguration('class_type', 'auto');      // will be updated in Telemetry update

            ArubaIotTool::log('debug', "Set enable.");
            $v_jeedom_device->setIsEnable(1);

            ArubaIotTool::log('debug', "save.");
            // Here is a trick I need to control the jeedom not to send back api request on refresh
            $v_jeedom_device->setConfiguration('trick_save_from_daemon', 'on');
    		$v_jeedom_device->save();
            $v_jeedom_device->setConfiguration('trick_save_from_daemon', 'off');

            // ----- Create the local device cache image
            $v_device = new ArubaIotDevice($v_device_mac);
            $v_device->setJeedomObjectId($v_jeedom_device->getId());
            $this->cached_devices[$v_device_mac] = $v_device;
            ArubaIotTool::log('debug', "add in list.");

            $this->include_device_count++;

          }

          // ----- Look for existing device and enabled
          if ($v_device != null) {
            $v_device->resetChangedFlag();

            // ----- Get corresponding Jeedom object
            $v_jeedom_object = eqLogic::byId($v_device->getJeedomObjectId());
            if ($v_jeedom_object === null) {
              // Should not occur, but if in removing an object phase ...
              ArubaIotTool::log('debug', "Fail to find a jeedom object with this ID '".$v_device->getJeedomObjectId()."' for mac '".$v_device->getMac()."'");
              continue;
            }

            // ----- Update object class if jeedom object is in 'auto'
            $v_device->updateObjectClass($v_jeedom_object, $v_object, $v_class_name);

            // ----- Look for enabled device  : no telemetry data to update
            if (!$v_jeedom_object->getIsEnable()) {
              ArubaIotTool::log('debug', "Device '".$v_device->getMac()."' is disabled. Skip telemetry data.");
              continue;
            }

            // ----- If same nearest AP or better one, update telemetry data.
            if ($v_device->updateNearestAP($p_reporter, $v_jeedom_object, $v_object)) {
              $v_device->updateTelemetryData($p_reporter, $v_jeedom_object, $v_object, $v_class_name);
            }

            // ----- If object supporting triangulation, update triangulation
            // TBC : if triangulation updated, widget is not refreshed ...
            $v_device->updateTriangulation($p_reporter, $v_jeedom_object, $v_object);

            // ----- Update jeedom wizard display if needed
            $v_device->checkChangedFlag($v_jeedom_object);

            $v_msglog .= "|      active ";

          }

          else {
            $v_msglog .= "| ignored     ";
          }

          if ($v_object->hasLastSeen()) {
            $v_val = $v_object->getLastSeen();
            $v_msglog .= "| ".date("Y-m-d H:i:s", $v_val);
          }
          ArubaIotTool::log('debug', $v_msglog."|");

        } // end of foreach

        ArubaIotTool::log('debug', "+-------------------------------------------------------------------------------+");
      }
      else {
        ArubaIotTool::log('debug', "Message with no reported devices.");
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : onMsgWiFiRtls()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function onMsgWiFiRtls(ConnectionInterface &$p_connection, $v_at_telemetry_msg) {

      ArubaIotTool::log('debug',  "Received RTLS message from ".$p_connection->my_id."");

      // ----- Look if there is a list of reported device
      if ($v_at_telemetry_msg->hasWifiDataList()) {
        $v_list = $v_at_telemetry_msg->getWifiDataList();

        ArubaIotTool::log('debug', "+-----------------------------------------------------------------+");
        ArubaIotTool::log('debug', "| MAC@ Address      | RSSI     |");

        // ----- Look at each reported device
        foreach ($v_list as $v_object) {

          // ----- Extract the class of the object
          // The object can have several class, so concatene in a single string
          $v_class_list = array();
          if ($v_object->hasDeviceClassList()) {
            foreach ($v_object->getDeviceClassList() as $v_class) {
              $v_class_list[] = $v_class->name();
            }
          }
          sort($v_class_list);
          $v_class_name = trim(implode(' ', $v_class_list));
          // ----- Remove double names
          if ($v_class_name == 'arubaBeacon iBeacon')
            $v_class_name = 'arubaBeacon';
          // ----- Change name to 'generic'
          if ($v_class_name == 'unclassified')
            $v_class_name = 'generic';

          // ----- Get device mac @
          $v_device_mac = ($v_object->hasMac() ? ArubaIotTool::macToString($v_object->getMac()) : '');

          // ----- Debug display
          ArubaIotTool::log('debug', "+-------------------------------------------------------------------------------+");
          $v_msglog = "|";
          $v_msglog .= sprintf(" %17s ", $v_device_mac);
          $v_msglog .= "|";
          $v_msglog .= sprintf("%20s ", $v_class_name);
          $v_msglog .= "|";
          $v_msglog .= sprintf(" %8s ", ($v_object->hasRSSI() ? trim($v_object->getRSSI()) : ' '));

          if ($v_device_mac == '') {
            ArubaIotTool::log('debug', $v_msglog."|");
            ArubaIotTool::log('debug',"Received a device with malformed MAC@, skip telemetry data.");
            continue;
          }


          // TBC : to be continued !


          ArubaIotTool::log('debug', $v_msglog."|");

        }


        ArubaIotTool::log('debug', "+-------------------------------------------------------------------------------+");
      }

      return(true);
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : onMessage()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function onMessage(ConnectionInterface &$p_connection, $p_msg) {

      ArubaIotTool::log('debug',  "Received message from ".$p_connection->my_id."");

      // ----- Parse Aruba protobuf message
      // TBC : I should check that the telemetry object is ok
      $v_at_telemetry_msg = new aruba_telemetry\Telemetry($p_msg);

      ArubaIotTool::log('trace', $v_at_telemetry_msg);

      // ----- Get Meta part of the message
      $v_at_meta = $v_at_telemetry_msg->getMeta();

      // ----- Get Topic
      $v_topic = '';
      if ($v_at_meta->hasNbTopic()) {
        $v_topic = $v_at_meta->getNbTopic()->name();
      }

      ArubaIotTool::log('debug', "--------- Meta :");
      ArubaIotTool::log('debug', "  Version: ".$v_at_meta->getVersion()."");
      ArubaIotTool::log('debug', "  Access Token: ".$v_at_meta->getAccessToken()."");
      ArubaIotTool::log('debug', "  NbTopic: ".$v_topic."");
      ArubaIotTool::log('debug', "---------");
      ArubaIotTool::log('debug', "");

      // ----- Check Topic
      if ($v_topic == ''){
        ArubaIotTool::log('debug', "Missing nbTopic information, not a valid protobuf message ... ?");
        // TBC : should I stop here ?
      }
      else if (($p_connection->my_type == 'telemetry') && ($v_topic != 'telemetry')) {
        ArubaIotTool::log('debug', "Received none telemetry frame (nbTopic:'".$v_topic."') on Telemetry URI.");
        // TBC : should I stop here ?
      }
      else if (($p_connection->my_type == 'rtls') && ($v_topic != 'wifiData')) {
        ArubaIotTool::log('debug', "Received none wifiData frame (nbTopic:'".$v_topic."') on RTLS URI.");
        // TBC : should I stop here ?
      }

      // ----- Get report infos
      $v_at_reporter = $v_at_telemetry_msg->getReporter();

      // ----- Get Reporter mac@
      $v_mac = ArubaIotTool::macToString($v_at_reporter->getMac());
      $v_ipv4 = ($v_at_reporter->hasIpv4() ? $v_at_reporter->getIpv4() : '');

      ArubaIotTool::log('debug', "--------- Reporter :");
      ArubaIotTool::log('debug', "  Name: ".$v_at_reporter->getName()."");
      ArubaIotTool::log('debug', "  Mac: ".ArubaIotTool::macToString($v_at_reporter->getMac())."");
      ArubaIotTool::log('debug', "  IPv4: ".$v_ipv4."");
      ArubaIotTool::log('debug', "  IPv6: ".($v_at_reporter->hasIpv6() ? $v_at_reporter->getIpv6() : '-')."");
      ArubaIotTool::log('debug', "  hwType: ".$v_at_reporter->getHwType()."");
      ArubaIotTool::log('debug', "  swVersion: ".$v_at_reporter->getSwVersion()."");
      ArubaIotTool::log('debug', "  swBuild: ".$v_at_reporter->getSwBuild()."");
      ArubaIotTool::log('debug', "  Time: ".date("Y-m-d H:i:s", $v_at_reporter->getTime())." (".$v_at_reporter->getTime().")");
      ArubaIotTool::log('debug', "---------");
      ArubaIotTool::log('debug', "");

      // ----- Check Access token
      if ( ($this->access_token != '') && ($this->access_token != $v_at_meta->getAccessToken()) ) {
        ArubaIotTool::log('info', "Received message from reporter (".$v_mac.",".$v_ipv4.") with invalid access token. Closing connection.");
        return(false);
      }

      // ----- Look for controlled list of reporters by MAC@
      if (sizeof($this->reporters_allow_list) > 0) {
        if (!in_array($v_mac, $this->reporters_allow_list)) {
          ArubaIotTool::log('info', "Received message from not allowed reporter (".$v_mac.",".$v_ipv4."). Closing connection.");
          return(false);
        }
      }


/*
$v_filemane = __DIR__."/telemetry-".date("Y-m-d-H-i-s", $v_at_reporter->getTime())."-".$v_mac.".json";
$v_filemane = str_replace(':', '-', $v_filemane);
echo "filename : $v_filemane\n";
$fd = fopen($v_filemane, 'w');
fwrite($fd, "Reporter (".$v_mac.") :\n");
fwrite($fd, $v_at_reporter);
fwrite($fd, "\n");
fwrite($fd, "\n");
*/
      // ----- Look for existing reporter in the list
      $v_reporter = $this->getReporterByMac($v_mac);

      // ----- Look for new connection with no reporter (normally first message following connection)
      if ( ($p_connection->my_status == 'initiated') && ($v_reporter === null) ) {

        ArubaIotTool::log('info', "Creating new reporter with MAC@ : ".$v_mac."");

        // ----- Create a new reporter
        $v_reporter = new ArubaIotWebsocketReporter($v_mac);

        // ----- Set additional attributes
        $v_reporter->setName($v_at_reporter->getName());
        $v_reporter->setLocalIp(($v_at_reporter->hasIpv4() ? $v_at_reporter->getIpv4() : ''));
        //$v_reporter->setLocalIpv6(($v_at_reporter->hasIpv6() ? $v_at_reporter->getIpv6() : ''));
        $v_reporter->setHardwareType($v_at_reporter->getHwType());
        $v_reporter->setSoftwareVersion($v_at_reporter->getSwVersion());
        $v_reporter->setSoftwareBuild($v_at_reporter->getSwBuild());
        $v_reporter->setLastSeen($v_at_reporter->getTime());
        //$v_reporter->setLastUpdateTime(date("Y-m-d H:i:s", $v_at_reporter->getTime()));

        // ----- Attach to list
        $this->reporters_list[$v_mac] = $v_reporter;

        // ----- Connect the reporter with the connection
        $v_reporter->connect($p_connection);

        ArubaIotTool::log('info', "Attaching Reporter '".$v_mac."' (".$v_reporter->getName().") to connection '".$p_connection->my_id."'.");

        // ----- Update connection custom attributes
        $p_connection->my_status = 'active';


      }
      // ----- Look for new connection with existing reporter
      else if ( ($p_connection->my_status == 'initiated') && ($v_reporter !== null) ) {

        // ----- Connect the reporter with the connection
        $v_reporter->connect($p_connection);

        ArubaIotTool::log('info', "Attaching Reporter '".$v_mac."' (".$v_reporter->setName.") to connection '".$p_connection->my_id."'.");

        // ----- Update connection custom attributes
        $p_connection->my_status = 'active';
      }

      // ----- Look for already established connection with valid reporter
      else if ( ($p_connection->my_status == 'active') && ($v_reporter !== null) ) {
        // ----- Look for reporter sync
        if ($v_reporter->getMac() != $p_connection->my_reporter_id) {
          ArubaIotTool::log('debug', "An active connection with reporter '".$p_connection->my_reporter_id."' receiving data from an other reporter '".$v_reporter->getMac()."' ... should not occur. Ignore data.");
          return(true);
        }
      }

      else /* if ( ($p_connection->my_status == 'active') && ($v_reporter === null) ) */ {
        // Should not occur .... !
      }

      // ----- Update changed attributes of the reporter
      if ($v_reporter->getName() != $v_at_reporter->getName()) {
        ArubaIotTool::log('info', "Reporter '".$v_reporter->getMac()."' changed name '".$v_reporter->getName()."' for '".$v_at_reporter->getName()."'");
        $v_reporter->setName($v_at_reporter->getName());
      }
      $v_ip = ($v_at_reporter->hasIpv4() ? $v_at_reporter->getIpv4() : '');
      if ($v_reporter->getLocalIp() != $v_ip) {
        ArubaIotTool::log('info', "Reporter '".$v_reporter->getMac()."' changed local IP '".$v_reporter->getLocalIp()."' for '".$v_ip."'");
        $v_reporter->setLocalIp($v_ip);
      }
      $v_hard = $v_at_reporter->getHwType();
      if ($v_reporter->getHardwareType() != $v_hard) {
        ArubaIotTool::log('info', "Reporter '".$v_reporter->getMac()."' changed hardware type '".$v_reporter->getHardwareType()."' for '".$v_hard."'");
        $v_reporter->setHardwareType($v_hard);
      }
      $v_soft = $v_at_reporter->getSwVersion();
      if ($v_reporter->getSoftwareVersion() != $v_soft) {
        ArubaIotTool::log('info', "Reporter '".$v_reporter->getMac()."' changed software version '".$v_reporter->getSoftwareVersion()."' for '".$v_soft."'");
        $v_reporter->setSoftwareVersion($v_soft);
      }
      $v_soft = $v_at_reporter->getSwBuild();
      if ($v_reporter->getSoftwareBuild() != $v_soft) {
        ArubaIotTool::log('info', "Reporter '".$v_reporter->getMac()."' changed software build '".$v_reporter->getSoftwareBuild()."' for '".$v_soft."'");
        $v_reporter->setSoftwareBuild($v_soft);
      }

      // ----- Update last seen value
      $v_reporter->setLastSeen($v_at_reporter->getTime());

      // ----- Parse data depending on nature
      if ($p_connection->my_type == 'telemetry') {
        return $this->onMsgTelemetry($v_reporter, $v_at_telemetry_msg);
      }
      else if ($p_connection->my_type == 'rtls') {
        return $this->onMsgWiFiRtls($p_connection, $v_at_telemetry_msg);
      }
      else {
      }

      return(false);
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : onPingMessage()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function onPingMessage(ConnectionInterface &$p_connection) {
      if (0) {
        ArubaIotTool::log('debug', "Received ping from ".$connection->my_name." (".date("Y-m-d H:i:s").")");
      }
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : onInterrupt()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function onInterrupt() {

      ArubaIotTool::log('debug', 'New interupt call');

      // ----- Add a gracefull restart
      // Need to wait for websocket connexions to be up before change status
      // to missing
      // 60 sec is maybe too much ?
      if (($this->up_time + 60) > time()) {
        return;
      }

      // ----- Scan all devices for presence update
      foreach ($this->cached_devices as $v_device) {
        $v_device->updateAbsence();
      }

    }
    /* -------------------------------------------------------------------------*/
  }
  /* -------------------------------------------------------------------------*/



  /**---------------------------------------------------------------------------
   * Class : ArubaIotDevice
   * Description :
   *   This object is used to cache essentials informations regarding
   *   Jeedom eqLogic object, and avoid to reload all the data at each
   *   telemetry message.
   * ---------------------------------------------------------------------------
   */
  class ArubaIotDevice {
    protected $mac_address;
    protected $jeedom_object_id;
    protected $date_created;

    // ----- Used to update jeedom widget only one time after all the commands updates
    protected $widget_change_flag;

    // ----- Storing data regarding the nearest AP for the device
    protected $nearest_ap_mac;
    protected $nearest_ap_rssi;
    protected $nearest_ap_last_seen;
    protected $presence_last_seen;

    /**---------------------------------------------------------------------------
     * Method : __construct()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function __construct($p_mac) {
      $this->mac_address = filter_var(trim(strtoupper($p_mac)), FILTER_VALIDATE_MAC);
      $this->jeedom_object_id = '';
      $this->date_created = date("Y-m-d H:i:s");

      $this->nearest_ap_mac = '';
      $this->nearest_ap_rssi = -110;
      $this->nearest_ap_last_seen = 0;
      $this->presence_last_seen = 0;

      $this->widget_change_flag = false;
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : setJeedomObjectId()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function setJeedomObjectId($p_id) {
      $this->jeedom_object_id = $p_id;
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : getJeedomObjectId()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function getJeedomObjectId() {
      return($this->jeedom_object_id);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : getMac()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function getMac() {
      return($this->mac_address);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : resetChangedFlag()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function resetChangedFlag() {
      $this->widget_change_flag = false;
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : checkChangedFlag()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function checkChangedFlag($p_jeedom_object) {
      if ($this->widget_change_flag) {
        $p_jeedom_object->refreshWidget();
        $this->resetChangedFlag();
      }
    }
    /* -------------------------------------------------------------------------*/


    /**---------------------------------------------------------------------------
     * Method : updateNearestAP()
     * Description :
     * Return Value :
     *   True : if telemetry values are to be updated
     *   False : if telemetry values are to be ignored
     * ---------------------------------------------------------------------------
     */
    public function updateNearestAP(&$p_reporter, &$p_jeedom_object, $p_telemetry) {

      // ----- Get reporter mac@
      $p_reporter_mac = $p_reporter->getMac();
      ArubaIotTool::log('debug', "Look to update nearestAP with ".$p_reporter_mac);

      // ----- Get last seen (if any)
      $v_lastseen = 0;
      if ($p_telemetry->hasLastSeen()) {
        $v_lastseen = $p_telemetry->getLastSeen();
        ArubaIotTool::log('debug', "LastSeen is : ".$v_lastseen." (".date("Y-m-d H:i:s", $v_lastseen).")");
      }
      else {
        // Should not occur ... I not yet seen an object without this value ...
        $v_lastseen = time();
        ArubaIotTool::log('debug', "LastSeen is missing, use current time : ".$v_lastseen." (".date("Y-m-d H:i:s", $v_lastseen).")");
      }

      // ----- Get RSSI (if any)
      $v_rssi = -110;
      if ($p_telemetry->hasRSSI()) {
        $v_val = explode(':', $p_telemetry->getRSSI());
        $v_rssi = (isset($v_val[1]) ? intval($v_val[1]) : $v_rssi);
      }

      // ----- Look if this is the current best reporter (nearest ap)
      if ($this->nearest_ap_mac == $p_reporter->getMac()) {
        ArubaIotTool::log('debug', "Reporter '".$p_reporter->getMac()."' is the current nearest reporter. Update last seen value");

        // ----- No change in last seen value => repeated old value ...
        // No : in fact we can receive 2 payloads with the same timestamp (in sec) with different values
        // exemple is the switch up-idle-bottom values
        /*
        if ($this->nearest_ap_last_seen == $v_lastseen) {
          ArubaIotTool::log('debug', "New last seen value is the same : repeated old telemetry data. Skip telemetry data.");
          return(false);
        }
        */

        // ----- Should never occur ...
        if ($this->nearest_ap_last_seen > $v_lastseen) {
          ArubaIotTool::log('error', "New last seen value is older than previous one ! Should never occur. Skip telemetry data.");
          return(false);
        }

        // ----- Update latest update
        $this->nearest_ap_last_seen = $v_lastseen;

        // ----- Update presence, but check that no go back in time
        if ($this->presence_last_seen < $v_lastseen) {
          $this->presence_last_seen = $v_lastseen;
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('presence', 1) || $this->widget_change_flag;
        }

        // ----- Update latest RSSI.
        //  if no RSSI, keep the old one ... ?
        //  an object should always send an RSSI or never send an RSSI.
        $this->nearest_ap_rssi = $v_rssi;

        // ----- Update Presence flag to 1
        // AP is already the nearest, so if teh RSSI is very low this is an update
        // so an indication that the device is still here, not so far.
        $this->widget_change_flag = $p_jeedom_object->checkAndUpdateCmd('rssi', $v_rssi) || $this->widget_change_flag;

        return(true);
      }

      // ----- Get device presence timeout
      $v_timeout = config::byKey('presence_timeout', 'ArubaIot');

      // ----- Look for too old data for updated presence
      // Even if this is a better RSSI the timestamp is too old for the presence flag.
      if ( (($v_lastseen + $v_timeout) < time())) {
        ArubaIotTool::log('debug', "Last-seen value for this AP is already older than presence timeout. Skip telemetry data.");
        return(false);
      }

      $swap_ap_flag = false;

      // ----- Look for no current nearest AP
      if ($this->nearest_ap_mac == '') {
        ArubaIotTool::log('debug', "No existing nearest reporter.");
        $swap_ap_flag = true;
      }

      // ----- Look if new reporter has a better RSSI than current nearest
      $v_nearest_ap_hysteresis = config::byKey('nearest_ap_hysteresis', 'ArubaIot');
      if (!$swap_ap_flag && ($v_rssi != -110) && ($v_rssi > ($this->nearest_ap_rssi + $v_nearest_ap_hysteresis))) {
        ArubaIotTool::log('debug', "Swap for a new nearest AP with better RSSI, from '".$this->nearest_ap_mac."' (RSSI '".$this->nearest_ap_rssi."') to '".$p_reporter->getMac()."' (RSSI '".$v_rssi."')");
        $swap_ap_flag = true;
      }

      /*
      // ----- Look if current reporter has a very long last_seen value
      $v_nearest_ap_timeout = config::byKey('nearest_ap_timeout', 'ArubaIot');
      if (!$swap_ap_flag && (($this->nearest_ap_last_seen + $v_nearest_ap_timeout) < time())) {
        ArubaIotTool::log('debug', "Swap for a new nearest AP with better last-seen value, from '".$this->nearest_ap_mac."' (".date("Y-m-d H:i:s", $this->nearest_ap_last_seen).") to '".$p_reporter->getMac()."' (".date("Y-m-d H:i:s", $v_lastseen).").");
        $swap_ap_flag = true;
      }
      */

      // ----- Look if rssi lower than the minimum to swap
      if ($swap_ap_flag) {
        $v_nearest_ap_min_rssi = config::byKey('nearest_ap_min_rssi', 'ArubaIot');

        if ($v_rssi < $v_nearest_ap_min_rssi) {
          ArubaIotTool::log('debug', "RSSI (".$v_rssi.") is not enought to become a nearestAP.");
          $swap_ap_flag = false;
        }
      }

      // ----- Look if swap to new AP is to be done
      if ($swap_ap_flag) {
        ArubaIotTool::log('debug', "Swap for new reporter '".$p_reporter->getMac()."'");

        // ----- Swap for new nearest AP
        $this->nearest_ap_mac = $p_reporter->getMac();
        $this->nearest_ap_last_seen = $v_lastseen;
        $this->nearest_ap_rssi = $v_rssi;

        $this->widget_change_flag = $p_jeedom_object->cmdUpdateNearestAP($p_reporter->getMac(), $p_reporter->getName()) || $this->widget_change_flag;
        $this->widget_change_flag = $p_jeedom_object->checkAndUpdateCmd('rssi', $v_rssi) || $this->widget_change_flag;

        if ($this->presence_last_seen < $v_lastseen) {
          $this->presence_last_seen = $v_lastseen;
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('presence', 1) || $this->widget_change_flag;
        }

        return(true);
      }

      // ----- Compare new AP lastseen to nearestAP timestamp
      if ($v_lastseen > $this->presence_last_seen) {
        // ----- The new reporter is not a better nearest AP, but it has seen the device
        // with a better timestamp, so update the timestamp for presence update
        $this->presence_last_seen = $v_lastseen;
        ArubaIotTool::log('debug', "Do not update nearest reporter, but update presence timestamp.");
      }

      ArubaIotTool::log('debug', "Reporter '".$p_reporter->getMac()."' not a new nearest reporter compared to current '".$this->nearest_ap_mac."'. Skip telemetry data.");

      return(false);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateAbsence()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateAbsence() {

      $v_timeout = config::byKey('presence_timeout', 'ArubaIot');

      $v_absent = true;

//      ArubaIotTool::log('debug', "NearestAP (".$this->nearest_ap_mac.") last seen : '".$this->nearest_ap_last_seen."', timeout : '".$v_timeout."', current time : '".time()."'");
      ArubaIotTool::log('debug', "Check Absence for ".$this->mac_address.":Last-seen:".$this->presence_last_seen.",timeout:".$v_timeout."',current time:".time()."");

//      if (($this->nearest_ap_last_seen+$v_timeout) > time() ) {
      if (($this->presence_last_seen+$v_timeout) > time() ) {
          $v_absent = false;
      }

      // ----- Update presence cmd
      if ($v_absent) {

        // ----- Get Jeedom object
        $v_jeedom_object = eqLogic::byId($this->getJeedomObjectId());
        if ($v_jeedom_object === null) {
          // Should not occur, but if in removing an object phase ...
          ArubaIotTool::log('debug', "-> Fail to find an object with this ID ..");
          return(false);
        }

        /*
        $v_jeedom_class = $v_jeedom_object->getConfiguration('class_type');

        // ----- Look if this command is allowed for this class_name
        if (!ArubaIot::isAllowedCmdForClass('presence', $v_jeedom_class)) {
          ArubaIotTool::log('debug', "-> Command 'presence' not allowed for this class_name '".$v_jeedom_class."'. Look at settings");
          return(false);
        }
        */

        // TBC : Improvment ? May be here I should look for triangulation list
        // and get the best one in the list with a last_seen value better than the current nearest AP ?

        // ----- Reset nearestAP
        $this->nearest_ap_mac = '';
        $this->nearest_ap_rssi = -110;
        $this->nearest_ap_last_seen = 0;
        $this->presence_last_seen = 0;

        ArubaIotTool::log('debug', "--> Presence flag is : missing");

        // ----- Reset presence, nearestAP and RSSI when device is missing
        $v_widget_change_flag = false;
        $v_widget_change_flag = $v_jeedom_object->createAndUpdateCmd('presence', 0) || $v_widget_change_flag;
        $v_widget_change_flag = $v_jeedom_object->cmdUpdateNearestAP('', '') || $v_widget_change_flag;
        $v_widget_change_flag = $v_jeedom_object->checkAndUpdateCmd('rssi', -110) || $v_widget_change_flag;

        if ($v_widget_change_flag) {
          $v_jeedom_object->refreshWidget();
        }
      }
      else {
        ArubaIotTool::log('debug', "--> Presence flag is : presence");
      }

      return;
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateRssiTelemetry()
     * Description :
     * ---------------------------------------------------------------------------
     */
     // DEPRECATED : included in updateNearestAP
    public function updateRssiTelemetry_DEPRECATED(&$p_jeedom_object, $p_telemetry, $p_class_name='') {

      //ArubaIotTool::log('debug', "Update Rssi Telemetry data");

      /*
      // ----- Look if this command is allowed for this class_name
      if (!ArubaIot::isAllowedCmdForClass('rssi', $p_class_name)) {
        ArubaIotTool::log('debug', "Command 'rssi' not allowed for this class_name '".$p_class_name."'. Look at settings");
        return(false);
      }
      */

      // ----- Update common telemetry data
      $v_rssi = 0;
      if ($p_telemetry->hasRSSI()) {
        $v_val = explode(':', $p_telemetry->getRSSI());
        $v_rssi = (isset($v_val[1]) ? intval($v_val[1]) : 0);
      }
      if ($v_rssi != 0) {
        ArubaIotTool::log('debug', "--> RSSI changed for : ".$v_rssi);
        $this->widget_change_flag = $p_jeedom_object->checkAndUpdateCmd('rssi', $v_rssi) || $this->widget_change_flag;
      }

      return($this->widget_change_flag);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateTriangulation()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateTriangulation(&$p_reporter, &$p_jeedom_object, $p_telemetry) {

      // ----- Update common telemetry data
      $v_rssi = -110;
      if ($p_telemetry->hasRSSI()) {
        $v_val = explode(':', $p_telemetry->getRSSI());
        $v_rssi = (isset($v_val[1]) ? intval($v_val[1]) : 0);
      }
      if ($v_rssi != -110) {
        ArubaIotTool::log('debug', "--> Update Triangulation data for ".$p_reporter->getMac()." RSSI : ".$v_rssi);

        // TBC : Don't know if I need to update the widget each time for this value, that should not be visible ...
        // triangulation values are more for calculation than to be displayed like than on the screen (unless troubleshooting)
        //$this->widget_change_flag = $p_jeedom_object->cmdUpdateTriangulation($p_reporter->getMac(), $v_rssi, $p_reporter->getLastSeen()) || $this->widget_change_flag;
        $p_jeedom_object->cmdUpdateTriangulation($p_reporter->getMac(), $v_rssi, $p_reporter->getLastSeen());
      }

      return($this->widget_change_flag);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateInputsTelemetry()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateInputsTelemetry(&$p_jeedom_object, $p_telemetry) {

      ArubaIotTool::log('debug', "Update Inputs Telemetry");

      $p_changed_flag = false;

      $v_inputs = $p_telemetry->getInputs();

      // ----- Look for rocket list
      if ($v_inputs->hasRockerList()) {
        ArubaIotTool::log('debug', "Update Switch Rocker Telemetry");
        $v_rocker_list = $v_inputs->getRockerList();
        $i = 1;
        foreach ($v_rocker_list as $v_rocker) {
          $v_id = $v_rocker->getId();
          $v_state = $v_rocker->getState();
          ArubaIotTool::log('debug', "Rocker id: '".$v_id."', state: ".$v_state);

          // ----- Look for Enocean case, where state is in the id ...
          // "switch bank 1: idle"
          if ((strstr($v_id, 'switch bank 1:') !== null) || (strstr($v_id, 'switch bank 2:') !== null)) {
            $v_val = explode(':', $v_id);
            ArubaIotTool::log('debug', "Rocker real id: '".$v_val[0]."', real state: ".trim($v_val[1]));
            //$v_cmd_id = str_replace(' ', '_', trim($v_val[0]));
            $v_cmd_id = str_replace('switch bank ', 'button_', trim($v_val[0]));
            $v_cmd_name = str_replace('switch bank', 'Button', trim($v_val[0]));
            $p_changed_flag = $p_jeedom_object->createAndUpdateDynCmd($v_cmd_id, trim($v_val[1]), $v_cmd_name, 'info', 'string', true) || $p_changed_flag;
          }
          else {
            $p_changed_flag = $p_jeedom_object->createAndUpdateDynCmd($v_id, $v_state, $v_id, 'info', 'string', true) || $p_changed_flag;
          }
          $i++;
        }
      }

      // ----- Look for switch list
      if ($v_inputs->hasSwitchIndexList()) {
        ArubaIotTool::log('debug', "Update Switch Index Telemetry");
        // TBC : strange to be a list, then multiple ids ... or list should all the time be in same order ... ?
        $v_switch_list = $v_inputs->getSwitchIndexList();
        foreach ($v_switch_list as $v_switch) {
          // ----- Its an enum so value should be direct like that ...
          $v_state = $v_switch->value();
          ArubaIotTool::log('debug', "Switch value: ".$v_state);
          $p_changed_flag = $p_jeedom_object->createAndUpdateDynCmd('switch', $v_state, 'Switch', 'info', 'string', false) || $p_changed_flag;
        }
      }

      return($p_changed_flag);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateSensorTelemetry()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateSensorTelemetry(&$p_jeedom_object, $p_telemetry) {

      ArubaIotTool::log('debug', "Update Sensor Telemetry data");

      $p_changed_flag = false;

      // ----- Look for sensor telemetry value
      if ($p_telemetry->hasSensors()) {

        $v_item = $p_telemetry->getSensors();

        // ----- Look for illumination values
        if ($v_item->hasIllumination()) {
          $v_val = $v_item->getIllumination();
          ArubaIotTool::log('debug', "Illumination value is : ".$v_val);
          //$this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('illumination', $v_val, 'Illumination', 'info', 'numeric', true) || $this->widget_change_flag;
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('illumination', $v_val) || $this->widget_change_flag;
        }

        // ----- Look for occupancy values
        if ($v_item->hasOccupancy()) {
          $v_level = $v_item->getOccupancy()->getLevel();
          ArubaIotTool::log('debug', "Occupancy value is : ".$v_level);
          //$this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('occupancy', $v_level, 'Occupancy', 'info', 'numeric', true) || $this->widget_change_flag;
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('occupancy', $v_level) || $this->widget_change_flag;
        }

        if ($v_item->hasTemperatureC()) {
          $v_val = $v_item->getTemperatureC();
          ArubaIotTool::log('debug', "TemperatureC value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('temperatureC', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasHumidity()) {
          $v_val = $v_item->getHumidity();
          ArubaIotTool::log('debug', "Humidity value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('humidity', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasVoltage()) {
          $v_val = $v_item->getVoltage();
          ArubaIotTool::log('debug', "Voltage value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('voltage', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasCO()) {
          $v_val = $v_item->getCO();
          ArubaIotTool::log('debug', "CO value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('CO', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasCO2()) {
          $v_val = $v_item->getCO2();
          ArubaIotTool::log('debug', "CO2 value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('CO2', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasVOC()) {
          $v_val = $v_item->getVOC();
          ArubaIotTool::log('debug', "VOC value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('VOC', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasMotion()) {
          $v_val = $v_item->getMotion();
          ArubaIotTool::log('debug', "Motion value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('motion', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasResistance()) {
          $v_val = $v_item->getResistance();
          ArubaIotTool::log('debug', "Resistance value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('resistance', $v_val) || $this->widget_change_flag;
        }

        if ($v_item->hasPressure()) {
          $v_val = $v_item->getPressure();
          ArubaIotTool::log('debug', "Pressure value is : ".$v_val);
          $this->widget_change_flag = $p_jeedom_object->createAndUpdateCmd('pressure', $v_val) || $this->widget_change_flag;
        }

        // ----- Update battery level
        if ($v_item->hasBattery()) {
          ArubaIotTool::log('debug', "Battery value is : ".$v_item->getBattery());
          $p_jeedom_object->batteryStatus($v_item->getBattery());
        }

        // ----- For future use :
        if ($v_item->hasCurrent()) {
          ArubaIotTool::log('debug', "Field hasCurrent() available. For future use.");
        }
        if ($v_item->hasDistance()) {
          ArubaIotTool::log('debug', "Field hasDistance() available. For future use.");
        }
        if ($v_item->hasMechanicalHandle()) {
          ArubaIotTool::log('debug', "Field hasMechanicalHandle() available. For future use.");
        }
        if ($v_item->hasCapacitance()) {
          ArubaIotTool::log('debug', "Field hasCapacitance() available. For future use.");
        }

      }

      return($this->widget_change_flag);
    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateObjectClass()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateObjectClass(&$v_jeedom_object, $p_telemetry, $p_class_name) {

      $v_jeedom_class = $v_jeedom_object->getConfiguration('class_type');

      // ----- Look for auto discovery of class_type
      if ($v_jeedom_class == 'auto') {

        // ----- Update class type & infos
        $v_jeedom_object->setConfiguration('class_type', $p_class_name);
        if ($p_telemetry->hasVendorName()) {
          $v_jeedom_object->setConfiguration('vendor_name', $p_telemetry->getVendorName());
        }
        if ($p_telemetry->hasLocalName()) {
          $v_jeedom_object->setConfiguration('local_name', $p_telemetry->getLocalName());
        }
        if ($p_telemetry->hasModel()) {
          $v_jeedom_object->setConfiguration('model', $p_telemetry->getModel());
        }

        // Here is a trick I need to control the jeedom not to send back api request on refresh
        $v_jeedom_object->setConfiguration('trick_save_from_daemon', 'on');
	    $v_jeedom_object->save();
        $v_jeedom_object->setConfiguration('trick_save_from_daemon', 'off');

        ArubaIotTool::log('info', "Auto learn class type '".$p_class_name."' for device '".$this->getMac()."'.");
      }

      // ----- Look if mismatch of class_type
      else if ($v_jeedom_class != $p_class_name) {
        ArubaIotTool::log('debug', "Device '".$this->getMac()."' is announcing type '".$p_class_name."', when type '".$v_jeedom_class."' is expected. Skip telemetry data.");
     }

    }
    /* -------------------------------------------------------------------------*/

    /**---------------------------------------------------------------------------
     * Method : updateTelemetryData()
     * Description :
     * ---------------------------------------------------------------------------
     */
    public function updateTelemetryData(&$p_reporter, &$v_jeedom_object, $p_telemetry, $p_class_name) {

      $v_changed_flag = false;

      // ----- Update common telemetry data
      //$this->widget_change_flag = $this->updateRssiTelemetry($v_jeedom_object, $p_telemetry, $p_class_name) || $this->widget_change_flag;

      // ----- Update SwitchRocker info
      if ($p_telemetry->hasInputs()) {
        $this->widget_change_flag = $this->updateInputsTelemetry($v_jeedom_object, $p_telemetry) || $this->widget_change_flag;
      }

      // ----- Update sensors telemetry data
      if ($p_telemetry->hasSensors()) {
        $this->widget_change_flag = $this->updateSensorTelemetry($v_jeedom_object, $p_telemetry) || $this->widget_change_flag;
      }



      // ----- Look for hasTxpower() : Nothing to do now, but for future use
      if ($p_telemetry->hasTxpower()) {
        $v_val = $p_telemetry->getTxpower();
        ArubaIotTool::log('debug', "Txpower value is : ".$v_val);
        $this->widget_change_flag = $v_jeedom_object->createAndUpdateCmd('txpower', $v_val) || $this->widget_change_flag;
      }

      // ----- Look for hasCell() : Nothing to do now, but for future use
      if ($p_telemetry->hasCell()) {
        ArubaIotTool::log('debug', "This device has hasCell info.");
      }

      // ----- Look for hasStats() : Nothing to do now, but for future use
      if ($p_telemetry->hasStats()) {
        ArubaIotTool::log('debug', "This device has hasStats info.");
      }

      // ----- Look for hasIdentity() : Nothing to do now, but for future use
      if ($p_telemetry->hasIdentity()) {
        ArubaIotTool::log('debug', "This device has hasIdentity info.");
      }

      // ----- Look for vendor data : Nothing to do now, but for future use
      if ($p_telemetry->hasVendorData()) {
        ArubaIotTool::log('debug', "This device has vendor data of size ".sizeof($p_telemetry->getVendorData()));
      }

      // ----- Look for need to update widget
      $v_jeedom_object->refreshWidget();

      return(true);
    }
    /* -------------------------------------------------------------------------*/

  }
  /* -------------------------------------------------------------------------*/





  // ----- Instanciate the class which will manage the data from the websocket server
  $aruba_iot_websocket = new ArubaIotWebsocket();

  // ----- Initialize my websocket object
  $aruba_iot_websocket->init();

  /**
   * This section is mainly inherited from Ratchet Websocket sample code.
   *
   *
   */


  $loop   = \React\EventLoop\Factory::create();


  $v_timeout = $aruba_iot_websocket->getInterruptTimeout();
  ArubaIotTool::log('debug', "Set interrupt timeout to : ".$v_timeout." secondes");

  $loop->addPeriodicTimer(
    $v_timeout,
    function () use (&$aruba_iot_websocket) {
        $aruba_iot_websocket->onInterrupt();
    }
  );

  // ----- Create socket on IP and port
  try {

    // doc : http://socketo.me/api/class-React.Socket.Server.html
    $socket = new \React\Socket\Server($aruba_iot_websocket->getIpAddress().':'.$aruba_iot_websocket->getTcpPort(), $loop);


  $closeFrameChecker = new \Ratchet\RFC6455\Messaging\CloseFrameChecker;
  $negotiator = new \Ratchet\RFC6455\Handshake\ServerNegotiator(new \Ratchet\RFC6455\Handshake\RequestVerifier, PermessageDeflateOptions::permessageDeflateSupported());

  $uException = new \UnderflowException;

  $socket->on('connection', function (React\Socket\ConnectionInterface $connection) use (&$aruba_iot_websocket, $negotiator, $closeFrameChecker, $uException, $socket) {
      $headerComplete = false;
      $buffer = '';
      $parser = null;
      $connection->on('data', function ($data) use (&$aruba_iot_websocket, &$connection, &$parser, &$headerComplete, &$buffer, $negotiator, $closeFrameChecker, $uException, $socket) {
          if ($headerComplete) {
              $parser->onData($data);
              return;
          }

          // ----- New connection
          //$aruba_iot_websocket->onOpen($connection);

          // ----- Extract HTTP Header from payload
          $buffer .= $data;
          $parts = explode("\r\n\r\n", $buffer);
          if (count($parts) < 2) {
              return;
          }

          //echo "Received HTTP Header -----\n";
          //var_dump($parts[0]);
          //echo "\n-----\n";

          // ----- Parse HTTTP Header
          $headerComplete = true;
          $psrRequest = \GuzzleHttp\Psr7\parse_request($parts[0] . "\r\n\r\n");

          // ----- Look for URI commands
          if ($psrRequest->getUri()->getPath() === '/api') {
            // ----- Call API parser
            $v_json_response = $aruba_iot_websocket->onApiCall($connection, (isset($parts[1])?$parts[1]:''));

            // ----- Send response
            $connection->end(\GuzzleHttp\Psr7\str(new Response(200, [], $v_json_response . PHP_EOL)));
            return;
          }
          else if ($psrRequest->getUri()->getPath() === '/telemetry') {
            ArubaIotTool::log('debug', "Received connection on Telemetry URI");
            $aruba_iot_websocket->onOpen($connection, 'telemetry');
          }
          else if ($psrRequest->getUri()->getPath() === '/rtls') {
            ArubaIotTool::log('debug', "Received connection on RTLS URI");
            $aruba_iot_websocket->onOpen($connection, 'rtls');
          }
          /* Need to add authentication for shutdown !!
          else if ($psrRequest->getUri()->getPath() === '/shutdown') {
              $connection->end(\GuzzleHttp\Psr7\str(new Response(200, [], 'Shutting down echo server.' . PHP_EOL)));
              $socket->close();
              return;
          }
          */
          else {
            ArubaIotTool::log('debug', "HTTP request on bad URI");
            // ----- Send response
            $connection->end(\GuzzleHttp\Psr7\str(new Response(403, [], '' . PHP_EOL)));
            return;
          }

          // ----- Perform Websocket handcheck
          $negotiatorResponse = $negotiator->handshake($psrRequest);

          $negotiatorResponse = $negotiatorResponse->withAddedHeader("Content-Length", "0");

          if ($negotiatorResponse->getStatusCode() !== 101 && $psrRequest->getUri()->getPath() === '/shutdown') {
              $connection->end(\GuzzleHttp\Psr7\str(new Response(200, [], 'Shutting down echo server.' . PHP_EOL)));
              $socket->close();
              return;
          };

          //echo "Negociator response -----\n";
          //var_dump(\GuzzleHttp\Psr7\str($negotiatorResponse));
          //echo "-----\n";

          $connection->write(\GuzzleHttp\Psr7\str($negotiatorResponse));

          if ($negotiatorResponse->getStatusCode() !== 101) {
              $connection->end();
              return;
          }

          // ----- New valid handchecked connection
          //$aruba_iot_websocket->onOpen($connection);

          // there is no need to look through the client requests
          // we support any valid permessage deflate
          $deflateOptions = PermessageDeflateOptions::fromRequestOrResponse($psrRequest)[0];

          $parser = new \Ratchet\RFC6455\Messaging\MessageBuffer($closeFrameChecker,
              function (MessageInterface $message, MessageBuffer $messageBuffer) use (&$aruba_iot_websocket, &$connection) {


                // onData() method is called for each received message, extracted from Websocket frame format
                // But still in protobuf format for Aruba Websocket

                // ----- Analyse message
                if (!$aruba_iot_websocket->onMessage($connection, $message->getPayload())) {
                  // ----- Close connection
                  $connection->end(\GuzzleHttp\Psr7\str(new Response(403, [], '' . PHP_EOL)));
                  return;
                }

                // ----- If a message need to be sent back, this would be done here
                //$messageBuffer->sendMessage($message->getPayload(), true, $message->isBinary());

              }, function (FrameInterface $frame) use (&$aruba_iot_websocket, &$connection, &$parser) {
                  switch ($frame->getOpCode()) {
                      case Frame::OP_CLOSE:
                          $aruba_iot_websocket->onClose($connection);
                          $connection->end($frame->getContents());
                          break;
                      case Frame::OP_PING:
                          $aruba_iot_websocket->onPingMessage($connection);
                          $connection->write($parser->newFrame($frame->getPayload(), true, Frame::OP_PONG)->getContents());
                          break;
                  }
              }, true, function () use ($uException) {
                  return $uException;
              },
              null,
              null,
             [$connection, 'write'],
             $deflateOptions);

          // ----- Retire la partie header HTTP, pour ne garder que la payload
          array_shift($parts);
          $parser->onData(implode("\r\n\r\n", $parts));
      });
/*
      $connection->on('end', function () use (&$aruba_iot_websocket, &$connection) {
        $aruba_iot_websocket->onClose($connection);
      });
*/
      $connection->on('close', function () use (&$aruba_iot_websocket, &$connection) {
        $aruba_iot_websocket->onClose($connection);
      });
      $connection->on('error', function (Exception $e) use (&$aruba_iot_websocket, &$connection) {
        //echo 'error: ' . $e->getMessage();
        ArubaIotTool::log('debug', "Received error on connection '".$connection->my_id."' : ".$e->getMessage());
      });
  });


  ArubaIotTool::log('debug', "");
  ArubaIotTool::log('debug', "-----");
  ArubaIotTool::log('debug', "Start Websocket Server Loop (".date("Y-m-d H:i:s").")");
  ArubaIotTool::log('debug', " -> listening on ".$aruba_iot_websocket->getIpAddress().':'.$aruba_iot_websocket->getTcpPort()."");
  ArubaIotTool::log('debug', "-----");

  ArubaIotTool::log('info', "Listening on port ".$aruba_iot_websocket->getIpAddress().":".$aruba_iot_websocket->getTcpPort());

  // ----- Start Websocket loop
    //while (1) { $v=1; }
    $loop->run();

  } catch (\Exception $e) {
    ArubaIotTool::log('error', 'Daemon crash with following error: ' . $e->getMessage());
  }

?>

