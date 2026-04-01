<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// 1. Ensure you are using the DtrSetting model, not DtrLog
use App\Models\DtrSetting; 
use Illuminate\Support\Facades\Auth;
use App\Models\DailyTimeRecord;
use Carbon\Carbon;

class DtrController extends Controller
{
    public function storeSettings(Request $request)
    {
        // Validation matches your form inputs
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'total_hours'  => 'required|integer|min:1',
            'department'   => 'required|string|max:255',
            'position'     => 'required|string|max:255',
            'am_in'        => 'required',
            'am_out'       => 'required',
            'pm_in'        => 'required',
            'pm_out'       => 'required',
        ]);

        // 2. Map data to match your 'dtr_settings' table columns exactly
        $data = [
            'full_name'   => $request->full_name,
            'total_hours' => $request->total_hours, // Matches migration
            'department'  => $request->department,
            'position'    => $request->position,
            'company'     => 'M Lhuillier',         // Your internship company
            'am_in'       => $request->am_in,       // Matches migration
            'am_out'      => $request->am_out,      // Matches migration
            'pm_in'       => $request->pm_in,       // Matches migration
            'pm_out'      => $request->pm_out,      // Matches migration
        ];

        // 3. Use DtrSetting model to save/update
        DtrSetting::updateOrCreate(
            ['user_id' => Auth::id()], 
            $data                      
        );

        // Clear session to refresh data
        session()->forget('dtr_settings');

        return redirect()->route('dtr.manage')->with('success', 'Profile configuration locked and saved!');
    }

    public function clockAction()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();
        $userId = Auth::id();

        // Find or create today's record
        $record = DailyTimeRecord::firstOrCreate(
            ['user_id' => $userId, 'log_date' => $today]
        );

        // Logic to fill the next empty slot
        if (!$record->am_in) {
            $record->update(['am_in' => $currentTime]);
            $msg = "Clocked In for Morning";
        } elseif (!$record->am_out) {
            $record->update(['am_out' => $currentTime]);
            $msg = "Clocked Out for Lunch";
        } elseif (!$record->pm_in) {
            $record->update(['pm_in' => $currentTime]);
            $msg = "Clocked In for Afternoon";
        } elseif (!$record->pm_out) {
            $record->update(['pm_out' => $currentTime]);
            $msg = "Clocked Out for the Day";
        } else {
            return back()->with('error', 'All slots for today are already filled.');
        }

        return back()->with('success', $msg);
    }
}