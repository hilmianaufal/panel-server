<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TelegramNotifier;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index', [
            'botToken' => config('services.telegram.bot_token'),
            'chatId' => config('services.telegram.chat_id'),
        ]);
    }

    public function test(): RedirectResponse
    {
        $message =
            "✅ <b>Test Notifikasi Berhasil</b>\n\n" .
            "Server: <b>" . config('app.name') . "</b>\n" .
            "Time: " . now()->format('d M Y H:i:s');

        $sent = TelegramNotifier::send($message);

        if (! $sent) {
            return back()->with('error', 'Gagal mengirim notifikasi Telegram. Cek token dan chat ID.');
        }

        return back()->with('success', 'Notifikasi test berhasil dikirim ke Telegram.');
    }
}