<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class PurchaseOrder extends Model
{
    use Findable;
    use Storable;

    protected $primaryKey = 'PurchaseOrderID';

    protected $purchaseOrderLines = [];

    protected $fillable = [
        'PurchaseOrderID',
        'AmountDC',
        'AmountFC',
        'ApprovalStatus',
        'ApprovalStatusDescription',
        'Approved',
        'Approver',
        'ApproverFullName',
        'Created',
        'Creator',
        'CreatorFullName',
        'Currency',
        'DeliveryAccount',
        'DeliveryAccountCode',
        'DeliveryAccountName',
        'DeliveryAddress',
        'DeliveryContact',
        'DeliveryContactPersonFullName',
        'Description',
        'Division',
        'Document',
        'DocumentSubject',
        'DropShipment',
        'ExchangeRate',
        'IncotermAddress',
        'IncotermCode',
        'IncotermVersion',
        'InvoiceStatus',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'OrderDate',
        'OrderNumber',
        'OrderStatus',
        'PaymentCondition',
        'PaymentConditionDescription',
        'PurchaseAgent',
        'PurchaseAgentFullName',
        'PurchaseOrderLineCount',
        'PurchaseOrderLines',
        'ReceiptDate',
        'ReceiptStatus',
        'Remarks',
        'SalesOrder',
        'SalesOrderNumber',
        'SelectionCode',
        'SelectionCodeCode',
        'SelectionCodeDescription',
        'ShippingMethod',
        'ShippingMethodCode',
        'ShippingMethodDescription',
        'Source',
        'Supplier',
        'SupplierCode',
        'SupplierContact',
        'SupplierContactPersonFullName',
        'SupplierName',
        'VATAmount',
        'Warehouse',
        'WarehouseCode',
        'WarehouseDescription',
        'YourRef',
    ];

    protected $url = 'purchaseorder/PurchaseOrders';

    public function addItem(array $array)
    {
        if (! isset($this->attributes['PurchaseOrderLines']) || $this->attributes['PurchaseOrderLines'] == null) {
            $this->attributes['PurchaseOrderLines'] = [];
        }
        if (! isset($array['LineNumber'])) {
            $array['LineNumber'] = count($this->attributes['PurchaseOrderLines']) + 1;
        }
        $this->attributes['PurchaseOrderLines'][] = $array;
    }

    public function getPurchaseOrderLines($select = '')
    {
        if (array_key_exists('__deferred', $this->attributes['PurchaseOrderLines'])) {
            $this->attributes['PurchaseOrderLines'] = (new PurchaseOrderLine($this->connection()))->filter("PurchaseOrderID eq guid'{$this->PurchaseOrderID}'", '', $select);
        }

        return $this->attributes['PurchaseOrderLines'];
    }
}
