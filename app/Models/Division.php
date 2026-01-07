<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Division extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'code', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
