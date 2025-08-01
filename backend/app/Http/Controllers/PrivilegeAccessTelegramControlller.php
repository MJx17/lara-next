<?php

namespace App\Http\Controllers;

use App\Models\PrivilegeAccessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PrivilegeAccessTelegramController extends Controller
{
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function sendToTelegram(Request $request, $uuid)
    {
        $accessRequest = PrivilegeAccessRequest::active()
            ->where('request_uuid', $uuid)
            ->first();

        if (!$accessRequest) {
            return response()->json([
                'message' => 'No active request found or request expired.'
            ], 404);
        }

        $message = $this->buildTelegramMessage($accessRequest);

        $response = Http::get("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'Notification sent to Telegram']);
        }

        return response()->json([
            'message' => 'Failed to send Telegram notification',
            'error' => $response->body(),
        ], 500);
    }

    protected function buildTelegramMessage($accessRequest)
    {
        $approveUrl = route('privilege.approve-teams', ['uuid' => $accessRequest->request_uuid]);
        $declineUrl = route('privilege.decline-teams', ['uuid' => $accessRequest->request_uuid]);

        $createdAt = $accessRequest->created_at;
        $isExpired = now()->diffInMinutes($createdAt) > 15;
        $isPending = $accessRequest->status === 'pending';

        $message = "*🔐 Privilege Access Request*\n\n";
        $message .= "*Requestor:* `{$accessRequest->requestor_username}`\n";
        $message .= "*Type:* {$accessRequest->type}\n";
        $message .= "*Host:* {$accessRequest->hostname}\n";
        $message .= "*IP:* {$accessRequest->ip_address}\n";
        $message .= "*Reason:* {$accessRequest->reason}\n";
        $message .= "*Status:* " . ucfirst($accessRequest->status) . "\n";
        $message .= "*Created At:* {$createdAt->toDateTimeString()}\n\n";

        if ($isPending && !$isExpired) {
            $message .= "[✅ Approve]({$approveUrl}) | [❌ Decline]({$declineUrl})";
        } else {
            $message .= $isExpired
                ? "⏰ This request has *expired*."
                : "✅ This request has *already been processed*.";
        }

        return $message;
    }
}
