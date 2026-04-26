@extends('layouts.app')

@section('title', 'Pending Approval')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 600px; text-align: center; padding: 40px; border-radius: 20px;">
        <div style="margin-bottom: 24px;">
            <i class='bx bx-time-five' style="font-size: 80px; color: var(--accent); opacity: 0.8;"></i>
        </div>
        
        <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 16px; color: var(--text-color);">Registration Status</h1>
        
        <p style="font-size: 18px; color: var(--text-muted); line-height: 1.6; margin-bottom: 30px;">
            Once your registration is approved by the platform admin, you will be redirected to your dashboard.
        </p>

        <div style="background: rgba(245, 158, 11, 0.1); border: 1px dashed var(--accent); padding: 20px; border-radius: 12px; margin-bottom: 30px;">
            <p style="margin: 0; font-size: 14px; color: var(--accent); font-weight: 600;">
                <i class='bx bx-info-circle'></i> We are currently reviewing your application.
            </p>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline" style="border-radius: 12px; padding: 10px 24px;">
                Logout <i class='bx bx-log-out'></i>
            </button>
        </form>
    </div>
</div>

<style>
    .main-wrapper {
        margin-left: 0 !important;
        margin-top: 0 !important;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 100 100"><rect fill="%230f172a" width="100" height="100"/><circle fill="%231e293b" cx="10" cy="10" r="20"/><circle fill="%23b45309" cx="90" cy="80" r="15" opacity="0.3"/></svg>') no-repeat;
        background-size: cover;
        background-attachment: fixed;
    }
    .sidebar, .topbar {
        display: none !important;
    }
</style>
@endsection
