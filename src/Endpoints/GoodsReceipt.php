<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class GoodsReceipt extends Model
{
    use Findable;
    use Storable;

    protected $fillable = [
        'ID',
        'Created',
        'Creator',
        'CreatorFullName',
        'Description',
        'Division',
        'Document',
        'DocumentSubject',
        'EntryNumber',
        'GoodsReceiptLineCount',
        'GoodsReceiptLines',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'ReceiptDate',
        'ReceiptNumber',
        'Remarks',
        'Supplier',
        'SupplierCode',
        'SupplierContact',
        'SupplierContactFullName',
        'SupplierName',
        'Warehouse',
        'WarehouseCode',
        'WarehouseDescription',
        'YourRef',
    ];

    protected $url = 'purchaseorder/GoodsReceipts';
}
