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
     */
    public function storeSettings(Request $request)
    {
        $request->validate([
            'full_name'   => 'required|string|max:255',
            'total_hours' => 'required|integer|min:1',
            'department'  => 'required|string|max:255',
            'position'    => 'required|string|max:255',
            'am_in'       => 'required',
            'am_out'      => 'required',
            'pm_in'       => 'required',
            'pm_out'      => 'required',
        ]);

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

        DtrSetting::updateOrCreate(
            ['user_id' => Auth::id()], 
            $data                      
        );

        session()->forget('dtr_settings');

        return redirect()->route('dtr.manage')->with('success', 'Profile configuration locked and saved!');
    }

    /**
     * Handles the logic for Clocking In and Out.
     */
    public function clockAction()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();
        $userId = Auth::id();

        $settings = DtrSetting::where('user_id', $userId)->first();

        if (!$settings) {
            return back()->with('error', 'Please configure your Internship Profile in Management first.');
        }

        // Set official times
        $officialAmIn  = Carbon::today()->setTimeFromTimeString($settings->am_in);
        $officialAmOut = Carbon::today()->setTimeFromTimeString($settings->am_out);
        $officialPmIn  = Carbon::today()->setTimeFromTimeString($settings->pm_in);
        $officialPmOut = Carbon::today()->setTimeFromTimeString($settings->pm_out);

        $record = DailyTimeRecord::firstOrCreate(
            ['user_id' => $userId, 'log_date' => $today]
        );

        if (!$record->am_in && $now->lt($officialPmIn)) {
            // Logic: If early, log official time. If late, log current time.
            $logTime = $now->lt($officialAmIn) ? $officialAmIn->toTimeString() : $currentTime;
            $record->update(['am_in' => $logTime]);
            $msg = "Morning Time In recorded: " . Carbon::parse($logTime)->format('h:i A');

        } elseif (!$record->am_out) {
            // Logic: If late, log official time. If early, log current time.
            $logTime = $now->gt($officialAmOut) ? $officialAmOut->toTimeString() : $currentTime;
            $record->update(['am_out' => $logTime]);
            $this->recalculateTotalHours($record); // Update total after morning shift
            $msg = "Morning Time Out recorded: " . Carbon::parse($logTime)->format('h:i A');

        } elseif (!$record->pm_in) {
            $logTime = $now->lt($officialPmIn) ? $officialPmIn->toTimeString() : $currentTime;
            $record->update(['pm_in' => $logTime]);
            $this->recalculateTotalHours($record); // Ensures AM hours persist in the total column
            $msg = "Afternoon Time In recorded: " . Carbon::parse($logTime)->format('h:i A');

        } elseif (!$record->pm_out) {
            $logTime = $now->gt($officialPmOut) ? $officialPmOut->toTimeString() : $currentTime;
            $record->update(['pm_out' => $logTime]);
            $this->recalculateTotalHours($record); // Final daily total
            $msg = "Afternoon Time Out recorded: " . Carbon::parse($logTime)->format('h:i A');

        } else {
            return back()->with('error', 'All slots for today are already filled.');
        }

        return back()->with('success', $msg);
    }

    /**
     * Calculates total hours rendered for the day (AM block + PM block).
     */
   private function recalculateTotalHours(DailyTimeRecord $record)
{
    $totalMinutes = 0;

    // 1. Calculate Morning Block (Only if BOTH are present)
    if (!empty($record->am_in) && !empty($record->am_out)) {
        $amIn = Carbon::parse($record->am_in);
        $amOut = Carbon::parse($record->am_out);
        $totalMinutes += max(0, $amOut->diffInMinutes($amIn));
    }

    // 2. Calculate Afternoon Block (Only if BOTH are present)
    if (!empty($record->pm_in) && !empty($record->pm_out)) {
        $pmIn = Carbon::parse($record->pm_in);
        $pmOut = Carbon::parse($record->pm_out);
        $totalMinutes += max(0, $pmOut->diffInMinutes($pmIn));
    }

    // 3. Convert minutes to decimal (e.g., 1 min / 60 = 0.0166...)
    $decimalHours = $totalMinutes / 60;

    // 4. Update using 'number_format' to force 2 decimal places in the database
    $record->total_hours = $record->calculated_hours; 
    $record->save();
}
}