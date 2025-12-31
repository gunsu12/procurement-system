<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id',
        'name',
        'specification',
        'quantity',
        'estimated_price',
        'unit',
        'budget_info',
        'is_checked',
        'checked_at',
        'checked_by',
        'is_rejected',
        'rejection_note'
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'datetime',
        'is_rejected' => 'boolean',
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->estimated_price;
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
