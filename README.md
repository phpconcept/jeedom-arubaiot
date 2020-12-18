# Jeedom ArubaIot Plugin

ArubaIot is a Jeedom plugin which implements a gateway role for Aruba Access Point (AP) IOT capabilities.
Today, ArubaIoT supports BLE capabilities of AP, and can understand few, wellknown devices : Aruba Tags, Enocean BLE Switch, Enocean BLE Sensor, some generic BLE devices.


## Requirements

You need to have a Jeedom ! (see https://www.jeedom.com).

## Documentation

### Introduction

The plugin is supporting the following type of BLE objects. For each object, is specified the list of supported info command.
- enoceanSensor (illumination, occupancy)
- enoceanSwitch (switch_bank_1, switch_bank_2)
- arubaTag (presence, rssi)
- arubaBeacon (rssi)
- generic (presence)

### Plugin Configuration

The following attributes can be configured :

- Device Types Allow List :
List of device types that are allowed to be learn when the daemon is in "include_mode". This is a comma separated lsit of types.
Supported types are those listed above.

- Presence Timeout :
The minimun value (in second) after which the device can be set to presence=0 (absence).

- Reporters Access Token :
The access token that will be configured in the Aruba AP. If empty, no access token will be verified.

- Reporters Allow List :
A comma separated list of reporters MAC@ (format AA:BB:00:00:00:00, with capitals) allowed to send telemetry data. If the list is empty
all reporters are accepted.

- Websocket IP Address :
The local IP address of the websocket in the jeedom. If 0.0.0.0, then will use the local IP address, no need to know it to start.

- Websocket TCP Port :
The TCP port where the jeedom will receive the connections. By default 8081. Must be a valid TCP port. If daemon fails to
connect then try an other free TCP port.

### Adding New Devices

To add new devices, click on the Add button and give a name for the device. You must then give a valid MAC@ of the device.
You can also give the type of object from the dropdown list. If you select "auto" then the daemon will learn the device type
when receiving the first telemetry frame.
Depending of the device type, some command information will be initiated for the device. Others will be automatically learnt when
receiving the telemetry frames from the device.

### Include Mode

To simplify the on-boarding of new device, you can select the "Include mode".

When started all new devices learnt by the daemon, and conform with the type allowed list, will be automatically added.

The include mode need to be disabled manually. Refresh the page to see the updated list.



---

### Aruba IOT Configuration Example

Below is an example of an Aruba IAP configuration to have the IOT Gateway sending telemetry data to ArubaIot Plugin.

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

Release v0.4 (in-dev) :
- Adding images for some of the BLE devices supported (Enocean, Aruba Tags)
- change the way plugin configuration is made
- Change the way devices are displayed in the plugin list
- Add enforcement of the access token for reporters

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
- Do not manage receiveing the same payload from different "reporters", which may lead to duplicate events like transition up/down for switche's buttons.
- The plugin is managing only part of the north bound Aruba API, it doesn't work with the south bound API.

## Behind the Scene

Explanation of some principles behind all this.

The main role of the Plugin is to receive the telemetry data, and transform it to jeedom info values.

But keep in mind the following points to understand what is the problem to be solved :

The Aruba AP is doing a caching of BLE frames received for a configurable aging timer (attribute "ageFilter" in IAP
configuration from 0 to 3600 sec) before sending a telemetry frame in the websocket connection. It will also resend the same data until teh expiration of the agingTimer.
The plugin daemon, will use the 'lastseen' value of the telemetry data in order to manage the presence/absence flag. It will also
update in a regular basis the presence flag when no more telemetry data is received for a configurable period of time.

Moreover by the broadcast nature of BLE, and the fact that an Aruba IAP network may be composed of several Access Point,
the same BLE frame will be received by several AP (known as IOT reporters), each reporter will report it to the daemon.
There is no sequence number nor easy way to identified the duplicated frames, unless compare the exact values.
Today the daemon is not managing this ... Which is not a big deal for information data (like temperature, illumination, etc)
but can be a concern when it is a push on a button : need to remove the duplicated events.

When started the daemon load the list of active devices, and some configuration values (ip address, tcp port, timers, ...).
The websocket daemon listen to the ip:port configured.
When a connection occur, the daemon perform the handcheck mecanism of the websocket standard with the client.
It then waits for data coming from this connection.
When receiveing data, it decodes the protobuf messages. In this message, the reporter (the Aruba Access Point) gives its
porperties (mac@, name, software version, local IP@, ...).
If the reporter is new, a local object is created in the daemon, for storing the properties. The connection is then associated
to a reporter.

The telemetry data are then decoded, and for each mac@, the daemon perform the following tasks :
- look if the device is know or not. If not, and the include_mode is on, and device class allowed, then it creates a new device in the jeedom DB.
- Then on each telemetry element of the data frame :
  - look if a command exists for the telemetry element (exemple : illumination), if not create the command (illumination) for the object
  - update the command with the latest value

When a changes occur at the device level in jeedom (new, updated, removed device), jeedom is sending an API message to the daemon to update the information.
Same for changing settings like incude_mode on/off.

