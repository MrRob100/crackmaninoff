<?php

// declare(strict_types=1);

namespace App\Services;

class UrlService
{
    public function checkUrl($sub) {
        $sub = $sub.'t';
        if (
            is_string($sub)
            && strlen($sub) < 15
            && ctype_alnum($sub)
        ) {
            return true;
        } else {
            return false;
        }
    }
}
