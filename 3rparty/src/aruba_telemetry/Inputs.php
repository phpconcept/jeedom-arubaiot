<?php
/**
 * Generated by Protobuf protoc plugin.
 *
 * File descriptor : aruba-iot-nb-telemetry.proto
 */


namespace aruba_telemetry;

/**
 * Protobuf message : aruba_telemetry.Inputs
 */
class Inputs extends \Protobuf\AbstractMessage
{

    /**
     * @var \Protobuf\UnknownFieldSet
     */
    protected $unknownFieldSet = null;

    /**
     * @var \Protobuf\Extension\ExtensionFieldMap
     */
    protected $extensions = null;

    /**
     * rocker repeated message = 1
     *
     * @var \Protobuf\Collection<\aruba_telemetry\RockerSwitch>
     */
    protected $rocker = null;

    /**
     * switchIndex repeated enum = 2
     *
     * @var \Protobuf\Collection<\aruba_telemetry\switchState>
     */
    protected $switchIndex = null;

    /**
     * Check if 'rocker' has a value
     *
     * @return bool
     */
    public function hasRockerList()
    {
        return $this->rocker !== null;
    }

    /**
     * Get 'rocker' value
     *
     * @return \Protobuf\Collection<\aruba_telemetry\RockerSwitch>
     */
    public function getRockerList()
    {
        return $this->rocker;
    }

    /**
     * Set 'rocker' value
     *
     * @param \Protobuf\Collection<\aruba_telemetry\RockerSwitch> $value
     */
    public function setRockerList(\Protobuf\Collection $value = null)
    {
        $this->rocker = $value;
    }

    /**
     * Add a new element to 'rocker'
     *
     * @param \aruba_telemetry\RockerSwitch $value
     */
    public function addRocker(\aruba_telemetry\RockerSwitch $value)
    {
        if ($this->rocker === null) {
            $this->rocker = new \Protobuf\MessageCollection();
        }

        $this->rocker->add($value);
    }

    /**
     * Check if 'switchIndex' has a value
     *
     * @return bool
     */
    public function hasSwitchIndexList()
    {
        return $this->switchIndex !== null;
    }

    /**
     * Get 'switchIndex' value
     *
     * @return \Protobuf\Collection<\aruba_telemetry\switchState>
     */
    public function getSwitchIndexList()
    {
        return $this->switchIndex;
    }

    /**
     * Set 'switchIndex' value
     *
     * @param \Protobuf\Collection<\aruba_telemetry\switchState> $value
     */
    public function setSwitchIndexList(\Protobuf\Collection $value = null)
    {
        $this->switchIndex = $value;
    }

    /**
     * Add a new element to 'switchIndex'
     *
     * @param \aruba_telemetry\switchState $value
     */
    public function addSwitchIndex(\aruba_telemetry\switchState $value)
    {
        if ($this->switchIndex === null) {
            $this->switchIndex = new \Protobuf\EnumCollection();
        }

        $this->switchIndex->add($value);
    }

