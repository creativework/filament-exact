<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;

class LeaveBuildUpRegistration extends Model
{
    use Findable;

    protected $fillable = [
        'ID',
        'Created',
        'Creator',
        'CreatorFullName',
        'Date',
        'Description',
        'Division',
        'Employee',
        'EmployeeFullName',
        'EmployeeHID',
        'Hours',
        'LeaveType',
        'LeaveTypeCode',
        'LeaveTypeDescription',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'Notes',
        'Status',
    ];

    protected $url = 'hrm/LeaveBuildUpRegistrations';
}