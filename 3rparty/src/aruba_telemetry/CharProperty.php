<?php
/**
 * Generated by Protobuf protoc plugin.
 *
 * File descriptor : aruba-iot-nb-characteristic.proto
 */


namespace aruba_telemetry;

/**
 * Protobuf enum : aruba_telemetry.CharProperty
 */
class CharProperty extends \Protobuf\Enum
{

    /**
     * broadcast = 0
     */
    const broadcast_VALUE = 0;

    /**
     * read = 1
     */
    const read_VALUE = 1;

    /**
     * writeWithoutResponse = 2
     */
    const writeWithoutResponse_VALUE = 2;

    /**
     * writeWithResponse = 3
     */
    const writeWithResponse_VALUE = 3;

    /**
     * notify = 4
     */
    const notify_VALUE = 4;

    /**
     * indicate = 5
     */
    const indicate_VALUE = 5;

    /**
     * signedWrite = 6
     */
    const signedWrite_VALUE = 6;

    /**
     * writeReliable = 7
     */
    const writeReliable_VALUE = 7;

    /**
     * writeAux = 8
     */
    const writeAux_VALUE = 8;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $broadcast = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $read = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $writeWithoutResponse = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $writeWithResponse = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $notify = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $indicate = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $signedWrite = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $writeReliable = null;

    /**
     * @var \aruba_telemetry\CharProperty
     */
    protected static $writeAux = null;

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function broadcast()
    {
        if (self::$broadcast !== null) {
            return self::$broadcast;
        }

        return self::$broadcast = new self('broadcast', self::broadcast_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function read()
    {
        if (self::$read !== null) {
            return self::$read;
        }

        return self::$read = new self('read', self::read_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function writeWithoutResponse()
    {
        if (self::$writeWithoutResponse !== null) {
            return self::$writeWithoutResponse;
        }

        return self::$writeWithoutResponse = new self('writeWithoutResponse', self::writeWithoutResponse_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function writeWithResponse()
    {
        if (self::$writeWithResponse !== null) {
            return self::$writeWithResponse;
        }

        return self::$writeWithResponse = new self('writeWithResponse', self::writeWithResponse_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function notify()
    {
        if (self::$notify !== null) {
            return self::$notify;
        }

        return self::$notify = new self('notify', self::notify_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function indicate()
    {
        if (self::$indicate !== null) {
            return self::$indicate;
        }

        return self::$indicate = new self('indicate', self::indicate_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function signedWrite()
    {
        if (self::$signedWrite !== null) {
            return self::$signedWrite;
        }

        return self::$signedWrite = new self('signedWrite', self::signedWrite_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function writeReliable()
    {
        if (self::$writeReliable !== null) {
            return self::$writeReliable;
        }

        return self::$writeReliable = new self('writeReliable', self::writeReliable_VALUE);
    }

    /**
     * @return \aruba_telemetry\CharProperty
     */
    public static function writeAux()
    {
        if (self::$writeAux !== null) {
            return self::$writeAux;
        }

        return self::$writeAux = new self('writeAux', self::writeAux_VALUE);
    }

    /**
     * @param int $value
     * @return \aruba_telemetry\CharProperty
     */
    public static function valueOf($value)
    {
        switch ($value) {
            case 0: return self::broadcast();
            case 1: return self::read();
            case 2: return self::writeWithoutResponse();
            case 3: return self::writeWithResponse();
            case 4: return self::notify();
            case 5: return self::indicate();
            case 6: return self::signedWrite();
            case 7: return self::writeReliable();
            case 8: return self::writeAux();
            default: return null;
        }
    }


}

