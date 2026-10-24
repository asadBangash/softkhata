<?php

namespace App\Services;

use App\Models\Party;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class LedgerService
{
    public function partyBalance(Party $party): float
    {
        $opening = $party->balance_type === 'receive'
            ? (float) $party->opening_balance
            : -(float) $party->opening_balance;

        $delta = Transaction::query()
            ->where('party_id', $party->id)
            ->whereNull('deleted_at')
            ->get()
            ->sum(fn (Transaction $t) => $this->signedAmount($t));

        return round($opening + $delta, 2);
    }

    public function signedAmount(Transaction $transaction): float
    {
        $amount = (float) $transaction->amount;

        return match ($transaction->type) {
            'receive', 'sale', 'income' => $amount,
            'give', 'purchase', 'expense', 'payment', 'refund' => -$amount,
            default => 0,
        };
    }

    public function ledger(Party $party, ?string $from = null, ?string $to = null): array
    {
        $openingSigned = $party->balance_type === 'receive'
            ? (float) $party->opening_balance
            : -(float) $party->opening_balance;

        $query = Transaction::query()
            ->where('party_id', $party->id)
            ->orderBy('date')
            ->orderBy('id');

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        /** @var Collection<int, Transaction> $rows */
        $rows = $query->get();
        $running = $openingSigned;
        $lines = [];

        $lines[] = [
            'date' => $from ?? optional($rows->first())->date?->format('Y-m-d'),
            'description' => 'Opening Balance',
            'debit' => $running > 0 ? $running : 0,
            'credit' => $running < 0 ? abs($running) : 0,
            'balance' => $running,
        ];

        foreach ($rows as $row) {
            $signed = $this->signedAmount($row);
            $running += $signed;
            $lines[] = [
                'id' => $row->id,
                'date' => $row->date->format('Y-m-d'),
                'description' => $row->description ?? ucfirst($row->type),
                'type' => $row->type,
                'debit' => $signed > 0 ? $signed : 0,
                'credit' => $signed < 0 ? abs($signed) : 0,
                'balance' => round($running, 2),
                'payment_method_id' => $row->payment_method_id,
                'reference' => $row->reference,
                'notes' => $row->notes,
            ];
        }

        return [
            'opening_balance' => $openingSigned,
            'closing_balance' => round($running, 2),
            'lines' => $lines,
        ];
    }
}
