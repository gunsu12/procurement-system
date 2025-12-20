<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementDocument extends Model
{
    protected $fillable = [
        'procurement_request_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size'
    ];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
