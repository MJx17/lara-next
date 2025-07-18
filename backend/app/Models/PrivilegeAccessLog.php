<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivilegeAccessLog extends Model
{
    protected $fillable = [
        'privilege_access_request_id',
        'request_uuid',
        'actor_id',
        'action',
        'type', // e.g. ssh, sftp, rdp
        'hostname',
        'ip_address',
        'reason',
        'status',
        'requestor_username',  // <-- added here
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