    /**
     * {@inheritdoc}
     */
    public function extensions()
    {
        if ( $this->extensions !== null) {
            return $this->extensions;
        }

        return $this->extensions = new \Protobuf\Extension\ExtensionFieldMap(__CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public function unknownFieldSet()
    {
        return $this->unknownFieldSet;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromStream($stream, \Protobuf\Configuration $configuration = null)
    {
        return new self($stream, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        $message = new self();
        $values  = array_merge([
            'rocker' => [],
            'switchIndex' => []
        ], $values);

        foreach ($values['rocker'] as $item) {
            $message->addRocker($item);
        }

        foreach ($values['switchIndex'] as $item) {
            $message->addSwitchIndex($item);
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public static function descriptor()
    {
        return \google\protobuf\DescriptorProto::fromArray([
            'name'      => 'Inputs',
            'field'     => [
                \google\protobuf\FieldDescriptorProto::fromArray([
                    'number' => 1,
                    'name' => 'rocker',
                    'type' => \google\protobuf\FieldDescriptorProto\Type::TYPE_MESSAGE(),
                    'label' => \google\protobuf\FieldDescriptorProto\Label::LABEL_REPEATED(),
                    'type_name' => '.aruba_telemetry.RockerSwitch'
                ]),
                \google\protobuf\FieldDescriptorProto::fromArray([
                    'number' => 2,
                    'name' => 'switchIndex',
                    'type' => \google\protobuf\FieldDescriptorProto\Type::TYPE_ENUM(),
                    'label' => \google\protobuf\FieldDescriptorProto\Label::LABEL_REPEATED(),
                    'type_name' => '.aruba_telemetry.switchState'
                ]),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function toStream(\Protobuf\Configuration $configuration = null)
    {
        $config  = $configuration ?: \Protobuf\Configuration::getInstance();
        $context = $config->createWriteContext();
        $stream  = $context->getStream();

        $this->writeTo($context);
        $stream->seek(0);

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function writeTo(\Protobuf\WriteContext $context)
    {
        $stream      = $context->getStream();
        $writer      = $context->getWriter();
        $sizeContext = $context->getComputeSizeContext();

        if ($this->rocker !== null) {
            foreach ($this->rocker as $val) {
                $writer->writeVarint($stream, 10);
                $writer->writeVarint($stream, $val->serializedSize($sizeContext));
                $val->writeTo($context);
            }
        }

        if ($this->switchIndex !== null) {
            foreach ($this->switchIndex as $val) {
                $writer->writeVarint($stream, 16);
                $writer->writeVarint($stream, $val->value());
            }
        }

        if ($this->extensions !== null) {
            $this->extensions->writeTo($context);
        }

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function readFrom(\Protobuf\ReadContext $context)
    {
        $reader = $context->getReader();
        $length = $context->getLength();
        $stream = $context->getStream();

        $limit = ($length !== null)
            ? ($stream->tell() + $length)
            : null;

        while ($limit === null || $stream->tell() < $limit) {

            if ($stream->eof()) {
                break;
            }

            $key  = $reader->readVarint($stream);
            $wire = \Protobuf\WireFormat::getTagWireType($key);
            $tag  = \Protobuf\WireFormat::getTagFieldNumber($key);

            if ($stream->eof()) {
                break;
            }

            if ($tag === 1) {
                \Protobuf\WireFormat::assertWireType($wire, 11);

                $innerSize    = $reader->readVarint($stream);
                $innerMessage = new \aruba_telemetry\RockerSwitch();

                if ($this->rocker === null) {
                    $this->rocker = new \Protobuf\MessageCollection();
                }

                $this->rocker->add($innerMessage);

                $context->setLength($innerSize);
                $innerMessage->readFrom($context);
                $context->setLength($length);

                continue;
            }

            if ($tag === 2) {
                \Protobuf\WireFormat::assertWireType($wire, 14);

                if ($this->switchIndex === null) {
                    $this->switchIndex = new \Protobuf\EnumCollection();
                }

                $this->switchIndex->add(\aruba_telemetry\switchState::valueOf($reader->readVarint($stream)));

                continue;
            }

            $extensions = $context->getExtensionRegistry();
            $extension  = $extensions ? $extensions->findByNumber(__CLASS__, $tag) : null;

            if ($extension !== null) {
                $this->extensions()->add($extension, $extension->readFrom($context, $wire));

                continue;
            }

            if ($this->unknownFieldSet === null) {
                $this->unknownFieldSet = new \Protobuf\UnknownFieldSet();
            }

            $data    = $reader->readUnknown($stream, $wire);
            $unknown = new \Protobuf\Unknown($tag, $wire, $data);

            $this->unknownFieldSet->add($unknown);

        }
    }

    /**
     * {@inheritdoc}
     */
    public function serializedSize(\Protobuf\ComputeSizeContext $context)
    {
        $calculator = $context->getSizeCalculator();
        $size       = 0;

        if ($this->rocker !== null) {
            foreach ($this->rocker as $val) {
                $innerSize = $val->serializedSize($context);

                $size += 1;
                $size += $innerSize;
                $size += $calculator->computeVarintSize($innerSize);
            }
        }

        if ($this->switchIndex !== null) {
            foreach ($this->switchIndex as $val) {
                $size += 1;
                $size += $calculator->computeVarintSize($val->value());
            }
        }

        if ($this->extensions !== null) {
            $size += $this->extensions->serializedSize($context);
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->rocker = null;
        $this->switchIndex = null;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(\Protobuf\Message $message)
    {
        if ( ! $message instanceof \aruba_telemetry\Inputs) {
            throw new \InvalidArgumentException(sprintf('Argument 1 passed to %s must be a %s, %s given', __METHOD__, __CLASS__, get_class($message)));
        }

        $this->rocker = ($message->rocker !== null) ? $message->rocker : $this->rocker;
        $this->switchIndex = ($message->switchIndex !== null) ? $message->switchIndex : $this->switchIndex;
    }


}
