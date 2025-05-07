<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class SalesInvoice extends Model
{
    use Findable;
    use Storable;

    protected $primaryKey = 'InvoiceID';

    protected $fillable = [
        'InvoiceID',
        'AmountDC',
        'AmountDiscount',
        'AmountDiscountExclVat',
        'AmountFC',
        'AmountFCExclVat',
        'Created',
        'Creator',
        'CreatorFullName',
        'Currency',
        'DeliverTo',
        'DeliverToAddress',
        'DeliverToContactPerson',
        'DeliverToContactPersonFullName',
        'DeliverToName',
        'Description',
        'Discount',
        'DiscountType',
        'Division',
        'Document',
        'DocumentNumber',
        'DocumentSubject',
        'DueDate',
        'ExtraDutyAmountFC',
        'GAccountAmountFC',
        'IncotermAddress',
        'IncotermCode',
        'IncotermVersion',
        'InvoiceDate',
        'InvoiceNumber',
        'InvoiceTo',
        'InvoiceToContactPerson',
        'InvoiceToContactPersonFullName',
        'InvoiceToName',
        'IsExtraDuty',
        'Journal',
        'JournalDescription',
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
        'SalesInvoiceLines',
        'SalesInvoiceOrderChargeLines',
        'Salesperson',
        'SalespersonFullName',
        'SelectionCode',
        'SelectionCodeCode',
        'SelectionCodeDescription',
        'ShippingMethod',
        'ShippingMethodCode',
        'ShippingMethodDescription',
        'StarterSalesInvoiceStatus',
        'StarterSalesInvoiceStatusDescription',
        'Status',
        'StatusDescription',
        'Type',
        'TypeDescription',
        'VATAmountDC',
        'VATAmountFC',
        'Warehouse',
        'WithholdingTaxAmountFC',
        'WithholdingTaxBaseAmount',
        'WithholdingTaxPercentage',
        'YourRef',
    ];

    protected $url = 'salesinvoice/SalesInvoices';

    /**
     * Updates the SalesInvoiceLines collection on a SalesInvoice if it's been detected as a deferred collection.
     * Fetches results and stores them on this object.
     *
     * @return mixed
     */
    public function getSalesInvoiceLines()
    {
        if (array_key_exists('__deferred', $this->attributes['SalesInvoiceLines'])) {
            $this->attributes['SalesInvoiceLines'] = (new SalesInvoiceLine($this->connection()))->filter("InvoiceID eq guid'{$this->InvoiceID}'");
        }

        return $this->attributes['SalesInvoiceLines'];
    }
}
