<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Unit extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'code', 'division_id', 'company_id', 'approval_by'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approval_by');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function requests()
    {
        return $this->hasMany(ProcurementRequest::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
