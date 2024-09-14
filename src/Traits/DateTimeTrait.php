<?php

namespace Vedovelli\VedovelliPHPLib\Traits;

use Carbon\Carbon;

trait DateTimeTrait
{
    public function convertClientDateToDBDate(string $date): string
    {
        return Carbon::createFromFormat('dmY', str_replace('/', '', $date))->format('Y-m-d');
    }
}
