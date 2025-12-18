<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'unit_id',
        'status',
        'manager_nominal',
        'document_path'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'PRC/' . date('Ymd') . '/';
            $last = self::where('code', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = 1;
            if ($last) {
                $parts = explode('/', $last->code);
                $nextNum = (int)end($parts) + 1;
            }
            $model->code = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }

    public function logs()
    {
        return $this->hasMany(RequestLog::class)->latest();
    }
}
