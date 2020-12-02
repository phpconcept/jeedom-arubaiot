# Jeedom ArubaIot Plugin

ArubaIot is a Jeedom plugin which implements a gateway role for Aruba Access Point (AP) IOT capabilities.
Today, ArubaIoT supports BLE capabilities of AP, and can understand few, wellknown devices : Aruba Tags, Enocean BLE Switch, Enocean BLE Sensor, some generic BLE devices.


## Requirements

You need to have a Jeedom application see https://www.jeedom.com for more details.

## Documentation

To Be Completed

---

### Aruba IOT Configuration Example

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
