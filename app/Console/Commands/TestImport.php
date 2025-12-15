<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\DataSuhuImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Models\Device;
use App\Models\DataSuhu;
use Illuminate\Support\Facades\Auth;

class TestImport extends Command
{
    protected $signature = 'app:test-import {file?}';
    protected $description = 'Test CSV Import with debug output';

    public function handle()
    {
        // Login as first superadmin/admin
        $user = \App\Models\User::first();
        if ($user) {
            Auth::login($user);
            $this->info('Logged in as: ' . $user->name . ' (ID: ' . $user->id . ')');
        } else {
            $this->error('No users found.');
            return;
        }

        // File path
        $filePath = $this->argument('file') ?? base_path('Coolroom Depan Januari.csv');
        
        if (!file_exists($filePath)) {
            $this->error('File not found: ' . $filePath);
            return;
        }
        
        $this->info('Testing import with file: ' . $filePath);
        
        // Count before
        $countBefore = DataSuhu::count();
        $this->info('Records before import: ' . $countBefore);

        // Read raw file content for debug
        $rawContent = file_get_contents($filePath);
        $lines = explode("\n", $rawContent);
        $this->info('Total lines in file: ' . count($lines));
        $this->info('First 3 lines:');
        for ($i = 0; $i < min(3, count($lines)); $i++) {
            $this->line('  Line ' . ($i+1) . ': ' . trim($lines[$i]));
        }

        try {
            Excel::import(new DataSuhuImport, $filePath);
            $this->info('Import completed without exception.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $this->error('Validation Exception:');
            foreach ($e->failures() as $failure) {
                 $this->error('Row ' . $failure->row() . ': ' . implode(', ', $failure->errors()));
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }

        // Count after
        $countAfter = DataSuhu::count();
        $this->info('Records after import: ' . $countAfter);
        $this->info('New records inserted: ' . ($countAfter - $countBefore));
        
        // Show last 3 records
        $lastRecords = DataSuhu::orderBy('id', 'desc')->take(3)->get();
        $this->info('Last 3 records in DB:');
        foreach ($lastRecords as $record) {
            $this->line('  ID: ' . $record->id . ', Device: ' . $record->device_id . ', Section: ' . $record->section . ', Temp: ' . $record->temperature . ', Created: ' . $record->created_at);
        }
    }
}
