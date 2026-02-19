<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Notification;
use App\Models\Tourleader; // Model TL lu
use App\Models\Muthawif;   // Model Muthawif lu
use App\Mail\ReportMasukMail;
use Google\Client as GoogleClient;

class NotificationController extends Controller
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        $this->projectId = 'retali-project';
        $this->credentialsPath = storage_path('app/firebase/retali-project-firebase.json');
    }

    private function getAccessToken(): string
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return $token['access_token'];
    }

    public function index()
    {
        $notifications = Notification::latest()->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Kirim Notifikasi ke SEMUA Tour Leader & Muthawif
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title'   => 'required|string',
            'message' => 'required|string',
        ]);

        // 1. Ambil semua data dari kedua tabel
        $tourleaders = Tourleader::all();
        $muthawifs = Muthawif::all();

        Log::info('--- Memulai Broadcast ke Semua TL & Muthawif ---');

        // --- PROSES TOUR LEADERS ---
        foreach ($tourleaders as $tl) {
            $this->processDelivery($tl, $request->title, $request->message);
        }

        // --- PROSES MUTHAWIFS ---
        foreach ($muthawifs as $mu) {
            $this->processDelivery($mu, $request->title, $request->message);
        }

        // --- KIRIM FCM BROADCAST (Popup HP) ---
        $this->sendFcmBroadcast($request->title, $request->message);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Broadcast berhasil dikirim ke semua TL & Muthawif!');
    }

    /**
     * Helper Fungsi untuk Simpan DB + Kirim Email
     */
    private function processDelivery($user, $title, $message)
    {
        // 1️⃣ Simpan ke Database (Biar muncul di list APK)
        // Pastikan tabel notifications punya kolom user_id yang fleksibel atau
        // disesuaikan dengan logic auth di APK lu
        Notification::create([
            'user_id'   => $user->id,
            'title'     => $title,
            'message'   => $message,
            'is_active' => true,
        ]);

        // 2️⃣ Kirim Email SMTP
        try {
            Mail::to($user->email)->send(new ReportMasukMail(
                $user->name ?? $user->nama, // Handle beda kolom 'name' vs 'nama'
                $message,
                $title
            ));
            Log::info('Email Berhasil dikirim ke: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Email Gagal ke ' . $user->email . ': ' . $e->getMessage());
        }
    }

    /**
     * Helper FCM Broadcast
     */
    private function sendFcmBroadcast($title, $message)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) return;

        $payload = [
            'message' => [
                'topic' => 'all',
                'data' => [
                    'title' => $title,
                    'body'  => $message,
                    'type'  => 'broadcast',
                ],
                'android' => ['priority' => 'high'],
            ],
        ];

        try {
            $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
            Http::withToken($accessToken)->post($url, $payload);
            Log::info('FCM Broadcast Berhasil.');
        } catch (\Exception $e) {
            Log::error('FCM Broadcast Gagal: ' . $e->getMessage());
        }
    }
}
