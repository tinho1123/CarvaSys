<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for client.
     * GET /api/client/notifications
     */
    public function index(Request $request)
    {
        $clientUser = auth('sanctum')->user();

        $query = Notification::where('client_user_id', $clientUser->id);

        // Filter by read status
        if ($request->has('read')) {
            $read = $request->get('read');
            if ($read === 'true' || $read === '1') {
                $query->read();
            } elseif ($read === 'false' || $read === '0') {
                $query->unread();
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        $notifications = $query->recent()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get a single notification.
     * GET /api/client/notifications/{notification}
     */
    public function show(Notification $notification)
    {
        $clientUser = auth('sanctum')->user();

        if ($notification->client_user_id !== $clientUser->id) {
            abort(403, 'Unauthorized access to this notification');
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Mark notification as read.
     * POST /api/client/notifications/{notification}/read
     */
    public function markAsRead(Notification $notification)
    {
        $clientUser = auth('sanctum')->user();

        if ($notification->client_user_id !== $clientUser->id) {
            abort(403, 'Unauthorized access to this notification');
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read.
     * POST /api/client/notifications/mark-all-read
     */
    public function markAllAsRead()
    {
        $clientUser = auth('sanctum')->user();

        Notification::where('client_user_id', $clientUser->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get unread count.
     * GET /api/client/notifications/unread-count
     */
    public function unreadCount()
    {
        $clientUser = auth('sanctum')->user();

        $count = Notification::where('client_user_id', $clientUser->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }
}
