<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ==================== CONTROLLERS ====================
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TourLeaderController;
use App\Http\Controllers\FCMTokenController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\ChecklistTaskApiController;
use App\Http\Controllers\Api\ChecklistSubmitController;
use App\Http\Controllers\Api\ItineraryApiController;

// **ABSEN JAMAAH**
use App\Http\Controllers\Api\TourLeaderAbsensiController;

// **ABSEN TOURLEADER**
use App\Http\Controllers\Api\AttendanceController;


// ======================================================
// ===============  USER LOGIN ==========================
// ======================================================
Route::post('/login', [AuthController::class, 'login']);


// ======================================================
// ===============  ITINERARY PUBLIC ====================
// ======================================================
Route::get('/itinerary', [ItineraryApiController::class, 'index']);
Route::get('/itinerary/{itinerary}', [ItineraryApiController::class, 'show']);


// ======================================================
// ===============  AUTH SANCTUM USER ====================
// ======================================================
Route::middleware('auth:sanctum')->group(function () {

    // SCAN
    Route::get('/scans', [ScanController::class, 'index']);
    Route::post('/scans', [ScanController::class, 'store']);

    // FCM
    Route::post('/save-fcm-token', function (Request $request) {
        $data = $request->validate([
            'fcm_token' => 'required',
            'platform'  => 'nullable',
        ]);

        $request->user()->update(['fcm_token' => $data['fcm_token']]);

        return response()->json(['success' => true]);
    });

    // NOTIF
    Route::get('/notifications', [NotificationController::class, 'list']);
    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // ITINERARY USER
    Route::post('/itinerary', [ItineraryApiController::class, 'store']);
    Route::put('/itinerary/{itinerary}', [ItineraryApiController::class, 'updateHeader']);
    Route::put('/itinerary/{itinerary}/day-config', [ItineraryApiController::class, 'setDayConfig']);
    Route::put('/itinerary/{itinerary}/days/{dayNumber}', [ItineraryApiController::class, 'fillDay']);
    Route::delete('/itinerary/{itinerary}', [ItineraryApiController::class, 'destroy']);

    // ABSENSI TOUR LEADER (ABSEN KERJA TL)  
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance', [AttendanceController::class, 'myHistory']);
});


// ======================================================
// ===============  TOUR LEADER AUTH =====================
// ======================================================
Route::post('/tourleader/login', [TourLeaderController::class, 'login']);

Route::middleware('auth:tourleader')->group(function () {

    // PROFIL TL
    Route::get('/tourleader/profile', [TourLeaderController::class, 'profile']);

    // SCAN
    Route::get('/tourleader/scans', [ScanController::class, 'index']);
    Route::post('/tourleader/scans', [ScanController::class, 'store']);

    // FCM TL
    Route::post('/tourleader/fcm-token', [FCMTokenController::class, 'store']);
    Route::delete('/tourleader/fcm-token', [FCMTokenController::class, 'destroy']);

    // TASK TL
    Route::get('/tourleader/tasks', [TaskApiController::class, 'index']);
    Route::get('/tourleader/tasks/{task}', [TaskApiController::class, 'show']);
    Route::post('/tourleader/tasks/{task}/done', [TaskApiController::class, 'markDone']);

    // CHECKLIST TL
    Route::get('/tourleader/checklist', [ChecklistTaskApiController::class, 'index']);
    Route::get('/tourleader/checklist/{task}', [ChecklistTaskApiController::class, 'show']);
    Route::post('/tourleader/checklist/{task}/submit', [ChecklistSubmitController::class, 'submit']);

    // ITINERARY TL
    Route::get('/tourleader/itinerary', [ItineraryApiController::class, 'tlList']);
    Route::get('/tourleader/itinerary/{itinerary}', [ItineraryApiController::class, 'tlShow']);



    // ======================================================
    // ===============  ABSENSI JAMAAH (FLOW BARU) =========
    // ======================================================
    Route::get('/tourleader/absensi', [TourLeaderAbsensiController::class, 'index']);
    Route::get('/tourleader/absensi/{id}', [TourLeaderAbsensiController::class, 'show']);
    Route::post('/tourleader/absensi/submit', [TourLeaderAbsensiController::class, 'submit']);


    // ======================================================
    // ========== ABSENSI TOUR LEADER (ABSEN DIRI SENDIRI) ==
    // ======================================================
    // ABSENSI TOUR LEADER (ABSEN DIRI SENDIRI)
    Route::post('/tourleader/attendance', [AttendanceController::class, 'store']);  // <= WAJIB ADA
    Route::get('/tourleader/attendance', [AttendanceController::class, 'myHistory']);

});
