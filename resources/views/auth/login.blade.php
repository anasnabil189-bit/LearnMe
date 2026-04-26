@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div style="display: flex; justify-content: center; align-items: flex-start; padding: 60px 20px;">
    <div class="card" style="width: 100%; max-width: 450px;">
        <div class="card-header text-center" style="display: block; border: none; margin-bottom: 30px;">
            <div style="display: flex; justify-content: center; margin-bottom: 24px;">
                <a href="{{ route('home') }}">
                    <x-logo-brand size="xl" />
                </a>
            </div>
            <h1 class="card-title" style="font-size: 28px; font-weight: 900; margin-bottom: 8px;">Welcome Back!</h1>
            <p style="color: var(--text-muted); font-size: 15px; font-weight: 500;">Sign in to access your professional area</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <div style="position: relative;">
                    <i class='bx bx-envelope' style="position: absolute; left: 14px; top: 14px; color: var(--text-muted); font-size: 18px;"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus style="padding-left: 40px;" placeholder="Enter your email">
                </div>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div style="position: relative;">
                    <i class='bx bx-lock-alt' style="position: absolute; left: 14px; top: 14px; color: var(--text-muted); font-size: 18px;"></i>
                    <input type="password" id="password" name="password" required style="padding-left: 40px;" placeholder="Enter your password">
                </div>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="remember" id="remember" style="width: 16px; height: 16px; accent-color: var(--primary);">
                <label for="remember" style="margin: 0; font-size: 14px; font-weight: 400; cursor: pointer;">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; font-size: 16px; border-radius: 12px; margin-top: 10px;">
                Sign In <i class='bx bx-right-arrow-alt'></i>
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; font-size: 14px; color: var(--text-muted);">
            Don't have an account? <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 700;">Create Account</a>
        </div>
    </div>
</div>

<style>
    /* Hide topbar and sidebar on auth pages */
    .sidebar { display: none !important; }
    .topbar { display: none !important; }
    .main-wrapper { 
        margin-left: 0; 
        margin-top: 0; 
        min-height: 100vh; 
        display: flex; 
        flex-direction: column;
        background: #f8fafc;
        background-image: 
            radial-gradient(at 0% 0%, rgba(20, 184, 166, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.05) 0px, transparent 50%);
        background-attachment: fixed;
    } 
</style>
@endsection
