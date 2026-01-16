<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use Google\Client as GoogleClient;

class NotificationController extends Controller
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct()
    {
        // GANTI sesuai project_id Firebase kamu
        $this->projectId = 'retali-project';

        // Path service account JSON Firebase
        $this->credentialsPath = storage_path('app/firebase/retali-project-firebase.json');
    }

    /**
     * Ambil OAuth2 Access Token untuk Firebase Cloud Messaging v1
     */
    private function getAccessToken(): string
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();

        $token = $client->getAccessToken();

        return $token['access_token'];
    }

    /**
     * List notifikasi (Admin)
     */
    public function index()
    {
        $notifications = Notification::latest()->get();
        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Form buat notifikasi
     */
    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Simpan notifikasi & kirim ke FCM
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'title'     => 'required|string',
            'message'   => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        // 1️⃣ Simpan ke database
        $notification = Notification::create([
            'title'     => $request->title,
            'message'   => $request->message,
            'is_active' => true,
        ]);

        // 2️⃣ Tentukan target
        // Jika ada token → kirim ke 1 device
        // Jika kosong → broadcast ke topic "all"
        $target = $request->filled('fcm_token')
            ? ['token' => $request->fcm_token]
            : ['topic' => 'all'];

        // 3️⃣ Payload FCM (WAJIB ada "notification")
        $payload = [
    'message' => array_merge($target, [
        'data' => [                         // ✅ DATA ONLY
            'title' => $request->title,
            'body'  => $request->message,
            'type'  => 'general',
            'notification_id' => (string) $notification->id,
        ],
        'android' => [
            'priority' => 'high',
        ],
    ]),
];


        // 4️⃣ Kirim ke Firebase
        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $response = Http::withToken($this->getAccessToken())
            ->withHeader('Content-Type', 'application/json')
            ->post($url, $payload);

        // (opsional) logging jika error
        if ($response->failed()) {
            \Log::error('FCM ERROR', [
                'response' => $response->body(),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Notifikasi berhasil dikirim');
    }
}
