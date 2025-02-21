<?php

namespace App\Dto;

use App\Protobuf\PresetSubtitleFont;
use App\Protobuf\PresetSubtitleOutlineThickness;
use App\Protobuf\PresetSubtitleShadow;

class UploadVideoPreset
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

    public function __construct()
    {
        $this->subtitleFont = PresetSubtitleFont::name(PresetSubtitleFont::ARIAL);
        $this->subtitleShadow = (string) PresetSubtitleShadow::SHADOW_MEDIUM;
        $this->subtitleShadowColor = '#000000';
        $this->subtitleOutlineThickness = (string) PresetSubtitleOutlineThickness::OUTLINE_MEDIUM;
        $this->subtitleOutlineColor = '#000000';
        $this->subtitleBold = '0';
        $this->subtitleItalic = '0';
        $this->subtitleUnderline = '0';
        $this->subtitleColor = '#FFFFFF';
        $this->subtitleSize = '20';
    }
}