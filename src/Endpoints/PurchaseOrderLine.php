<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class PurchaseOrderLine extends Model
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
        'Created',
        'Creator',
        'CreatorFullName',
        'CustomField',
        'Description',
        'Discount',
        'Division',
        'Expense',
        'ExpenseDescription',
        'InStock',
        'InvoicedQuantity',
        'IsBatchNumberItem',
        'IsSerialNumberItem',
        'Item',
        'ItemBarcode',
        'ItemBarcodeAdditional',
        'ItemCode',
        'ItemDescription',
        'ItemDivisable',
        'LineNumber',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'NetPrice',
        'Notes',
        'Project',
        'ProjectCode',
        'ProjectDescription',
        'ProjectedStock',
        'PurchaseOrderID',
        'Quantity',
        'QuantityInPurchaseUnits',
        'Rebill',
        'ReceiptDate',
        'ReceivedQuantity',
        'SalesOrder',
        'SalesOrderLine',
        'SalesOrderLineNumber',
        'SalesOrderNumber',
        'ShopOrderMaterialPlans',
        'ShopOrderRoutingStepPlans',
        'SupplierItemCode',
        'SupplierItemCopyRemarks',
        'Unit',
        'UnitDescription',
        'UnitPrice',
        'VATAmount',
        'VATCode',
        'VATDescription',
        'VATPercentage',
    ];

    protected $url = 'purchaseorder/PurchaseOrderLines';
}
