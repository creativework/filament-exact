<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class SalesOrder extends Model
{
    use Findable;
    use Storable;

    protected $primaryKey = 'OrderID';

    protected $saleOrderLines = [];

    protected $fillable = [
        'OrderID',
        'AmountDC',
        'AmountDiscount',
        'AmountDiscountExclVat',
        'AmountFC',
        'AmountFCExclVat',
        'ApprovalStatus',
        'ApprovalStatusDescription',
        'Approved',
        'Approver',
        'ApproverFullName',
        'Created',
        'Creator',
        'CreatorFullName',
        'Currency',
        'CustomField',
        'DeliverTo',
        'DeliverToContactPerson',
        'DeliverToContactPersonFullName',
        'DeliverToName',
        'DeliveryAddress',
        'DeliveryDate',
        'DeliveryStatus',
        'DeliveryStatusDescription',
        'Description',
        'Discount',
        'Division',
        'Document',
        'DocumentNumber',
        'DocumentSubject',
        'IncotermAddress',
        'IncotermCode',
        'IncotermVersion',
        'InvoiceStatus',
        'InvoiceStatusDescription',
        'InvoiceTo',
        'InvoiceToContactPerson',
        'InvoiceToContactPersonFullName',
        'InvoiceToName',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'OrderDate',
        'OrderedBy',
        'OrderedByContactPerson',
        'OrderedByContactPersonFullName',
        'OrderedByName',
        'OrderNumber',
        'PaymentCondition',
        'PaymentConditionDescription',
        'PaymentReference',
        'Remarks',
        'SalesChannel',
        'SalesChannelCode',
        'SalesChannelDescription',
        'SalesOrderLines',
        'SalesOrderOrderChargeLines',
        'Salesperson',
        'SalespersonFullName',
        'SelectionCode',
        'SelectionCodeCode',
        'SelectionCodeDescription',
        'ShippingMethod',
        'ShippingMethodDescription',
        'Status',
        'StatusDescription',
        'WarehouseCode',
        'WarehouseDescription',
        'WarehouseID',
        'YourRef',
    ];

    protected $url = 'salesorder/SalesOrders';

    /**
     * @param array $array
     */
    public function addItem(array $array)
    {
        if (! isset($this->attributes['SalesOrderLines']) || $this->attributes['SalesOrderLines'] == null) {
            $this->attributes['SalesOrderLines'] = [];
        }
        if (! isset($array['LineNumber'])) {
            $array['LineNumber'] = count($this->attributes['SalesOrderLines']) + 1;
        }
        $this->attributes['SalesOrderLines'][] = $array;
    }

    public function getSalesOrderLines()
    {
        if (array_key_exists('__deferred', $this->attributes['SalesOrderLines'])) {
            $this->attributes['SalesOrderLines'] = (new SalesOrderLine($this->connection()))->filter("OrderID eq guid'{$this->OrderID}'");
        }

        return $this->attributes['SalesOrderLines'];
    }

    public function getSalesOrderOrderChargeLines()
    {
        if (array_key_exists('__deferred', $this->attributes['SalesOrderOrderChargeLines'])) {
            $this->attributes['SalesOrderOrderChargeLines'] = (new SalesOrderOrderChargeLine($this->connection()))->filter("OrderID eq guid'{$this->OrderID}'");
        }

        return $this->attributes['SalesOrderOrderChargeLines'];
    }
}
