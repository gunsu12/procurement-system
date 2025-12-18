<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_request_id',
        'user_id',
        'action',
        'note',
        'status_before',
        'status_after'
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
