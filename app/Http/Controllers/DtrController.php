<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DtrSetting; 
use App\Models\DailyTimeRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DtrController extends Controller
{
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

    public function clockAction()
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();
        $userId = Auth::id();

        // 1. Strict Gate: 12:00 PM onwards is Afternoon
        $isAfternoon = $now->hour >= 12;

        $settings = DtrSetting::where('user_id', $userId)->first();
        if (!$settings) {
            return back()->with('error', 'Please configure your profile first.');
        }

        $record = DailyTimeRecord::firstOrCreate(
            ['user_id' => $userId, 'log_date' => $today]
        );

        // --- AFTERNOON LOGIC ---
        if ($isAfternoon) {
            if (!$record->pm_in) {
                $record->update(['pm_in' => $currentTime]);
                $msg = "Afternoon Time In recorded.";
            } 
            elseif (!$record->pm_out) {
                $record->update(['pm_out' => $currentTime]);
                $this->recalculateTotalHours($record); 
                $msg = "Afternoon Time Out recorded. Shift Complete!";
            } 
            else {
                return back()->with('error', 'Afternoon shift already completed.');
            }
        } 
        // --- MORNING LOGIC ---
        else {
            if (!$record->am_in) {
                $record->update(['am_in' => $currentTime]);
                $msg = "Morning Time In recorded.";
            } 
            elseif (!$record->am_out) {
                $record->update(['am_out' => $currentTime]);
                $this->recalculateTotalHours($record); 
                $msg = "Morning Time Out recorded.";
            } 
            else {
                // If they finished morning but it's still before 12PM
                return back()->with('error', 'Morning shift completed. Afternoon logs start at 12:00 PM.');
            }
        }

        return back()->with('success', $msg);
    }

    /**
     * Recalculates the total hours based on AM and PM sessions.
     */
   private function recalculateTotalHours(DailyTimeRecord $record)
{
    $totalMinutes = 0;

    // 1. Morning Session: Only calculate if BOTH am_in and am_out exist
    if ($record->am_in && $record->am_out) {
        $amIn = Carbon::parse($record->am_in);
        $amOut = Carbon::parse($record->am_out);
        
        // Ensure out is later than in to avoid negative results
        if ($amOut->gt($amIn)) {
            $totalMinutes += $amIn->diffInMinutes($amOut);
        }
    }

    // 2. Afternoon Session: Only calculate if BOTH pm_in and pm_out exist
    if ($record->pm_in && $record->pm_out) {
        $pmIn = Carbon::parse($record->pm_in);
        $pmOut = Carbon::parse($record->pm_out);
        
        // Ensure out is later than in to avoid negative results
        if ($pmOut->gt($pmIn)) {
            $totalMinutes += $pmIn->diffInMinutes($pmOut);
        }
    }

    // 3. Convert to hours and round to 2 decimal places
    $record->total_hours = round($totalMinutes / 60, 2); 
    $record->save();
}
}