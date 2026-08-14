<?php

namespace App\Http\Controllers;

use App\Services\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $data = AdminNotificationService::getNotifications();
        return response()->json($data);
    }

    public function markAllRead(): JsonResponse
    {
        AdminNotificationService::markAllRead();
        return response()->json(['success' => true, 'unread_count' => 0]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $id = $request->input('id');
        if ($id) {
            AdminNotificationService::markAsRead($id);
        }
        return response()->json(['success' => true]);
    }
}