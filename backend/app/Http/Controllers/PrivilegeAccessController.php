<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeAccessRequest;
use App\Models\PrivilegeAccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PrivilegeAccessTeamsController;

class PrivilegeAccessController extends Controller
{
    public function index(Request $request)
    {
        $query = PrivilegeAccessRequest::with(['user', 'logs'])->orderByDesc('created_at');

        $filters = [
            'type',
            'user_id',
            'hostname',
            'ip_address',
            'requestor_username',
            'status'
        ];

        foreach ($filters as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->$field);
            }
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
            'requestor_username',
            'created_at',
            'updated_at',
        ]);
    }

    // GET /api/requests/active
    public function active()
    {
        return PrivilegeAccessRequest::active()
            ->with(['user', 'logs'])
            ->orderByDesc('created_at')
            ->get([
                'id',
                'request_uuid',
                'type',
                'user_id',
                'reason',
                'status',
                'hostname',
                'ip_address',
                'requestor_username',
                'created_at',
                'updated_at',
            ]);
    }



    // Old -v without teams 
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'type'               => 'required|string',
    //         'reason'             => 'required|string|max:255',
    //         'requestor_username' => 'required|string',
    //         'host'               => 'required|string',
    //         'ip'                 => 'required|ip',
    //         'timestamp'          => 'required|date',
    //     ]);

    //     $userId = Auth::id();

    //     $accessRequest = PrivilegeAccessRequest::create([
    //         'type'               => $validated['type'],
    //         'user_id'            => $userId,
    //         'reason'             => $validated['reason'],
    //         'hostname'           => $validated['host'],
    //         'ip_address'         => $validated['ip'],
    //         'created_at'         => $validated['timestamp'],
    //         'requestor_username' => $validated['requestor_username'],
    //     ]);

    //     PrivilegeAccessLog::create([
    //         'privilege_access_request_id' => $accessRequest->id,
    //         'request_uuid'        => $accessRequest->request_uuid,
    //         'actor_id'            => $userId,
    //         'action'              => 'submitted',
    //         'type'                => $validated['type'],
    //         'hostname'            => $validated['host'],
    //         'ip_address'          => $validated['ip'],
    //         'reason'              => $validated['reason'],
    //         'status'              => 'pending',
    //         'requestor_username'  => $validated['requestor_username'],
    //         'created_at'          => $validated['timestamp'],
    //     ]);

    //     return response()->json([
    //         'message' => 'Request submitted',
    //         'request_uuid' => $accessRequest->request_uuid,
    //     ], 201);
    // }




    public function store(Request $request)
        {
            $validated = $request->validate([
                'type'               => 'required|string',
                'reason'             => 'required|string|max:255',
                'requestor_username' => 'required|string',
                'host'               => 'required|string',
                'ip'                 => 'required|ip',
                'timestamp'          => 'required|date',
            ]);

            $userId = Auth::id();

            $accessRequest = PrivilegeAccessRequest::create([
                'type'               => $validated['type'],
                'user_id'            => $userId,
                'reason'             => $validated['reason'],
                'hostname'           => $validated['host'],
                'ip_address'         => $validated['ip'],
                'created_at'         => $validated['timestamp'],
                'requestor_username' => $validated['requestor_username'],
            ]);

            PrivilegeAccessLog::create([
                'privilege_access_request_id' => $accessRequest->id,
                'request_uuid'                => $accessRequest->request_uuid,
                'actor_id'                    => $userId,
                'action'                      => 'submitted',
                'type'                        => $validated['type'],
                'hostname'                    => $validated['host'],
                'ip_address'                  => $validated['ip'],
                'reason'                      => $validated['reason'],
                'status'                      => 'pending',
                'requestor_username'          => $validated['requestor_username'],
                'created_at'                  => $validated['timestamp'],
            ]);

            // ✅ Properly call the Teams controller using Laravel's service container
            app(PrivilegeAccessTeamsController::class)->sendToLogicApp($accessRequest);

            return response()->json([
                'message' => 'Request submitted and notification sent',
                'request_uuid' => $accessRequest->request_uuid,
            ], 201);
    }





    public function getStatusByUuid($uuid)
    {
        $request = PrivilegeAccessRequest::with('logs')
            ->where('request_uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'status'     => $request->status,
            'logs'       => $request->logs,
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

    //Old v -without teams

//    private function processApproval(Request $request, $uuid, string $status)
// {
//     $validated = $request->validate([
//         'host'      => 'required|string',
//         'ip'        => 'required|ip',
//         'timestamp' => 'required|date',
//         'reason'    => 'nullable|string|max:255',
//     ]);

//     $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();

//     if ($accessRequest->status !== 'pending') {
//         return response()->json([
//             'message' => "Request has already been {$accessRequest->status}.",
//         ], 400);
//     }

//     // ⛔ Check if the request is expired
//     if ($accessRequest->isExpired()) {
//         return response()->json([
//             'message' => 'Request has expired.',
//         ], 403);
//     }

//     $accessRequest->update([
//         'status'     => $status,
//         'updated_at' => $validated['timestamp'],
//     ]);

//     try {
//         PrivilegeAccessLog::create([
//             'privilege_access_request_id' => $accessRequest->id,
//             'request_uuid'        => $accessRequest->request_uuid,
//             'actor_id'            => Auth::id(),
//             'action'              => $status,
//             'type'                => $accessRequest->type,
//             'hostname'            => $validated['host'],
//             'ip_address'          => $validated['ip'],
//             'status'              => $status,
//             'reason'              => $validated['reason'],
//             'requestor_username'  => $accessRequest->requestor_username,
//             'created_at'          => $validated['timestamp'],
//         ]);
//     } catch (\Throwable $e) {
//         return response()->json([
//             'message' => 'Log creation failed',
//             'error'   => $e->getMessage(),
//         ], 500);
//     }

//     return response()->json([
//         'message' => ucfirst($status),
//     ]);
// }

    // Teams link approval (no Auth, GET)


        // public function approveByUuidTeams($uuid)
        // {
        //     $fakeRequest = new Request([
        //         'host'      => gethostname() ?? 'unknown',
        //         'ip'        => request()->ip() ?? 'unknown',
        //         'timestamp' => now()->toDateTimeString(),
        //         'reason'    => 'Approved via Teams link',
        //     ]);

        //     return $this->processApproval($fakeRequest, $uuid, 'approved', 2);
        // }

        // public function declineByUuidTeams($uuid)
        // {
        //     $fakeRequest = new Request([
        //         'host'      => gethostname() ?? 'unknown',
        //         'ip'        => request()->ip() ?? 'unknown',
        //         'timestamp' => now()->toDateTimeString(),
        //         'reason'    => 'Declined via Teams link',
        //     ]);

        //     return $this->processApproval($fakeRequest, $uuid, 'declined', 2);
        // }

    //Latest -v 
    /**
     * Core approval logic, with optional actorId override
     */
    // private function processApproval(Request $request, $uuid, string $status, $actorId = null)
    // {
    //     // Validate input - require host, ip, timestamp, reason optional
    //     $validated = $request->validate([
    //         'host'      => 'required|string',
    //         'ip'        => 'required|ip',
    //         'timestamp' => 'required|date',
    //         'reason'    => 'nullable|string|max:255',
    //     ]);

    //     $accessRequest = PrivilegeAccessRequest::where('request_uuid', $uuid)->firstOrFail();

    //     if ($accessRequest->status !== 'pending') {
    //         return response()->json([
    //             'message' => "Request has already been {$accessRequest->status}.",
    //         ], 400);
    //     }

    //     // Check if request expired (your method)
    //     if ($accessRequest->isExpired()) {
    //         return response()->json([
    //             'message' => 'Request has expired.',
    //         ], 403);
    //     }

    //     $accessRequest->update([
    //         'status'     => $status,
    //         'updated_at' => $validated['timestamp'],
    //     ]);

    //     try {
    //         PrivilegeAccessLog::create([
    //             'privilege_access_request_id' => $accessRequest->id,
    //             'request_uuid'                => $accessRequest->request_uuid,
    //             // Use actorId if provided, else fallback to Auth
    //             'actor_id'                   => $actorId ?? Auth::id(),
    //             'action'                     => $status,
    //             'type'                       => $accessRequest->type,
    //             'hostname'                   => $validated['host'],
    //             'ip_address'                 => $validated['ip'],
    //             'status'                     => $status,
    //             'reason'                     => $validated['reason'],
    //             'requestor_username'         => $accessRequest->requestor_username,
    //             'created_at'                 => $validated['timestamp'],
    //         ]);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'message' => 'Log creation failed',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }

    //     return response()->json([
    //         'message' => ucfirst($status),
    //     ]);
    // }


    public function approveByUuidTeams(Request $request, $uuid)
    {
        $metadata = [
            'host'          => gethostname() ?? 'unknown',
            'ip'            => $request->ip() ?? 'unknown',
            'timestamp'     => $request->input('clicked_at') ?? now()->toDateTimeString(),
            'reason'        => 'Approved via Teams',
            'teams_payload' => $request->all(), // full Teams data
        ];

        return $this->processApproval(new Request($metadata), $uuid, 'approved');
    }

    public function declineByUuidTeams(Request $request, $uuid)
    {
        $metadata = [
            'host'          => gethostname() ?? 'unknown',
            'ip'            => $request->ip() ?? 'unknown',
            'timestamp'     => $request->input('clicked_at') ?? now()->toDateTimeString(),
            'reason'        => 'Declined via Teams',
            'teams_payload' => $request->all(),
        ];

        return $this->processApproval(new Request($metadata), $uuid, 'declined');
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
            return response()->json(['message' => 'Request expired.'], 403);
        }

        $validated = $request->validate([
            'host'          => 'nullable|string',
            'ip'            => 'nullable|ip',
            'timestamp'     => 'nullable|date',
            'reason'        => 'nullable|string|max:255',
            'teams_payload' => 'nullable|array',
        ]);

        // Update main request
        $accessRequest->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        // Log the action
        PrivilegeAccessLog::create([
            'privilege_access_request_id' => $accessRequest->id,
            'request_uuid'                => $accessRequest->request_uuid,
            'actor_id'                    => Auth::id(), // null if unauthenticated (e.g., from Teams)
            'action'                      => $status,
            'type'                        => $accessRequest->type,
            'hostname'                    => $validated['host'] ?? null,
            'ip_address'                  => $validated['ip'] ?? $request->ip(),
            'status'                      => $status,
            'reason'                      => $validated['reason'] ?? null,
            'requestor_username'          => $accessRequest->requestor_username,
            'teams_payload'               => $validated['teams_payload'] ?? null,
            'created_at'                  => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['message' => ucfirst($status) . ' successfully']);
    }




    public function latestForUser()
    {
        return PrivilegeAccessRequest::where('user_id', Auth::id())
            ->latest()
            ->first();
    }
}
