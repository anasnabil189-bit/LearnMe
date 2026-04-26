@extends('layouts.app')

@section('title', 'Transactions Log')
@section('page-title', 'Transactions')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-credit-card-front' style="color: var(--primary);"></i> Transactions Log
        </h1>
        <p>Monitor all payments and subscriptions initiated by users.</p>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                <tr>
                    <td>
                        <strong style="color: var(--text);">#{{ $txn->paymob_order_id ?? $txn->id }}</strong>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text);">{{ $txn->user->name ?? 'Deleted User' }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $txn->user->email ?? '' }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">{{ ucfirst($txn->plan) }}</span>
                    </td>
                    <td>
                        <strong style="color: var(--success);">{{ number_format($txn->amount, 0) }} EGP</strong>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 500; font-size: 13px;">
                            @if($txn->payment_method === 'fawry')
                                <i class='bx bx-store' style="font-size: 16px; color: #f97316;"></i> Fawry
                            @elseif($txn->payment_method === 'wallet')
                                <i class='bx bx-mobile-alt' style="font-size: 16px; color: #8b5cf6;"></i> Wallet
                            @else
                                <i class='bx bx-credit-card' style="font-size: 16px; color: #3b82f6;"></i> Card
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($txn->status === 'success')
                            <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;"><i class='bx bx-check'></i> Paid</span>
                        @elseif($txn->status === 'failed')
                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;"><i class='bx bx-x'></i> Failed</span>
                        @else
                            <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706;"><i class='bx bx-time'></i> Pending</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 13px; font-weight: 500; color: var(--text);">{{ $txn->created_at->format('M d, Y') }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $txn->created_at->format('h:i A') }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">
                        <i class='bx bx-receipt' style="font-size: 48px; color: var(--border); margin-bottom: 16px;"></i>
                        <p style="font-size: 15px; color: var(--text-muted); margin: 0;">No transactions found yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 20px;">
    {{ $transactions->links('pagination::bootstrap-5') }}
</div>
@endsection
