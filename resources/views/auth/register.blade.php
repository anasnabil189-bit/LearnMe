@extends('layouts.app')

@section('title', 'Create New Account')

@push('styles')
<style>
    /* Override global layout margins for registration page */
    .main-wrapper {
        margin-left: 0 !important;
        margin-top: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
</style>
@endpush

@section('content')
    <div style="display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; padding: 20px; width: 100%;">
        <div class="card animate-up register-card">
            
            <!-- Left Side: Welcome Hero -->
            <div class="register-left">
                <!-- Decorative Circle -->
                <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; justify-content: center; margin-bottom: 40px; background: #fff; padding: 12px 48px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <x-logo-brand size="lg" />
                    </a>
                    <h1 style="font-size: 32px; font-weight: 900; line-height: 1.2; margin-bottom: 20px; letter-spacing: -1px;">Start your elite learning journey</h1>
                    <p style="font-size: 16px; opacity: 0.9; font-weight: 500; line-height: 1.6;">Join thousands of students and schools worldwide powered by AI and smart gamification.</p>
                </div>

                <div style="margin-top: auto; position: relative; z-index: 1;">
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; opacity: 0.8; font-weight: 600;">
                        <i class='bx bxs-check-shield' style="font-size: 20px;"></i> Professional Secure Platform
                    </div>
                </div>
            </div>

            <!-- Right Side: Form Inputs -->
            <div class="register-right">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    @if ($errors->any())
                        <div style="background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; padding: 16px; border-radius: 12px; margin-bottom: 24px;">
                            <ul style="margin: 0; padding-left: 20px; font-size: 13px; font-weight: 700;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Basic Info -->
                    <div class="form-grid-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">Full Name</label>
                            <div style="position: relative;">
                                <i class='bx bx-user' style="position: absolute; left: 14px; top: 12px; color: var(--text-muted); font-size: 16px;"></i>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                                    style="padding-left: 38px; height: 42px; font-size: 14px;" placeholder="e.g. Abdullah Ahmed">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">Email Address</label>
                            <div style="position: relative;">
                                <i class='bx bx-envelope' style="position: absolute; left: 14px; top: 12px; color: var(--text-muted); font-size: 16px;"></i>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    style="padding-left: 38px; height: 42px; font-size: 14px;" placeholder="name@example.com">
                            </div>
                        </div>
                    </div>



                    <!-- Role Selection -->
                    <div style="margin-bottom: 20px; background: #f8fafc; padding: 16px; border-radius: 16px; border: 1px solid var(--border);">
                        <label style="font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block; margin-bottom: 12px;">Choose your role</label>
                        <div class="role-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                            <label class="reg-type-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px; background: #fff; border: 1px solid var(--border); border-radius: 10px; transition: 0.2s;">
                                <input type="radio" name="reg_type" value="self" checked onclick="toggleRegistrationType('self')" style="accent-color: var(--primary);">
                                <span style="font-weight: 700; color: var(--text); font-size: 13px;">Courses</span>
                            </label>
                            <label class="reg-type-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px; background: #fff; border: 1px solid var(--border); border-radius: 10px; transition: 0.2s;">
                                <input type="radio" name="reg_type" value="school" onclick="toggleRegistrationType('school')" style="accent-color: var(--primary);">
                                <span style="font-weight: 700; color: var(--text); font-size: 13px;">School Student</span>
                            </label>
                            <label class="reg-type-label" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px; background: #fff; border: 1px solid var(--border); border-radius: 10px; transition: 0.2s;">
                                <input type="radio" name="reg_type" value="school_owner" onclick="toggleRegistrationType('school_owner')" style="accent-color: var(--primary);">
                                <span style="font-weight: 700; color: var(--text); font-size: 13px;">School Owner</span>
                            </label>
                        </div>
                    </div>





                    <!-- School Owner Selection -->
                    <div id="school_plans_group" style="display: none; margin-bottom: 20px;">
                        <label style="font-weight: 800; font-size: 11px; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 10px;">School Capacity & Pricing</label>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 2px solid var(--border); transition: 0.3s;" id="capacity_container">
                            <div style="margin-bottom: 16px;">
                                <label style="font-size: 13px; font-weight: 700; margin-bottom: 8px; display: block;">Number of Students (Limit)</label>
                                <div style="position: relative;">
                                    <i class='bx bx-group' style="position: absolute; left: 14px; top: 12px; color: var(--text-muted); font-size: 16px;"></i>
                                    <input type="number" id="student_limit" name="student_limit" value="{{ old('student_limit', 100) }}" min="1" step="1"
                                        style="padding-left: 38px; height: 42px; font-size: 14px;" placeholder="Enter number of students"
                                        oninput="updateSchoolPrice(this.value)">
                                </div>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px; font-weight: 500;">Pricing: 20 EGP per student annually.</p>
                            </div>
                            
                            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid var(--border);">
                                <span style="font-weight: 700; color: var(--text); font-size: 14px;">Total Annual Plan:</span>
                                <div style="text-align: right;">
                                    <span id="total_school_price" style="font-size: 20px; font-weight: 900; color: var(--primary);">2,000</span>
                                    <span style="font-size: 14px; font-weight: 700; color: var(--primary);">EGP</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment specific for School Owner -->
                        <div style="margin-top: 20px; text-align: left;">
                            <label style="font-size: 13px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 4px;">Phone Number <span style="color:var(--accent);">*</span></label>
                            <input type="text" name="phone_number" id="phone_number" placeholder="01XXXXXXXXX" pattern="^01[0125][0-9]{8}$"
                                style="width: 100%; padding: 0 14px; border-radius: 10px; border: 1px solid var(--border); font-size: 14px; outline: none; height: 42px; font-weight: 600;">
                            <span style="font-size: 11px; color: var(--text-muted);">Required to process your school's payment via Paymob.</span>
                        </div>

                        <div style="margin-top: 20px;">
                            <label style="font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); display: block; margin-bottom: 12px;">Payment Method</label>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                                <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; cursor: pointer; padding: 12px 8px; background: #fff; border: 1px solid var(--border); border-radius: 12px; transition: 0.2s; font-size: 11px; font-weight: 700; text-align: center;">
                                    <input type="radio" name="payment_method" value="card" checked style="accent-color: var(--primary);">
                                    <i class='bx bx-credit-card' style="font-size: 18px; color: var(--primary);"></i> Card
                                </label>
                                <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; cursor: pointer; padding: 12px 8px; background: #fff; border: 1px solid var(--border); border-radius: 12px; transition: 0.2s; font-size: 11px; font-weight: 700; text-align: center;">
                                    <input type="radio" name="payment_method" value="wallet" style="accent-color: var(--primary);">
                                    <i class='bx bx-wallet' style="font-size: 18px; color: var(--primary);"></i> Wallet
                                </label>
                                <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; cursor: pointer; padding: 12px 8px; background: #fff; border: 1px solid var(--border); border-radius: 12px; transition: 0.2s; font-size: 11px; font-weight: 700; text-align: center;">
                                    <input type="radio" name="payment_method" value="fawry" style="accent-color: var(--primary);">
                                    <i class='bx bx-store' style="font-size: 18px; color: var(--primary);"></i> Fawry
                                </label>
                            </div>
                        </div>
                    </div>



                    <!-- School Codes -->
                    <div id="school_code_group" style="display: none; margin-bottom: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group"><label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">School Code *</label><input type="text" id="school_code" name="school_code" placeholder="SCH-XXXXXX" style="height: 42px; font-size: 14px;"></div>
                            <div class="form-group"><label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">Grade Code *</label><input type="text" id="grade_code" name="grade_code" placeholder="GRD-YYYYYY" style="height: 42px; font-size: 14px;"></div>
                        </div>
                    </div>

                    <!-- Passwords -->
                    <div class="form-grid-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">Password</label>
                            <div style="position: relative;">
                                <i class='bx bx-lock-alt' style="position: absolute; left: 14px; top: 12px; color: var(--text-muted); font-size: 16px;"></i>
                                <input type="password" id="password" name="password" required style="padding-left: 38px; height: 42px; font-size: 14px;" placeholder="••••••••">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 13px; font-weight: 700; margin-bottom: 4px; display: block;">Confirm Password</label>
                            <div style="position: relative;">
                                <i class='bx bx-lock-alt' style="position: absolute; left: 14px; top: 12px; color: var(--text-muted); font-size: 16px;"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation" required style="padding-left: 38px; height: 42px; font-size: 14px;" placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; border-top: 1px solid var(--border); padding-top: 24px;">
                        <div style="font-size: 14px; color: var(--text-muted); font-weight: 500;">
                            Already account? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 800;">Login Now</a>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 12px 48px; font-size: 16px; border-radius: 14px;">
                            Create My Account <i class='bx bx-check-circle'></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleRegistrationType(type) {
            const schoolGroup = document.getElementById('school_code_group');
            const schoolPlansGroup = document.getElementById('school_plans_group');
            
            document.querySelectorAll('.reg-type-label').forEach(label => {
                label.style.borderColor = 'var(--border)';
                label.style.background = '#fff';
            });
            const activeLabel = document.querySelector('input[name="reg_type"][value="'+type+'"]').closest('.reg-type-label');
            activeLabel.style.borderColor = 'var(--primary)';
            activeLabel.style.background = 'rgba(20, 184, 166, 0.05)';

            schoolGroup.style.display = 'none';
            schoolPlansGroup.style.display = 'none';

            if (type === 'school') {
                schoolGroup.style.display = 'block';
            } else if (type === 'school_owner') {
                schoolPlansGroup.style.display = 'block';
            }
        }

        function updateSchoolPrice(limit) {
            const pricePerStudent = 20;
            const total = (limit || 0) * pricePerStudent;
            document.getElementById('total_school_price').innerText = new Intl.NumberFormat().format(total);
            
            // Visual feedback
            const container = document.getElementById('capacity_container');
            container.style.borderColor = total > 0 ? 'var(--primary)' : 'var(--border)';
            container.style.background = total > 0 ? 'rgba(20, 184, 166, 0.05)' : '#f8fafc';
        }

        window.onload = function() {
            toggleRegistrationType("{{ old('reg_type', 'self') }}");
            @if(old('student_limit')) updateSchoolPrice("{{ old('student_limit') }}"); @endif
        };
    </script>

    <style>
        .sidebar, .topbar { display: none !important; }
        .main-wrapper {
            margin-left: 0 !important; margin-top: 0 !important;
            background: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(20, 184, 166, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            display: flex; justify-content: center; align-items: flex-start;
            padding: 40px 0;
            min-height: 100vh;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 0 14px; border-radius: 10px;
            border: 1px solid var(--border); background: #fff;
            font-weight: 600; transition: 0.2s; outline: none;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.1); }
        
        .register-card {
            width: 100%; max-width: 1100px; padding: 0; display: flex;
            background: var(--surface); border-radius: 24px; overflow: hidden;
            border: none; box-shadow: var(--shadow-lg);
        }
        .register-left {
            flex: 0 0 380px; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 48px; display: flex; flex-direction: column; justify-content: center;
            color: #fff; position: relative; overflow: hidden;
        }
        .register-right {
            flex: 1; padding: 40px 48px; background: #fff;
        }

        @media (max-width: 992px) {
            .register-card { flex-direction: column; }
            .register-left { flex: none; padding: 32px; }
            .register-right { padding: 32px 24px; }
            .form-grid-responsive { grid-template-columns: 1fr !important; }
            .role-grid, .tier-grid { grid-template-columns: 1fr !important; }
        }
    </style>
@endsection