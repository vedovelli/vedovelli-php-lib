<?php

namespace Vedovelli\VedovelliPHPLib\Traits;

use NumberFormatter;

trait CurrencyFormatterTrait
{
    public function formatCurrency($amount): string
    {
        $formatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount / 100, 'BRL');
    }
}
