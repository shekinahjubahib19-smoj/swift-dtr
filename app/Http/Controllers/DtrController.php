<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DtrSetting; 
use App\Models\DailyTimeRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\MonthlyTotal;

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

    public function clockAction(Request $request)
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $currentTime = $now->toTimeString();
        $userId = Auth::id();

        // 1. Strict Gate: 12:00 PM onwards is Afternoon
        $isAfternoon = $now->hour >= 12;

        // Load user DTR settings if present. Do not block clock actions
        // for users who haven't configured their profile yet; allow
        // basic time logging without requiring settings.
        $settings = DtrSetting::where('user_id', $userId)->first();

        $record = DailyTimeRecord::firstOrCreate(
            ['user_id' => $userId, 'log_date' => $today]
        );

        // If the client explicitly provided an action, honor it exactly.
        $action = $request->input('action');
        if ($action) {
            switch ($action) {
                case 'am_in':
                    if (!$record->am_in) {
                        $record->update(['am_in' => $currentTime]);
                        $msg = "Morning Time In recorded.";
                        return redirect()->route('dtr.record')->with('success', $msg);
                    }
                    return back()->with('error', 'Morning In already recorded.');

                case 'am_out':
                    if ($record->am_in && !$record->am_out) {
                        $record->update(['am_out' => $currentTime]);
                        $this->recalculateTotalHours($record);
                        $msg = "Morning Time Out recorded.";
                        return redirect()->route('dtr.record')->with('success', $msg);
                    }
                    return back()->with('error', 'Cannot record Morning Out now.');

                case 'pm_in':
                    if (!$record->pm_in) {
                        $record->update(['pm_in' => $currentTime]);
                        $msg = "Afternoon Time In recorded.";
                        return redirect()->route('dtr.record')->with('success', $msg);
                    }
                    return back()->with('error', 'Afternoon In already recorded.');

                case 'pm_out':
                    if ($record->pm_in && !$record->pm_out) {
                        $record->update(['pm_out' => $currentTime]);
                        $this->recalculateTotalHours($record);
                        $msg = "Afternoon Time Out recorded. Shift Complete!";
                        return redirect()->route('dtr.record')->with('success', $msg);
                    }
                    return back()->with('error', 'Cannot record Afternoon Out now.');

                default:
                    // unknown action, continue to heuristic flow
                    break;
            }
        }

        // Auto-fix edge case: if a PM In was recorded very close to noon
        // (e.g. 12:00-12:15) but AM Out is missing while AM In exists,
        // treat that PM In as the AM Out (move it to am_out).
        if ($record->pm_in && !$record->am_out && $record->am_in) {
            try {
                $pmIn = Carbon::parse($record->pm_in);
                $noon = Carbon::parse($today . ' 12:00:00');
                $threshold = $noon->copy()->addMinutes(15);
                if ($pmIn->betweenIncluded($noon, $threshold)) {
                    // Move pm_in => am_out
                    $record->am_out = $record->pm_in;
                    $record->pm_in = null;
                    $record->save();
                    $this->recalculateTotalHours($record);
                    $msg = "Moved noon entry to Morning Out (auto-fix).";
                    return back()->with('success', $msg);
                }
            } catch (\Exception $e) {
                // if parsing fails, ignore and continue with normal flow
            }
        }

        // If there's an open AM session (am_in present but am_out missing),
        // prefer closing it regardless of current hour. This avoids the case
        // where the page showed "Morning Out" when rendered but the server
        // time is already >= 12 and would otherwise record PM In.
        if ($record->am_in && !$record->am_out) {
            $record->update(['am_out' => $currentTime]);
            $this->recalculateTotalHours($record);
            $msg = "Morning Time Out recorded.";
            return back()->with('success', $msg);
        }

        // Similarly, if afternoon has been started but not closed, close it.
        if ($record->pm_in && !$record->pm_out) {
            $record->update(['pm_out' => $currentTime]);
            $this->recalculateTotalHours($record);
            $msg = "Afternoon Time Out recorded. Shift Complete!";
            return back()->with('success', $msg);
        }

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

    /**
     * Save monthly total for the given month (defaults to current month)
     */
    public function saveMonthlyTotal(Request $request)
    {
        $userId = Auth::id();
        $yearMonth = $request->input('year_month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($yearMonth . '-01')->startOfMonth()->toDateString();
        $end = Carbon::parse($yearMonth . '-01')->endOfMonth()->toDateString();

        $totalMinutes = DailyTimeRecord::where('user_id', $userId)
            ->whereBetween('log_date', [$start, $end])
            ->get()
            ->reduce(function ($carry, $rec) {
                return $carry + ($rec->calculated_hours * 60);
            }, 0);

        $totalHours = round($totalMinutes / 60, 2);

        MonthlyTotal::updateOrCreate(
            ['user_id' => $userId, 'year_month' => $yearMonth],
            ['total_hours' => $totalHours]
        );

        return response()->json(['status' => 'ok', 'year_month' => $yearMonth, 'total_hours' => $totalHours]);
    }
}