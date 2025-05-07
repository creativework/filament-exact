<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class GoodsReceiptLine extends Model
{
    use Findable;
    use Storable;

    protected $fillable = [
        'ID',
        'BatchNumbers',
        'Created',
        'Creator',
        'CreatorFullName',
        'Description',
        'Division',
        'Expense',
        'ExpenseDescription',
        'GoodsReceiptID',
        'Item',
        'ItemCode',
        'ItemDescription',
        'ItemUnitCode',
        'LineNumber',
        'Location',
        'LocationCode',
        'LocationDescription',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'Notes',
        'Project',
        'ProjectCode',
        'ProjectDescription',
        'PurchaseOrderID',
        'PurchaseOrderLineID',
        'PurchaseOrderNumber',
        'QuantityOrdered',
        'QuantityReceived',
        'Rebill',
        'SerialNumbers',
        'SupplierItemCode',
    ];

    protected $url = 'purchaseorder/GoodsReceiptLines';
}
