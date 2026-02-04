<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProcurementRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'user_id',
        'unit_id',
        'status',
        'notes',
        'request_type',
        'is_medical',
        'is_cito',
        'cito_reason',
        'document_path',
        'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'hashid';
    }

    /**
     * Get the hashid attribute.
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Resolve route binding for hashid.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);

        if (empty($decoded)) {
            abort(404);
        }

        return $this->where('id', $decoded[0])->firstOrFail();
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $prefix = 'PRC/' . date('Ymd') . '/';
            $last = self::where('code', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
            $nextNum = 1;
            if ($last) {
                $parts = explode('/', $last->code);
                $nextNum = (int) end($parts) + 1;
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

    public function documents()
    {
        return $this->hasMany(ProcurementDocument::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->estimated_price;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }

    /**
     * Get the next status for approval workflow.
     * Centralized logic used by both Controller and Policy.
     */
    public static function getNextStatus($currentStatus, $role, $requestType, $totalAmount)
    {
        $fullChain = [
            'submitted' => ['manager' => 'approved_by_manager'],
            'approved_by_manager' => ['budgeting' => 'approved_by_budgeting'],
            'approved_by_budgeting' => ['director_company' => 'approved_by_dir_company'],
            'approved_by_dir_company' => ['finance_manager_holding' => 'approved_by_fin_mgr_holding'],
            'approved_by_fin_mgr_holding' => ['finance_director_holding' => 'approved_by_fin_dir_holding'],
            'approved_by_fin_dir_holding' => ['general_director_holding' => 'approved_by_gen_dir_holding'],
            'approved_by_gen_dir_holding' => ['purchasing' => 'processing'],
            'processing' => ['purchasing' => 'completed'],
        ];

        $shortChain = [
            'submitted' => ['manager' => 'approved_by_manager'],
            'approved_by_manager' => ['budgeting' => 'approved_by_budgeting'],
            'approved_by_budgeting' => ['purchasing' => 'processing'],
            'processing' => ['purchasing' => 'completed'],
        ];

        // Logic Re-defined (03 Feb 2026) based on Controller logic:
        // Non-Asset > 1,000,000 -> Short Chain
        // All others -> Full Chain

        $map = $fullChain; // Default to full

        if ($requestType === 'nonaset' && $totalAmount > 1000000) {
            $map = $shortChain;
        }

        return $map[$currentStatus][$role] ?? null;
    }
}
