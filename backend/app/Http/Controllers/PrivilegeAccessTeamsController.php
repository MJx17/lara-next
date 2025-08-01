<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeAccessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PrivilegeAccessTeamsController extends Controller
{
    public function sendToLogicApp(PrivilegeAccessRequest $accessRequest)
    {
        $payload = [
            'type'               => $accessRequest->type,
            'reason'             => $accessRequest->reason,
            'requestor_username' => $accessRequest->requestor_username,
            'host'               => $accessRequest->hostname,
            'ip'                 => $accessRequest->ip_address,
            'timestamp'          => $accessRequest->created_at->toIso8601String(),
            'request_uuid'       => $accessRequest->request_uuid,
        ];

        $logicAppUrl = config('services.teams.logic_app_url');

        $response = Http::post($logicAppUrl, $payload);

        if ($response->successful()) {
            return response()->json(['message' => 'Payload sent to Logic App']);
        }

        return response()->json([
            'message' => 'Failed to send payload to Logic App',
            'error'   => $response->body(),
        ], 500);
    }
}
