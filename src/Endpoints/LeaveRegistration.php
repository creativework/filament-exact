<?php

namespace CreativeWork\FilamentExact\Endpoints;

use CreativeWork\FilamentExact\Traits\Findable;

class LeaveRegistration extends Model
{
    use Findable;

    protected $fillable = [
        'ID',
        'Created',
        'Creator',
        'CreatorFullName',
        'Description',
        'Division',
        'Employee',
        'EmployeeFullName',
        'EmployeeHID',
        'EndDate',
        'EndTime',
        'Hours',
        'HoursFirstDay',
        'HoursLastDay',
        'LeaveType',
        'LeaveTypeCode',
        'LeaveTypeDescription',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'Notes',
        'StartDate',
        'StartTime',
        'Status',
    ];

    protected $url = 'hrm/LeaveRegistrations';
}
