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
        $query = PrivilegeAccessRequest::with('user')->orderByDesc('created_at');

        $filters = [
            'type', 'user_id', 'hostname', 'requestor_ip', 'host_ip',
            'requestor_username', 'requestor_fullname', 'system_name', 'status',
        ];

        foreach ($filters as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
        }

        return $query->get([
            'id', 'request_uuid', 'type', 'user_id', 'reason', 'status',
            'hostname', 'requestor_ip', 'host_ip',
            'requestor_username', 'requestor_fullname', 'system_name',
            'created_at', 'updated_at',
        ]);
    }

    public function fetchAllLogs(Request $request)
    {
        $query = PrivilegeAccessLog::with('request')->orderByDesc('created_at');

        $filters = [
            'privilege_access_request_id', 'request_uuid', 'actor_id', 'status', 'created_at',
        ];

        foreach ($filters as $field) {
            if ($request->filled($field)) {
                if ($field === 'request_uuid') {
                    $query->where('request_uuid', $request->request_uuid);
                } else {
                    $query->where($field, $request->$field);
                }
            }
        }

        return $query->get([
            'id', 'privilege_access_request_id', 'status', 'actor_id',
            'action', 'created_at',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type'               => 'required|string',
                'reason'             => 'required|string|max:255',
                'requestor_username' => 'required|string',
                'host'               => 'required|string',
                'requestor_ip'       => 'required|ip',
                'host_ip'            => 'required|ip',
                'timestamp'          => 'required|date',
                'requestor_fullname' => 'required|string|max:255',
                'system_name'        => 'required|string|max:255',
            ]);

            $accessRequest = PrivilegeAccessRequest::create([
                'type'               => $validated['type'],
                'user_id'            => Auth::id(),
                'reason'             => $validated['reason'],
                'hostname'           => $validated['host'],
                'ip_address'         => $validated['host_ip'],
                'requestor_ip'       => $validated['requestor_ip'],
                'host_ip'            => $validated['host_ip'],
                'created_at'         => $validated['timestamp'],
                'requestor_username' => $validated['requestor_username'],
                'requestor_fullname' => $validated['requestor_fullname'],
                'system_name'        => $validated['system_name'],
            ]);

            PrivilegeAccessLog::create([
                'privilege_access_request_id' => $accessRequest->id,
                'request_uuid'                => $accessRequest->request_uuid,
                'action'                      => 'submitted',
                'type'                        => $validated['type'],
                'hostname'                    => $validated['host'],
                'host_ip'                     => $validated['host_ip'],
                'ip_address'                  => $validated['host_ip'],
                'requestor_ip'                => $validated['requestor_ip'],
                'reason'                      => $validated['reason'],
                'requestor_username'          => $validated['requestor_username'],
                'requestor_fullname'          => $validated['requestor_fullname'],
                'system_name'                 => $validated['system_name'],
                'created_at'                  => $validated['timestamp'],
                'status'                      => 'pending',
                'actor_id'                    => null,
            ]);

            return response()->json([
                'message'       => 'Request submitted and notification sent',
                'request_uuid'  => $accessRequest->request_uuid,
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('PrivilegeAccessRequest Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error'   => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStatusByUuid($uuid)
    {
        $request = PrivilegeAccessRequest::with('logs')
            ->where('request_uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'logs'       => $request->logs,
            'updated_at' => $request->updated_at,
        ]);
    }

    public function active()
    {
        $requests = PrivilegeAccessRequest::where('status', 'pending')
            ->where(function ($q) {
                $q->where('created_at', '>=', now()->subMinutes(5))
                  ->orWhereNull('created_at');
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($requests);
    }

    private function processApproval(Request $request, $uuid, string $status)
    {
        $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();

        if ($accessRequest->status !== 'pending') {
            return response()->json([
                'message' => "Request already {$accessRequest->status}.",
            ], 400);
        }

        if ($accessRequest->isExpired()) {
            $accessRequest->update([
                'status'     => 'expired',
                'updated_at' => now(),
            ]);

            PrivilegeAccessLog::where('request_uuid', $uuid)
                ->where('action', 'submitted')
                ->where('status', 'pending')
                ->first()?->update([
                    'status'     => 'expired',
                    'updated_at' => now(),
                ]);

            return response()->json(['message' => 'Request expired.'], 410);
        }

        $accessRequest->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        PrivilegeAccessLog::where('request_uuid', $uuid)
            ->where('action', 'submitted')
            ->where('status', 'pending')
            ->first()?->update([
                'actor_id'   => auth()->id(),
                'action'     => $status,
                'status'     => $status,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => ucfirst($status) . ' successfully.'
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
}
