@extends('layouts.app')

@section('title', 'Live Challenges List')
@section('page-title', 'Challenges & Competitions')

@section('topbar-actions')
    <a href="{{ route($prefix . '.challenges.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Create Challenge Room
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Registered Challenges</h2>
        <p style="color:var(--text-muted); font-size:14px; margin-top:5px;">You can join a challenge using an invite code or create a new challenge.</p>
    </div>

    <div style="margin-bottom: 25px; padding: 25px; background:var(--bg2); border-radius: 16px; border:1px solid rgba(16,185,129,0.2); box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-size: 18px; margin-bottom: 15px; color: var(--accent);">Have a Join Code?</h3>
        <form action="{{ route('user.challenges.join') }}" method="POST" style="display:flex; gap:12px;">
            @csrf
            <input type="text" name="code" placeholder="Enter Challenge Code (e.g. AB12CD)" style="flex:1; margin:0; font-size: 18px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px;" required maxlength="6">
            <button type="submit" class="btn btn-accent" style="white-space:nowrap; padding: 0 30px; font-weight: 700;">Join Now <i class='bx bx-log-in-circle'></i></button>
        </form>
    </div>

    @if($challenges->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Room Code</th>
                        <th>Creator</th>
                        <th>Participants</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($challenges as $challenge)
                    <tr>
                        <td>
                            <span style="font-size: 20px; font-weight:900; letter-spacing: 2px; color:var(--primary); font-family:monospace; background:rgba(14,165,233,0.1); padding: 5px 15px; border-radius: 8px;">{{ $challenge->code }}</span>
                        </td>
                        <td><span class="badge badge-primary">{{ $challenge->creator->name ?? 'Unknown' }}</span></td>
                        <td><span class="badge" style="background:var(--bg2);">{{ $challenge->participants_count }} Student(s)</span></td>
                        <td style="color:var(--text-muted); font-size:14px;">{{ $challenge->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route($prefix . '.challenges.show', $challenge->id) }}" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px;"><i class='bx bx-show' style="color: var(--primary);"></i></a>
                            
                            @if(in_array(auth()->user()->type, ['admin', 'manager']) || auth()->id() === $challenge->created_by)
                            <form action="{{ route($prefix . '.challenges.destroy', $challenge->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this challenge permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px; border:none; background:transparent;"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $challenges->links() }}</div>
    @else
        <div class="empty-state">
            <i class='bx bx-trophy'></i>
            <h3>No Live Challenges</h3>
            <p>Be the first to open a challenge room and test your peers or students.</p>
        </div>
    @endif
</div>

<style>
    .btn-accent { background: var(--accent); color: #fff; border: 1px solid var(--accent); box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
    .btn-accent:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5); }
</style>
@endsection
