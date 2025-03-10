<?php

// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: Message.proto

namespace App\Protobuf;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\GPBUtil;
use Google\Protobuf\Internal\RepeatedField;

/**
 * Generated from protobuf message <code>App.Protobuf.MediaPod</code>.
 */
class MediaPod extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string uuid = 1;</code>.
     */
    protected $uuid = '';
    /**
     * Generated from protobuf field <code>string userUuid = 2;</code>.
     */
    protected $userUuid = '';
    /**
     * Generated from protobuf field <code>.App.Protobuf.Video originalVideo = 3;</code>.
     */
    protected $originalVideo;
    /**
     * Generated from protobuf field <code>string status = 5;</code>.
     */
    protected $status = '';
    /**
     * Generated from protobuf field <code>.App.Protobuf.Configuration configuration = 6;</code>.
     */
    protected $configuration;
    /**
     * Generated from protobuf field <code>.App.Protobuf.Video processedVideo = 7;</code>.
     */
    protected $processedVideo;
    /**
     * Generated from protobuf field <code>repeated .App.Protobuf.Video finalVideo = 8;</code>.
     */
    private $finalVideo;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var string                     $uuid
     * @var string                     $userUuid
     * @var Video                      $originalVideo
     * @var string                     $status
     * @var Configuration              $configuration
     * @var Video                      $processedVideo
     * @var array<Video>|RepeatedField $finalVideo
     *                                 }
     */
    public function __construct($data = null)
    {
        GPBMetadata\Message::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string uuid = 1;</code>.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Generated from protobuf field <code>string uuid = 1;</code>.
     *
     * @param string $var
     *
     * @return $this
     */
    public function setUuid($var)
    {
        GPBUtil::checkString($var, true);
        $this->uuid = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string userUuid = 2;</code>.
     *
     * @return string
     */
    public function getUserUuid()
    {
        return $this->userUuid;
    }

    /**
     * Generated from protobuf field <code>string userUuid = 2;</code>.
     *
     * @param string $var
     *
     * @return $this
     */
    public function setUserUuid($var)
    {
        GPBUtil::checkString($var, true);
        $this->userUuid = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.Video originalVideo = 3;</code>.
     *
     * @return Video|null
     */
    public function getOriginalVideo()
    {
        return $this->originalVideo;
    }

    public function hasOriginalVideo()
    {
        return isset($this->originalVideo);
    }

    public function clearOriginalVideo()
    {
        unset($this->originalVideo);
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.Video originalVideo = 3;</code>.
     *
     * @param Video $var
     *
     * @return $this
     */
    public function setOriginalVideo($var)
    {
        GPBUtil::checkMessage($var, Video::class);
        $this->originalVideo = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string status = 5;</code>.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Generated from protobuf field <code>string status = 5;</code>.
     *
     * @param string $var
     *
     * @return $this
     */
    public function setStatus($var)
    {
        GPBUtil::checkString($var, true);
        $this->status = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.Configuration configuration = 6;</code>.
     *
     * @return Configuration|null
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function hasConfiguration()
    {
        return isset($this->configuration);
    }

    public function clearConfiguration()
    {
        unset($this->configuration);
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.Configuration configuration = 6;</code>.
     *
     * @param Configuration $var
     *
     * @return $this
     */
    public function setConfiguration($var)
    {
        GPBUtil::checkMessage($var, Configuration::class);
        $this->configuration = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.Video processedVideo = 7;</code>.
     *
     * @return Video|null
     */
    public function getProcessedVideo()
    {
        return $this->processedVideo;
    }

    public function hasProcessedVideo()
    {
        return isset($this->processedVideo);
    }

    public function clearProcessedVideo()
    {
        unset($this->processedVideo);
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.Video processedVideo = 7;</code>.
     *
     * @param Video $var
     *
     * @return $this
     */
    public function setProcessedVideo($var)
    {
        GPBUtil::checkMessage($var, Video::class);
        $this->processedVideo = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .App.Protobuf.Video finalVideo = 8;</code>.
     *
     * @return RepeatedField
     */
    public function getFinalVideo()
    {
        return $this->finalVideo;
    }

    /**
     * Generated from protobuf field <code>repeated .App.Protobuf.Video finalVideo = 8;</code>.
     *
     * @param array<Video>|RepeatedField $var
     *
     * @return $this
     */
    public function setFinalVideo($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, GPBType::MESSAGE, Video::class);
        $this->finalVideo = $arr;

        return $this;
    }
}
