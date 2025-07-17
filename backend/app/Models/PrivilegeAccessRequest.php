<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class PrivilegeAccessRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'status',
        'hostname',
        'ip_address',
        'request_uuid',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PrivilegeAccessLog::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->request_uuid = (string) Str::uuid();
        });
    }
}


