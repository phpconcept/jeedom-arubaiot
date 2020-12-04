<?php
/**
 * Generated by Protobuf protoc plugin.
 *
 * File descriptor : aruba-iot-nb-telemetry.proto
 */


namespace aruba_telemetry;

/**
 * Protobuf enum : aruba_telemetry.MechanicalH
 */
class MechanicalH extends \Protobuf\Enum
{

    /**
     * upToRight = 1
     */
    const upToRight_VALUE = 1;

    /**
     * rightToDown = 2
     */
    const rightToDown_VALUE = 2;

    /**
     * downToLeft = 3
     */
    const downToLeft_VALUE = 3;

    /**
     * leftToUp = 4
     */
    const leftToUp_VALUE = 4;

    /**
     * upToLeft = 5
     */
    const upToLeft_VALUE = 5;

    /**
     * leftToDown = 6
     */
    const leftToDown_VALUE = 6;

    /**
     * downToRight = 7
     */
    const downToRight_VALUE = 7;

    /**
     * rightToUp = 8
     */
    const rightToUp_VALUE = 8;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $upToRight = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $rightToDown = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $downToLeft = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $leftToUp = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $upToLeft = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $leftToDown = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $downToRight = null;

    /**
     * @var \aruba_telemetry\MechanicalH
     */
    protected static $rightToUp = null;

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function upToRight()
    {
        if (self::$upToRight !== null) {
            return self::$upToRight;
        }

        return self::$upToRight = new self('upToRight', self::upToRight_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function rightToDown()
    {
        if (self::$rightToDown !== null) {
            return self::$rightToDown;
        }

        return self::$rightToDown = new self('rightToDown', self::rightToDown_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function downToLeft()
    {
        if (self::$downToLeft !== null) {
            return self::$downToLeft;
        }

        return self::$downToLeft = new self('downToLeft', self::downToLeft_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function leftToUp()
    {
        if (self::$leftToUp !== null) {
            return self::$leftToUp;
        }

        return self::$leftToUp = new self('leftToUp', self::leftToUp_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function upToLeft()
    {
        if (self::$upToLeft !== null) {
            return self::$upToLeft;
        }

        return self::$upToLeft = new self('upToLeft', self::upToLeft_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function leftToDown()
    {
        if (self::$leftToDown !== null) {
            return self::$leftToDown;
        }

        return self::$leftToDown = new self('leftToDown', self::leftToDown_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function downToRight()
    {
        if (self::$downToRight !== null) {
            return self::$downToRight;
        }

        return self::$downToRight = new self('downToRight', self::downToRight_VALUE);
    }

    /**
     * @return \aruba_telemetry\MechanicalH
     */
    public static function rightToUp()
    {
        if (self::$rightToUp !== null) {
            return self::$rightToUp;
        }

        return self::$rightToUp = new self('rightToUp', self::rightToUp_VALUE);
    }

    /**
     * @param int $value
     * @return \aruba_telemetry\MechanicalH
     */
    public static function valueOf($value)
    {
        switch ($value) {
            case 1: return self::upToRight();
            case 2: return self::rightToDown();
            case 3: return self::downToLeft();
            case 4: return self::leftToUp();
            case 5: return self::upToLeft();
            case 6: return self::leftToDown();
            case 7: return self::downToRight();
            case 8: return self::rightToUp();
            default: return null;
        }
    }


}

