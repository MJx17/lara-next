<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PrivilegeAccessRequest extends Model
{
    protected $fillable = [
        'request_uuid',
        'requestor_username',
        'requestor_fullname',
        'system_name',
        'user_id',
        'reason',
        'status',
        'type',
        'hostname',
        'host_ip',
        'requestor_ip',
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

    /**
     * Determine if the request is expired (older than 15 minutes).
     */
    public function isExpired(): bool
    {
        return now()->diffInMinutes($this->created_at) > 15;
    }

    public function scopeActive($query)
{
    return $query->where('status', 'pending')
                 ->where('created_at', '>=', now()->subMinutes(15));
}

}
