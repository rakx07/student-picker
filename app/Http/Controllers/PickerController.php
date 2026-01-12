<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SelectedExport;
use App\Exports\RemainingExport;

use Barryvdh\DomPDF\Facade\Pdf;

class PickerController extends Controller
{
    public function index(Request $request)
    {
        $remaining = session('remaining', []);
        $selected  = session('selected', []);

        return view('picker', compact('remaining', 'selected'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'students' => ['required', 'string'],
        ]);

        $raw = $request->input('students');

        $lines = preg_split("/\r\n|\n|\r/", trim($raw));
        $names = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Split Excel columns (tab / comma / semicolon)
            $cells = preg_split("/\t|,|;/", $line);

            // Combine ALL columns into a full name
            $fullName = implode(' ', array_filter(array_map('trim', $cells)));

            // Normalize spaces
            $fullName = preg_replace('/\s+/', ' ', $fullName);

            if ($fullName !== '') {
                $names[] = $fullName;
            }
        }

        // Remove duplicates but keep order
        $names = array_values(array_unique($names));

        session([
            'remaining' => $names,
            'selected'  => [],
        ]);

        return redirect()->route('picker.index')
            ->with('ok', 'Student list imported: ' . count($names));
    }

    public function draw(Request $request)
    {
        $remaining = session('remaining', []);
        $selected  = session('selected', []);

        if (count($remaining) === 0) {
            return response()->json([
                'ok' => false,
                'message' => 'No remaining students.',
            ], 422);
        }

        // Pick random
        $winnerIndex = random_int(0, count($remaining) - 1);
        $winnerName  = $remaining[$winnerIndex];

        // Remove winner from remaining
        array_splice($remaining, $winnerIndex, 1);

        // Add to selected (with timestamp + draw order)
        $selected[] = [
            'name' => $winnerName,
            'drawn_at' => now()->format('Y-m-d H:i:s'),
            'draw_no' => count($selected) + 1,
        ];

        session([
            'remaining' => $remaining,
            'selected'  => $selected,
        ]);

        return response()->json([
            'ok' => true,
            'winner' => $winnerName,
            'remaining' => $remaining,
            'selected' => $selected,
        ]);
    }

    public function reset()
    {
        session()->forget(['remaining', 'selected']);
        return redirect()->route('picker.index')->with('ok', 'Reset complete.');
    }

    // ====== EXPORT SELECTED ======

    public function exportCsv()
    {
        $selected = session('selected', []);
        return Excel::download(new SelectedExport($selected), 'selected_students.csv');
    }

    public function exportXlsx()
    {
        $selected = session('selected', []);
        return Excel::download(new SelectedExport($selected), 'selected_students.xlsx');
    }

    public function exportPdf()
    {
        $selected = session('selected', []);

        $pdf = Pdf::loadView('exports.selected_pdf', [
            'selected' => $selected,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('selected_students.pdf');
    }

    // ====== EXPORT REMAINING ======

    public function exportRemainingCsv()
    {
        $remaining = session('remaining', []);
        return Excel::download(new RemainingExport($remaining), 'remaining_students.csv');
    }

    public function exportRemainingXlsx()
    {
        $remaining = session('remaining', []);
        return Excel::download(new RemainingExport($remaining), 'remaining_students.xlsx');
    }
}
