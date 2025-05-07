<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;
use CreativeWork\FilamentExact\Traits\Storable;

class User extends Model
{
    use Findable;
    use Storable;

    protected $primaryKey = 'UserID';

    protected $fillable = [
        'UserID',
        'BirthDate',
        'BirthName',
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
        'StartDivision',
        'Title',
        'UserName',
        'UserRoles',
        'UserRolesPerDivision',
        'UserTypesList',
    ];

    protected $url = 'users/Users';
}
