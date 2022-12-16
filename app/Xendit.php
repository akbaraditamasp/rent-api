<?php

namespace Siluet;

use Xendit\Invoice;
use \Xendit\Xendit as Xen;

class Xendit
{
    private static $booted = false;

    public static function get()
    {
        if (!(static::$booted)) {
            Xen::setApiKey($_ENV["XENDIT_API"]);
            static::$booted = true;
        }

        return Invoice::class;
    }
}
