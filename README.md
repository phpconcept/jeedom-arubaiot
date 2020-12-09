# Jeedom ArubaIot Plugin

ArubaIot is a Jeedom plugin which implements a gateway role for Aruba Access Point (AP) IOT capabilities.
Today, ArubaIoT supports BLE capabilities of AP, and can understand few, wellknown devices : Aruba Tags, Enocean BLE Switch, Enocean BLE Sensor, some generic BLE devices.


## Requirements

You need to have a Jeedom application see https://www.jeedom.com for more details.

## Documentation

To Be Completed

---

### Aruba IOT Configuration Example

Please notice that URI "/telemetry" is used for the endpointURL. For all other attributes of the transportProfile, please refer to Aruba documentation. The right configuration will improve the load on the Jeedom box.


```cli
iot radio-profile Test
radio-mode ble

iot use-radio-profile Test

iot transportProfile Test
endpointType telemetry-websocket
payloadContent all
endpointURL ws://<jeedom_ip_address>:8081/telemetry
endpointToken 12346
transportInterval 60

iot useTransportProfile Test
```

## Change Logs

Release v0.3 (beta) :
- Adding systematic auto-add of commands for an equipement, when receiving the corresponding telemetry data
- Adding a filtering capabilities to deny some commands for specific object classes (no auto-add). Today static in method isAllowedCmdForClass()
- Adding support of comma separated list of reporters' mac@ allowed. If list is empty then all reporters are allowed.
- Adding support for multiple connections coming from same AP. This will allow future use of Telemetry/RTLS data including WiFi device connectivity.
- Modify the way presence/absence is updated


Release v0.2 (beta) :
- Adding support for inlusion mode of scanned devices
- Improve start/stop of websocket daemon
- Add an API communication channel between jeedom plugin and websocket daemon

Release v0.1 :
- First release


## Known Caveats

As of Release v0.2, known caveats are :
- /!\ Only ws:// is supported today by the websocket daemon, which means communication is in clear. No support yet of wss:// with certificate.
- Reporter endpointToken is not checked, all reporters are accepted by default.
- Do not manage receiveing the same payload from different "reporters", which may lead to duplicate events like transition up/down for switche's buttons.
- The plugin is managing only part of the north bound Aruba API, it doesn't work with the south bound API.

## Behind the Scene

Explanation of some principles behind all this.

The daemon has two main roles :
The main role is to receive the telemetry data, and transfort it to jeedom cmd.
But the Aruba AP is doing a caching of BLE frames received for a configurable aging timer (attribute "ageFilter" in IAP configuration from 0 to 3600 sec), the daemon will also check and ignore the repeated same telemetry data resent by the reporter. It is a best practice to select the right ageFilter value to optimize the amount of data that will have to be managed by the daemon.
The daemon will look for resent frame and ignore it.
Moreover by the broadcast nature of BLE, and the fact that an Aruba IAP network may be composed of several Access Point, the same BLE frame will be received by several AP (known as IOT reporters), each reporter will report it to the daemon.
There is no sequence number nor easy ways to identified the duplicated frames, unless compare the exact values. Today the daemon is not managing this ...

When started the daemon load the list of active devices, and the needed configuration values (like the include_mode, or the list of allowed device classes).
The websocket daemon listen to the ip:port configured.
When a connection occur, the daemon perform the handcheck mecanism of the websocket standard with the client. It then waits for data from this connection.
When receiveing data, it decodes the protobuf messages. In this message, the reporter (the Aruba Access Point) gives its porperties (mac@, name, software version, local IP@, ...).
If the reporter is new, a local object is created in the daemon, for storing these properties.
The telemetry data are then decoded, and for each mac@, the daemon perform the following tasks :
- look if the device is know or not. If not and the include_mode is on and device class allowed, then it creates a new device in the jeedom DB.
- look if the telemetry data for this device are not a duplicate from a previous message from the same reporter (using a cachin gmecanism in the daemon)
- Then on each element of the telemetry data :
- look if a command exists for the data element (exemple : illumination), if not create the command (illumination) for the object
- update the command with the new value

When a changes occur at the device level in jeedom (new, updated, removed device), jeedom is sending an API message to the daemon to update the information.
Same for changing settings like incude_mode on/off.

