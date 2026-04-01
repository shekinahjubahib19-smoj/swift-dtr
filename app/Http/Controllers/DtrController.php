<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DtrSetting; 
use App\Models\DailyTimeRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtrController extends Controller
{
    /**
     * Handles the "Save Configuration" from the DTR Management page.
     * This locks the intern's shift schedule and profile details.
     */
    public function storeSettings(Request $request)
    {
        // 1. Validation: Keys here must match the 'name' attributes in your Blade file
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

        // 2. Map the request data to your dtr_settings table columns
        $data = [
            'full_name'   => $request->full_name,
            'total_hours' => $request->total_hours, 
            'department'  => $request->department,
            'position'    => $request->position,
            'company'     => 'M Lhuillier', 
            'am_in'       => $request->am_in,  
            'am_out'      => $request->am_out, 
            'pm_in'       => $request->pm_in,  
            'pm_out'      => $request->pm_out, 
        ];

        // 3. Update existing record or create a new one for the logged-in user
        DtrSetting::updateOrCreate(
            ['user_id' => Auth::id()], 
            $data                      
        );

        // Clear session to ensure fresh data is pulled on next load
        session()->forget('dtr_settings');

        return redirect()->route('dtr.manage')->with('success', 'Profile configuration locked and saved!');
    }

    /**
     * Handles the logic for Clocking In and Out.
     * Automatically determines which slot (AM In, AM Out, PM In, PM Out) to fill.
     */
    public function clockAction()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();
        $userId = Auth::id();

        // Fetch user settings to get the official shift times
        $settings = DtrSetting::where('user_id', $userId)->first();

        // Safety check: User must have a profile saved to clock in
        if (!$settings) {
            return back()->with('error', 'Please configure your Internship Profile in Management first.');
        }

        // Set official times from user settings (e.g., 07:00 AM)
        $officialAmIn  = Carbon::today()->setTimeFromTimeString($settings->am_in);
        $officialAmOut = Carbon::today()->setTimeFromTimeString($settings->am_out);
        $officialPmIn  = Carbon::today()->setTimeFromTimeString($settings->pm_in);
        $officialPmOut = Carbon::today()->setTimeFromTimeString($settings->pm_out);

        // Find today's record or create a blank one
        $record = DailyTimeRecord::firstOrCreate(
            ['user_id' => $userId, 'log_date' => $today]
        );

       if (!$record->am_in && $now->lt($officialPmIn)) {
            $logTime = $now->lt($officialAmIn) ? $officialAmIn->toTimeString() : $currentTime;
            $record->update(['am_in' => $logTime]);
            $msg = "Morning Time In recorded: " . Carbon::parse($logTime)->format('h:i A');
        } elseif (!$record->am_out) {
            // Logic: If clocking out late, use official time (no OT). If early, use current time.
            $logTime = $now->gt($officialAmOut) ? $officialAmOut->toTimeString() : $currentTime;
            $record->update(['am_out' => $logTime]);
            $this->recalculateTotalHours($record);
            $msg = "Morning Time Out recorded: " . Carbon::parse($logTime)->format('h:i A');

        } elseif (!$record->pm_in) {
            $logTime = $now->lt($officialPmIn) ? $officialPmIn->toTimeString() : $currentTime;
            $record->update(['pm_in' => $logTime]);
            $msg = "Afternoon Time In recorded: " . Carbon::parse($logTime)->format('h:i A');
        } elseif (!$record->pm_out) {
            $logTime = $now->gt($officialPmOut) ? $officialPmOut->toTimeString() : $currentTime;
            $record->update(['pm_out' => $logTime]);
            $this->recalculateTotalHours($record);
            $msg = "Afternoon Time Out recorded: " . Carbon::parse($logTime)->format('h:i A');

        } else {
            return back()->with('error', 'All slots for today are already filled.');
        }

        return back()->with('success', $msg);
    }

    /**
     * Calculates total hours rendered for the day in decimal format (e.g., 8.5).
     */
    private function recalculateTotalHours(DailyTimeRecord $record)
    {
        $totalMinutes = 0;

        if ($record->am_in && $record->am_out) {
            $totalMinutes += Carbon::parse($record->am_out)
                            ->diffInMinutes(Carbon::parse($record->am_in));
        }

        if ($record->pm_in && $record->pm_out) {
            $totalMinutes += Carbon::parse($record->pm_out)
                            ->diffInMinutes(Carbon::parse($record->pm_in));
        }

        // Convert minutes to decimal hours, rounded to 2 decimal places
        $record->update(['total_hours' => round($totalMinutes / 60, 2)]);
    }
}