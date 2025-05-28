<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthCheckRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'health_check_records';

    protected $fillable = [
        'profile_id',
        'health_surveillance_id',
        'activity_id',
        'check_up_date',
        'expiration_date',
        'outcome',
        'notes',
        // 'status',
    ];

    protected $casts = [
        'check_up_date' => 'date',
        'expiration_date' => 'date',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function healthSurveillance(): BelongsTo
    {
        return $this->belongsTo(HealthSurveillance::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
