<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SettlementPdfController extends Controller
{
    public function download($id)
    {
        // Get settlement with user info
        $settlement = DB::table('till_settlements as ts')
            ->leftJoin('users as u', 'u.id', '=', 'ts.user_id')
            ->where('ts.id', $id)
            ->select([
                'ts.*',
                'u.name as user_name'
            ])
            ->first();

        if (!$settlement) {
            return response()->json(['error' => 'Settlement not found'], 404);
        }

        // Get card transactions for this period
        $cardTransactions = DB::table('payments as p')
            ->join('orders as o', 'o.id', '=', 'p.order_id')
            ->where('p.method', 'card')
            ->where('p.created_at', '>=', $settlement->period_start)
            ->where('p.created_at', '<=', $settlement->period_end)
            ->select([
                'p.id',
                'p.created_at',
                'p.amount_cents',
                'p.reference',
                'o.id as order_id',
            ])
            ->orderBy('p.created_at', 'asc')
            ->get();

        // Prepare data for PDF
        $data = [
            'settlement' => $settlement,
            'card_transactions' => $cardTransactions,
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.settlement', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Download with filename
        $filename = 'settlement_' . $settlement->id . '_' . date('Y-m-d', strtotime($settlement->settlement_date)) . '.pdf';

        return $pdf->download($filename);
    }
}
