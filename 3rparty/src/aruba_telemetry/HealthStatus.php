<?php
/**
 * Generated by Protobuf protoc plugin.
 *
 * File descriptor : aruba-iot-nb-ap-health-update.proto
 */


namespace aruba_telemetry;

/**
 * Protobuf enum : aruba_telemetry.HealthStatus
 */
class HealthStatus extends \Protobuf\Enum
{

    /**
     * healthy = 0
     */
    const healthy_VALUE = 0;

    /**
     * degraded = 1
     */
    const degraded_VALUE = 1;

    /**
     * unavailable = 2
     */
    const unavailable_VALUE = 2;

    /**
     * @var \aruba_telemetry\HealthStatus
     */
    protected static $healthy = null;

    /**
     * @var \aruba_telemetry\HealthStatus
     */
    protected static $degraded = null;

    /**
     * @var \aruba_telemetry\HealthStatus
     */
    protected static $unavailable = null;

    /**
     * @return \aruba_telemetry\HealthStatus
     */
    public static function healthy()
    {
        if (self::$healthy !== null) {
            return self::$healthy;
        }

        return self::$healthy = new self('healthy', self::healthy_VALUE);
    }

    /**
     * @return \aruba_telemetry\HealthStatus
     */
    public static function degraded()
    {
        if (self::$degraded !== null) {
            return self::$degraded;
        }

        return self::$degraded = new self('degraded', self::degraded_VALUE);
    }

    /**
     * @return \aruba_telemetry\HealthStatus
     */
    public static function unavailable()
    {
        if (self::$unavailable !== null) {
            return self::$unavailable;
        }

        return self::$unavailable = new self('unavailable', self::unavailable_VALUE);
    }

    /**
     * @param int $value
     * @return \aruba_telemetry\HealthStatus
     */
    public static function valueOf($value)
    {
        switch ($value) {
            case 0: return self::healthy();
            case 1: return self::degraded();
            case 2: return self::unavailable();
            default: return null;
        }
    }


}

