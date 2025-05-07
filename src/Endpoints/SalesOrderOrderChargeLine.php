<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class SalesOrderOrderChargeLine extends Model
{
    use Findable;
    use Storable;

    protected $fillable = [
        'ID',
        'AmountDC',
        'AmountFCExclVAT',
        'AmountFCInclVAT',
        'AmountVATFC',
        'Division',
        'IsShippingCost',
        'LineNumber',
        'OrderCharge',
        'OrderChargeCode',
        'OrderChargeDescription',
        'OrderChargesLineDescription',
        'OrderID',
        'VATCode',
        'VATDescription',
        'VATPercentage',
    ];

    protected $url = 'salesorder/SalesOrderOrderChargeLines';
}
