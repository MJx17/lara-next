<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeAccessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PrivilegeAccessTeamsController extends Controller
{
    protected $teamsWebhookUrl;

    public function __construct()
    {
        $this->teamsWebhookUrl = config('services.teams.webhook_url'); // Set in .env as TEAMS_WEBHOOK_URL
    }

    /**
     * Send an active privilege access request notification to Microsoft Teams.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToTeams(Request $request, $uuid)
    {
        $accessRequest = PrivilegeAccessRequest::active()
            ->where('request_uuid', $uuid)
            ->first();

        if (!$accessRequest) {
            return response()->json([
                'message' => 'No active request found or request expired.'
            ], 404);
        }

        $card = $this->buildAdaptiveCardPayload($accessRequest);

        $response = Http::post($this->teamsWebhookUrl, [
            'type' => 'message',
            'attachments' => [
                [
                    'contentType' => 'application/vnd.microsoft.card.adaptive',
                    'content' => $card,
                ]
            ],
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Notification sent to Teams']);
        }

        return response()->json([
            'message' => 'Failed to send Teams notification',
            'error' => $response->body(),
        ], 500);
    }

    /**
     * Build the Adaptive Card JSON payload for Teams.
     *
     * @param  \App\Models\PrivilegeAccessRequest  $accessRequest
     * @return array
     */
        protected function buildAdaptiveCardPayload($accessRequest)
        {
            $approveUrl = route('privilege.approve-teams', ['uuid' => $accessRequest->request_uuid]);
            $declineUrl = route('privilege.decline-teams', ['uuid' => $accessRequest->request_uuid]);

            $createdAt = $accessRequest->created_at;
            $isExpired = now()->diffInMinutes($createdAt) > 15;
            $isPending = $accessRequest->status === 'pending';

            $card = [
                '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                'type' => 'AdaptiveCard',
                'version' => '1.4',
                'body' => [
                    [
                        'type' => 'TextBlock',
                        'size' => 'Medium',
                        'weight' => 'Bolder',
                        'text' => 'Privilege Access Request',
                    ],
                    [
                        'type' => 'FactSet',
                        'facts' => [
                            ['title' => 'Requestor:', 'value' => $accessRequest->requestor_username],
                            ['title' => 'Type:', 'value' => $accessRequest->type],
                            ['title' => 'Host:', 'value' => $accessRequest->hostname],
                            ['title' => 'IP:', 'value' => $accessRequest->ip_address],
                            ['title' => 'Reason:', 'value' => $accessRequest->reason],
                            ['title' => 'Status:', 'value' => ucfirst($accessRequest->status)],
                            ['title' => 'Created At:', 'value' => $createdAt->toDateTimeString()],
                        ],
                    ],
                ],
            ];

            // Only add buttons if request is still pending and not expired
          if ($isPending && !$isExpired) {
                $card['actions'] = [
                    [
                        'type' => 'Action.OpenUrl',
                        'title' => 'Approve',
                        'url' => $approveUrl,
                    ],
                    [
                        'type' => 'Action.OpenUrl',
                        'title' => 'Decline',
                        'url' => $declineUrl,
                    ],
                ];
            } else {
                $card['body'][] = [
                    'type' => 'TextBlock',
                    'text' => $isExpired
                        ? '⏰ This request has expired.'
                        : '✅ This request has already been processed.',
                    'color' => 'Warning',
                    'wrap' => true,
                ];
            }

            return $card;
        }

}
