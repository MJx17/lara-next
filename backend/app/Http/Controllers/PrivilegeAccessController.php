<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeAccessRequest;
use App\Models\PrivilegeAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivilegeAccessController extends Controller
{
    public function index(Request $request)
    {
        $query = PrivilegeAccessRequest::with(['user', 'logs'])->orderByDesc('created_at');

        // Filters - you can add more if needed
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('hostname')) {
            $query->where('hostname', $request->hostname);
        }
        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }
         if ($request->filled('requestor_username')) {
            $query->where('requestor_username', $request->requestor_username);
        }

        return $query->get([
            'id',
            'request_uuid',
            'type',
            'user_id',
            'reason',
            'status',
            'hostname',
            'ip_address',
            'requestor_username', // ✅ Add this
            'created_at',
            'updated_at',
        ]);

    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'reason' => 'required|string|max:255',
            'requestor_username'  => 'required|string',
            'host' => 'required|string',
            'ip' => 'required|ip',
            'timestamp' => 'required|date',
        ]);

        $userId = Auth::check() ? Auth::id() : null;
        $requestorUsername = $request->requestor_username;

        // Create the main access request
        $accessRequest = PrivilegeAccessRequest::create([
            'type' => $request->type,
            'user_id' => $userId,
            'reason' => $request->reason,
            'hostname' => $request->host,
            'ip_address' => $request->ip,
            'created_at' => $request->timestamp,
            'requestor_username' => $requestorUsername, // assuming you added this to the model + migration
        ]);

        // Create a log entry referencing this request
        PrivilegeAccessLog::create([
            'privilege_access_request_id' => $accessRequest->id,
            'request_uuid' => $accessRequest->request_uuid,
            'actor_id' => $userId,
            'action' => 'submitted',
            'type' => $request->type,
            'hostname' => $request->host,
            'ip_address' => $request->ip,
            'reason' => $request->reason,
            'status' => 'pending',
            'requestor_username' => $requestorUsername,
            'created_at' => $request->timestamp,
        ]);

        return response()->json([
            'message' => 'Request submitted',
            'request_uuid' => $accessRequest->request_uuid,
        ], 201);
    }

    public function getStatusByUuid($uuid)
    {
        $request = PrivilegeAccessRequest::with('logs')
            ->where('request_uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'status' => $request->status,
            'logs' => $request->logs,
            'updated_at' => $request->updated_at,
        ]);
    }

    public function approveByUuid(Request $request, $uuid)
    {
        return $this->processApproval($request, $uuid, 'approved');
    }

    public function declineByUuid(Request $request, $uuid)
    {
        return $this->processApproval($request, $uuid, 'declined');
    }

    private function processApproval(Request $request, $uuid, string $status)
    {
        $request->validate([
            'host' => 'required|string',
            'ip' => 'required|ip',
            'timestamp' => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();

        $accessRequest->update([
            'status' => $status,
            'updated_at' => $request->timestamp,
        ]);

        PrivilegeAccessLog::create([
            'privilege_access_request_id' => $accessRequest->id,
            'actor_id' => Auth::id(),
            'action' => $status,
            'type' => $accessRequest->type,
            'hostname' => $request->host,
            'ip_address' => $request->ip,
            'status' => $status,
            'reason' => $request->reason,
            'requestor_username' => $accessRequest->requestor_username,
            'created_at' => $request->timestamp,
        ]);

        return response()->json([
            'message' => ucfirst($status),
        ]);
    }

    public function latestForUser()
    {
        return PrivilegeAccessRequest::where('user_id', Auth::id())
            ->latest()
            ->first();
    }
}
