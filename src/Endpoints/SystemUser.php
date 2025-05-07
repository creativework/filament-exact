<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class SystemUser extends Model
{
    use Findable;
    use Storable;

    protected $primaryKey = 'UserID';

    protected $fillable = [
        'UserID',
        'AuthenticationType',
        'BirthDate',
        'Created',
        'Creator',
        'CreatorFullName',
        'Customer',
        'CustomerName',
        'Email',
        'EndDate',
        'FirstName',
        'FullName',
        'Gender',
        'HasRegisteredForTwoStepVerification',
        'HasTwoStepVerification',
        'Initials',
        'IsAnonymised',
        'Language',
        'LastLogin',
        'LastName',
        'MiddleName',
        'Mobile',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'Nationality',
        'Notes',
        'Phone',
        'PhoneExtension',
        'ProfileCode',
        'StartDate',
        'Title',
        'UserDivisionList',
        'UserLanguage',
        'UserName',
        'UserTypeCode',
    ];

    protected $url = 'system/Users';
}
