<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeAccessLog extends Model
{
    protected $fillable = [
        'privilege_access_request_id',
        'actor_id',
        'action',
        'hostname',
        'ip_address',
        'reason',
        'status',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(PrivilegeAccessRequest::class, 'privilege_access_request_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
