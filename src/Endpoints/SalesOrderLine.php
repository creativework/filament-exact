<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class SalesOrderLine extends Model
{
    use Findable;
    use Storable;

    protected $fillable = [
        'ID',
        'AmountDC',
        'AmountFC',
        'CostCenter',
        'CostCenterDescription',
        'CostPriceFC',
        'CostUnit',
        'CostUnitDescription',
        'CustomerItemCode',
        'CustomField',
        'DeliveryDate',
        'DeliveryStatus',
        'Description',
        'Discount',
        'Division',
        'InvoiceStatus',
        'Item',
        'ItemCode',
        'ItemDescription',
        'ItemVersion',
        'ItemVersionDescription',
        'LineNumber',
        'Margin',
        'NetPrice',
        'Notes',
        'OrderID',
        'OrderNumber',
        'OrderStatus',
        'Pricelist',
        'PricelistDescription',
        'Project',
        'ProjectDescription',
        'PurchaseOrder',
        'PurchaseOrderLine',
        'PurchaseOrderLineNumber',
        'PurchaseOrderNumber',
        'Quantity',
        'QuantityDelivered',
        'QuantityInvoiced',
        'ShopOrder',
        'UnitCode',
        'UnitDescription',
        'UnitPrice',
        'UseDropShipment',
        'VATAmount',
        'VATCode',
        'VATCodeDescription',
        'VATPercentage',
    ];

    protected $url = 'salesorder/SalesOrderLines';
}
