<?php

// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: Message.proto

namespace App\Protobuf;

use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>App.Protobuf.ApiToVideoSplitter</code>.
 */
class ApiToVideoSplitter extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.App.Protobuf.MediaPod mediaPod = 1;</code>.
     */
    protected $mediaPod;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var MediaPod $mediaPod
     *               }
     */
    public function __construct($data = null)
    {
        GPBMetadata\Message::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.MediaPod mediaPod = 1;</code>.
     *
     * @return MediaPod|null
     */
    public function getMediaPod()
    {
        return $this->mediaPod;
    }

    public function hasMediaPod()
    {
        return isset($this->mediaPod);
    }

    public function clearMediaPod()
    {
        unset($this->mediaPod);
    }

    /**
     * Generated from protobuf field <code>.App.Protobuf.MediaPod mediaPod = 1;</code>.
     *
     * @param MediaPod $var
     *
     * @return $this
     */
    public function setMediaPod($var)
    {
        GPBUtil::checkMessage($var, MediaPod::class);
        $this->mediaPod = $var;

        return $this;
    }
}
