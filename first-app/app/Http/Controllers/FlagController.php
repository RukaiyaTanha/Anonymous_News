<?php

namespace App\Http\Controllers;

use App\Models\Flag;
use App\Models\Report;
use Illuminate\Http\Request;

class FlagController extends Controller
{
    public function store(Request $request, Report $report)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $existingFlag = Flag::where('user_id', auth()->id())
            ->where('report_id', $report->id)
            ->first();

        if (! $existingFlag) {
            Flag::create([
                'user_id' => auth()->id(),
                'report_id' => $report->id,
                'reason' => $request->reason,
                'created_at' => now(),
            ]);
        }

        return back()->with('success', 'Report flagged successfully.');
    }
}
