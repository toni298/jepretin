<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generate(string $url): string
    {
        return (string) QrCode::format('svg')
            ->size(320)
            ->errorCorrection('H')
            ->generate($url);
    }
}
