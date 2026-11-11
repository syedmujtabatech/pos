<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BackupLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $backupLogs = BackupLog::latest()->take(10)->get();

        return view('settings.index', compact('settings', 'backupLogs'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        AuditLog::log('update_settings', null, null, $data, 'Company settings updated');

        return back()->with('success', 'Company settings updated successfully.');
    }

    public function createBackup()
    {
        try {
            $backupDir = storage_path('app/backups');
            if (!File::isDirectory($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $filename = 'backup_al_noor_' . date('Y_m_d_His') . '.sqlite';
            $dbPath = database_path('database.sqlite');

            if (File::exists($dbPath)) {
                File::copy($dbPath, $backupDir . '/' . $filename);
                $size = File::size($backupDir . '/' . $filename);

                BackupLog::create([
                    'filename' => $filename,
                    'file_size' => $size,
                    'status' => 'success',
                ]);

                AuditLog::log('backup_created', null, null, ['filename' => $filename], 'Database backup taken');

                return back()->with('success', "Database backup created successfully ({$filename}).");
            }

            return back()->with('error', 'Database file not found.');
        } catch (\Exception $e) {
            BackupLog::create([
                'filename' => 'backup_failed',
                'file_size' => 0,
                'status' => 'failed',
            ]);

            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
}
