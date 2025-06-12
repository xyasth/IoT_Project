<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

class RobotController extends Controller
{
    private static $logFile = 'robot_log.csv';

    private function getFirebaseDatabase()
    {
        $factory = (new Factory)
            ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
            ->withDatabaseUri(env('FIREBASE_DB_URL'));
        return $factory->createDatabase();
    }

    public function index()
    {
        $status = session('status', 'standby');
        $start = session('start_time');
        $log = [];

        $berat = 'N/A';
        $jalur = 'N/A';

        try {
            $database = (new Factory)
                ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
                ->withDatabaseUri(env('FIREBASE_DB_URL'))
                ->createDatabase();


            // Optional: Read current weight/path (if you want)
            $berat = $database->getReference('sensor/berat')->getValue() ?? 'N/A';
            $jalur = $database->getReference('sensor/jalur')->getValue() ?? 'N/A';

            // ✅ Get car movement log
            $entries = $database->getReference('car_movement_log')->getValue();
            $logEntries = array_values($entries ?? []);
            usort($logEntries, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

            $log = [];
            $pendingStart = null;

            foreach ($logEntries as $entry) {
                if (!isset($entry['event'], $entry['timestamp'], $entry['weight'])) {
                    continue; // Skip if data is malformed
                }

                if ($entry['event'] === 'Car STARTED moving') {
                    $pendingStart = $entry;
                } elseif ($entry['event'] === 'Car STOPPED' && $pendingStart) {
                    $startTime = Carbon::createFromTimestamp($pendingStart['timestamp']);
                    $stopTime = Carbon::createFromTimestamp($entry['timestamp']);
                    $duration = $startTime->diff($stopTime)->format('%i menit %s detik');

                    $log[] = [
                        $startTime->format('Y-m-d H:i:s'),
                        $pendingStart['weight'] . ' kg',
                        '-',
                        $startTime->format('H:i:s'),
                        $stopTime->format('H:i:s'),
                        $duration
                    ];

                    $pendingStart = null;
                }
            }


            // $log = array_reverse($pairedLogs); // Optional: latest first
        } catch (\Exception $e) {
            logger()->error('Firebase Error: ' . $e->getMessage());
        }

        return view('robot', [
            'status' => $status,
            'weight' => $berat,
            'path' => $jalur,
            'start_time' => $start,
            'log' => $log,
        ]);
    }

    public function start(Request $request)
    {
        session(['status' => 'sedang berjalan...', 'start_time' => now()]);
        return redirect('/');
    }

    public function finish(Request $request)
    {
        $start = session('start_time') ?? now();
        $end = now();
        $duration = $start->diff($end)->format('%i menit %s detik');

        $berat = 'N/A';
        $jalur = 'N/A';

        try {
            $database = $this->getFirebaseDatabase();

            $berat = $database->getReference('sensor/berat')->getValue() ?? 'N/A';
            $jalur = $database->getReference('sensor/jalur')->getValue() ?? 'N/A';
        } catch (FirebaseException $e) {
            logger()->error('Firebase error: ' . $e->getMessage());
        }

        // Save to CSV
        $record = [
            now()->format('Y-m-d H:i:s'),
            $berat . ' kg',
            'jalur ' . $jalur,
            $start->format('H:i:s'),
            $end->format('H:i:s'),
            $duration
        ];

        $logLine = implode(',', $record) . "\n";
        Storage::append(self::$logFile, $logLine);

        session()->forget(['status', 'start_time']);
        session(['status' => 'selesai']);
        return redirect('/');
    }

    public function export()
    {
        if (!Storage::exists(self::$logFile)) {
            return redirect('/')->with('error', 'Belum ada log!');
        }

        return response(Storage::get(self::$logFile))
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="robot_log.csv"');
    }

    public function reset()
    {
        session()->forget(['status', 'start_time']);
        return redirect('/');
    }
}
