<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PrivilegeAccessRequest extends Model
{
    protected $fillable = [
        'requestor_username',  // added requestor username
        'user_id',             // grantor/admin user
        'reason',
        'status',
        'type',
        'hostname',
        'ip_address',
        'request_uuid',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->request_uuid)) {
                $model->request_uuid = (string) Str::uuid();
            }
        });
    }

    // Grantor/admin user who approves or handles the request
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PrivilegeAccessLog::class);
    }
}
