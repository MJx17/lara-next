<?php

// namespace App\Http\Controllers;

// use App\Models\PrivilegeAccessRequest;
// use App\Models\PrivilegeAccessLog;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use App\Events\PrivilegeAccessStatusUpdated;

// class PrivilegeAccessController extends Controller
// {
//     public function index()
//     {
//         return PrivilegeAccessRequest::with(['user', 'logs'])->orderByDesc('created_at')->get();
//     }

//     public function store(Request $request)
//     {
//         $request->validate([
//             'reason'    => 'nullable|string|max:255',
//             'user'      => 'nullable|string',
//             'host'      => 'nullable|string',
//             'ip'        => 'nullable|ip',
//             'timestamp' => 'nullable|date',
//         ]);

//         $userId = Auth::check() ? Auth::id() : null;
//         $hostname = $request->host ?? gethostname();
//         $ip = $request->ip ?? $request->ip();

//         $accessRequest = PrivilegeAccessRequest::create([
//             'user_id'    => $userId,
//             'reason'     => $request->reason,
//             'hostname'   => $hostname,
//             'ip_address' => $ip,
//             'created_at' => $request->timestamp ?? now(),
//         ]);

//         PrivilegeAccessLog::create([
//             'privilege_access_request_id' => $accessRequest->id,
//             'actor_id'   => $userId,
//             'action'     => 'submitted',
//             'hostname'   => $hostname,
//             'ip_address' => $ip,
//             'reason'     => $request->reason,
//             'status'     => 'pending',
//             'created_at' => $request->timestamp ?? now(),
//         ]);

//         return response()->json([
//             'message' => 'Request submitted',
//             'request_uuid' => $accessRequest->request_uuid,
//         ], 201);
//     }

//     public function getStatusByUuid($uuid)
//     {
//         $request = PrivilegeAccessRequest::with('logs')
//             ->where('request_uuid', $uuid)
//             ->firstOrFail();

//         return response()->json([
//             'status' => $request->status,
//             'logs' => $request->logs,
//             'updated_at' => $request->updated_at,
//         ]);
//     }

//     // 🟢 UUID-based Approve
//     public function approveByUuid(Request $request, $uuid)
//     {
//         $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();
//         return $this->processApproval($request, $accessRequest, 'approved');
//     }

//     // 🟡 UUID-based Decline
//     public function declineByUuid(Request $request, $uuid)
//     {
//         $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();
//         return $this->processApproval($request, $accessRequest, 'declined');
//     }

//     // 🧠 Shared logic for both approve/decline
//     private function processApproval(Request $request, PrivilegeAccessRequest $accessRequest, string $status)
//     {
//         $accessRequest->update(['status' => $status]);

//         PrivilegeAccessLog::create([
//             'privilege_access_request_id' => $accessRequest->id,
//             'actor_id'   => Auth::id(),
//             'action'     => $status,
//             'hostname'   => gethostname(),
//             'ip_address' => $request->ip(),
//             'status'     => $status,
//         ]);

//         event(new PrivilegeAccessStatusUpdated($accessRequest));

//         return response()->json(['message' => ucfirst($status)]);
//     }

//     public function latestForUser()
//     {
//         return PrivilegeAccessRequest::where('user_id', Auth::id())->latest()->first();
//     }
// }




namespace App\Http\Controllers;

use App\Models\PrivilegeAccessRequest;
use App\Models\PrivilegeAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivilegeAccessController extends Controller
{
    public function index()
    {
        return PrivilegeAccessRequest::with(['user', 'logs'])->orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason'    => 'nullable|string|max:255',
            'user'      => 'nullable|string',
            'host'      => 'nullable|string',
            'ip'        => 'nullable|ip',
            'timestamp' => 'nullable|date',
        ]);

        $userId = Auth::check() ? Auth::id() : null;
        $hostname = $request->host ?? gethostname();
        $ip = $request->ip ?? $request->ip();

        $accessRequest = PrivilegeAccessRequest::create([
            'user_id'    => $userId,
            'reason'     => $request->reason,
            'hostname'   => $hostname,
            'ip_address' => $ip,
            'created_at' => $request->timestamp ?? now(),
        ]);

        PrivilegeAccessLog::create([
            'privilege_access_request_id' => $accessRequest->id,
            'actor_id'   => $userId,
            'action'     => 'submitted',
            'hostname'   => $hostname,
            'ip_address' => $ip,
            'reason'     => $request->reason,
            'status'     => 'pending',
            'created_at' => $request->timestamp ?? now(),
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
        $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();
        return $this->processApproval($request, $accessRequest, 'approved');
    }

    public function declineByUuid(Request $request, $uuid)
    {
        $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();
        return $this->processApproval($request, $accessRequest, 'declined');
    }

    private function processApproval(Request $request, PrivilegeAccessRequest $accessRequest, string $status)
    {
        $accessRequest->update(['status' => $status]);

        PrivilegeAccessLog::create([
            'privilege_access_request_id' => $accessRequest->id,
            'actor_id'   => Auth::id(),
            'action'     => $status,
            'hostname'   => gethostname(),
            'ip_address' => $request->ip(),
            'status'     => $status,
        ]);

        return response()->json(['message' => ucfirst($status)]);
    }

    public function latestForUser()
    {
        return PrivilegeAccessRequest::where('user_id', Auth::id())->latest()->first();
    }
}
