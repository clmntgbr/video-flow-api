<?php

namespace App\Dto;

use App\Protobuf\ConfigurationSubtitleFont;
use App\Protobuf\ConfigurationSubtitleOutlineThickness;
use App\Protobuf\ConfigurationSubtitleShadow;
use App\Protobuf\VideoFormatStyle;

class UploadVideoConfiguration
{
    public ?string $subtitleFont = null;
    public ?string $subtitleSize = null;
    public ?string $subtitleColor = null;
    public ?string $subtitleBold = null;
    public ?string $subtitleItalic = null;
    public ?string $subtitleUnderline = null;
    public ?string $subtitleOutlineColor = null;
    public ?string $subtitleOutlineThickness = null;
    public ?string $subtitleShadow = null;
    public ?string $subtitleShadowColor = null;
    public ?string $format = null;
    public ?string $split = null;

    public function __construct()
    {
        $this->format = VideoFormatStyle::name(VideoFormatStyle::ORIGINAL);
        $this->subtitleFont = ConfigurationSubtitleFont::name(ConfigurationSubtitleFont::ARIAL);
        $this->subtitleShadow = (string) ConfigurationSubtitleShadow::SHADOW_MEDIUM;
        $this->subtitleShadowColor = '#000000';
        $this->subtitleOutlineThickness = (string) ConfigurationSubtitleOutlineThickness::OUTLINE_MEDIUM;
        $this->subtitleOutlineColor = '#000000';
        $this->subtitleBold = '0';
        $this->subtitleItalic = '0';
        $this->subtitleUnderline = '0';
        $this->subtitleColor = '#FFFFFF';
        $this->subtitleSize = '20';
        $this->split = '1';
    }
}
