<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class SalesInvoiceLine extends Model
{
    use Findable;
    use Storable;

    protected $fillable = [
        'ID',
        'AmountDC',
        'AmountFC',
        'CostCenter',
        'CostCenterDescription',
        'CostUnit',
        'CostUnitDescription',
        'CustomerItemCode',
        'CustomField',
        'DeliveryDate',
        'Description',
        'Discount',
        'Division',
        'Employee',
        'EmployeeFullName',
        'EndTime',
        'ExtraDutyAmountFC',
        'ExtraDutyPercentage',
        'GLAccount',
        'GLAccountDescription',
        'InvoiceID',
        'Item',
        'ItemCode',
        'ItemDescription',
        'LineNumber',
        'NetPrice',
        'Notes',
        'Pricelist',
        'PricelistDescription',
        'Project',
        'ProjectDescription',
        'ProjectWBS',
        'ProjectWBSDescription',
        'Quantity',
        'SalesOrder',
        'SalesOrderLine',
        'SalesOrderLineNumber',
        'SalesOrderNumber',
        'StartTime',
        'Subscription',
        'SubscriptionDescription',
        'UnitCode',
        'UnitDescription',
        'UnitPrice',
        'VATAmountDC',
        'VATAmountFC',
        'VATCode',
        'VATCodeDescription',
        'VATPercentage',
    ];

    protected $url = 'salesinvoice/SalesInvoiceLines';
}
