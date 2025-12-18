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
        'budget_info'
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->estimated_price;
    }
}
