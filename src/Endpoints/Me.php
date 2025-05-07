<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;

class Me extends Model
{
    use Findable;

    protected $primaryKey = 'UserID';

    protected $fillable = [
        'UserID',
        'AccountingDivision',
        'CurrentDivision',
        'CustomerCode',
        'DivisionCustomer',
        'DivisionCustomerCode',
        'DivisionCustomerName',
        'DivisionCustomerSiretNumber',
        'DivisionCustomerVatNumber',
        'DossierDivision',
        'Email',
        'EmployeeID',
        'FirstName',
        'FullName',
        'Gender',
        'Initials',
        'IsClientUser',
        'IsEmployeeSelfServiceUser',
        'IsMyFirmLiteUser',
        'IsMyFirmPortalUser',
        'IsOEIMigrationMandatory',
        'IsStarterUser',
        'Language',
        'LanguageCode',
        'LastName',
        'Legislation',
        'MiddleName',
        'Mobile',
        'Nationality',
        'PackageCode',
        'Phone',
        'PhoneExtension',
        'PictureUrl',
        'ServerTime',
        'ServerUtcOffset',
        'ThumbnailPicture',
        'ThumbnailPictureFormat',
        'Title',
        'UserName',
    ];

    protected $url = 'current/Me';

    public function find()
    {
        $result = $this->connection()->get($this->url);

        return new self($this->connection(), $result);
    }

    public function findWithSelect($select = '')
    {
        $result = $this->connection()->get($this->url, [
            '$select' => $select,
        ]);

        return new self($this->connection(), $result);
    }
}
