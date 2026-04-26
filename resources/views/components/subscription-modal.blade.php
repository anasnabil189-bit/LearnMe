<div class="modal-overlay" id="subscriptionModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); z-index: 9999; display: {{ session('show_subscription_modal') ? 'flex' : 'none' }}; align-items: center; justify-content: center; padding: 20px;">
    @php $intended = session('intended_plan'); @endphp
    <div class="modal-content animate-up" style="background: var(--surface); border-radius: var(--radius); width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; padding: 40px; position: relative;">
        
        <button onclick="document.getElementById('subscriptionModal').style.display='none'" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);"><i class='bx bx-x'></i></button>

        <div style="text-align: center; margin-bottom: 30px;">
            <div style="width: 64px; height: 64px; background: rgba(245, 158, 11, 0.1); color: var(--accent); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                <i class='bx bx-crown'></i>
            </div>
            <h2 style="font-size: 28px; font-weight: 800; color: var(--text); margin-bottom: 8px;">Upgrade to Premium</h2>
            <p style="color: var(--text-muted); font-size: 15px;">Unlock all lessons, unlimited quizzes, and full AI feedback explanations daily.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <!-- Individual Tier -->
            <div style="border: 2px solid {{ $intended === 'individual' ? 'var(--primary)' : 'var(--border)' }}; border-radius: 16px; padding: 24px; text-align: center; transition: all 0.3s; position: relative; background: {{ $intended === 'individual' ? 'rgba(20, 184, 166, 0.05)' : 'transparent' }};" onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateY(-5px)';" onmouseout="this.style.borderColor='{{ $intended === 'individual' ? 'var(--primary)' : 'var(--border)' }}'; this.style.transform='translateY(0)';" id="tier_individual_card">
                @if($intended === 'individual')
                    <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--primary); color: white; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase;">Your Selection</div>
                @endif
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px; color: var(--text);">Individual</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Perfect for a single learner</p>
                <div style="font-size: 32px; font-weight: 800; color: var(--primary); margin-bottom: 24px;">319 EGP<span style="font-size: 14px; color: var(--text-muted); font-weight: 500;">/year</span></div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: -20px; margin-bottom: 24px;">(Only 26 EGP / month)</div>
                
                <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; text-align: left; font-size: 15px; color: var(--text);">
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 20px;"></i> Unlimited daily lessons</li>
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 20px;"></i> Unlimited quizzes</li>
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 20px;"></i> Advanced AI Explanations</li>
                </ul>

                <form action="{{ route('payments.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="individual">
                    
                    <div style="margin-bottom: 16px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 4px;">Phone Number <span style="color:var(--accent);">*</span></label>
                        <input type="text" name="phone_number" placeholder="01XXXXXXXXX" required pattern="^01[0125][0-9]{8}$"
                            style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; outline: none;"
                            value="{{ auth()->user()->phone_number }}">
                        <span style="font-size: 10px; color: var(--text-muted);">Required for Paymob Billing</span>
                    </div>

                    <div style="margin-bottom: 16px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 4px;">Organization Code (Optional)</label>
                        <input type="text" name="organization_code" placeholder="Enter code for discount" 
                            style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; outline: none;">
                    </div>

                    <div style="margin-bottom: 20px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 8px;">Payment Method</label>
                        <div style="display: grid; gap: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                <input type="radio" name="method" value="card" checked style="accent-color: var(--primary);">
                                <i class='bx bx-credit-card' style="font-size: 18px; color: var(--primary);"></i> Bank Card
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                <input type="radio" name="method" value="wallet" style="accent-color: var(--primary);">
                                <i class='bx bx-wallet' style="font-size: 18px; color: var(--primary);"></i> Mobile Wallet (Vodafone, etc.)
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                <input type="radio" name="method" value="fawry" style="accent-color: var(--primary);">
                                <i class='bx bx-store' style="font-size: 18px; color: var(--primary);"></i> Fawry Kiosk
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Pay & Upgrade</button>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Discount applies if your email matches your organization's domain.</p>
                </form>
            </div>

            <!-- Family Tier -->
            <div style="border: 2px solid {{ $intended === 'family' ? 'var(--accent)' : 'var(--accent)' }}; border-radius: 16px; padding: 24px; text-align: center; transition: all 0.3s; position: relative; background: linear-gradient(to bottom, rgba(245, 158, 11, {{ $intended === 'family' ? '0.15' : '0.05' }}), transparent);" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';" id="tier_family_card">
                <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--accent); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">{{ $intended === 'family' ? 'Your Selection' : 'Best Value' }}</div>
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px; color: var(--text);">Family</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Up to 6 family members</p>
                <div style="font-size: 32px; font-weight: 800; color: var(--text); margin-bottom: 24px;">1399 EGP<span style="font-size: 14px; color: var(--text-muted); font-weight: 500;">/year</span></div>
                <div style="font-size: 14px; color: var(--text-muted); margin-top: -20px; margin-bottom: 24px;">(Only 116 EGP / month)</div>
                
                <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; text-align: left; font-size: 15px; color: var(--text);">
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 20px;"></i> All Individual features</li>
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 20px;"></i> 2 to 6 Independent accounts</li>
                    <li style="margin-bottom: 12px; display: flex; align-items: center; gap: 8px;"><i class='bx bx-check-circle' style="color: var(--success); font-size: 20px;"></i> Shared learning dashboard</li>
                </ul>

                <form action="{{ route('payments.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan" value="family">
                    
                    <div style="margin-bottom: 16px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 4px;">Phone Number <span style="color:var(--accent);">*</span></label>
                        <input type="text" name="phone_number" placeholder="01XXXXXXXXX" required pattern="^01[0125][0-9]{8}$"
                            style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; outline: none;"
                            value="{{ auth()->user()->phone_number }}">
                        <span style="font-size: 10px; color: var(--text-muted);">Required for Paymob Billing</span>
                    </div><div style="margin-bottom: 20px; text-align: left;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 8px;">Payment Method</label>
                        <div style="display: grid; gap: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                <input type="radio" name="method" value="card" checked style="accent-color: var(--primary);">
                                <i class='bx bx-credit-card' style="font-size: 18px; color: var(--primary);"></i> Bank Card
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                <input type="radio" name="method" value="wallet" style="accent-color: var(--primary);">
                                <i class='bx bx-wallet' style="font-size: 18px; color: var(--primary);"></i> Mobile Wallet
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 1px solid var(--border); border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                <input type="radio" name="method" value="fawry" style="accent-color: var(--primary);">
                                <i class='bx bx-store' style="font-size: 18px; color: var(--primary);"></i> Fawry
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent" style="width: 100%; justify-content: center;">Pay & Upgrade</button>
                </form>
            </div>
        </div>

        @if(!auth()->user()->trial_ends_at)
        <div style="text-align: center; border-top: 1px solid var(--border); padding-top: 24px;">
            <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 14px;">Not sure yet? Try it out for 48 hours completely free.</p>
            <form method="POST" action="{{ route('subscription.start-trial') }}">
                @csrf
                <button type="submit" class="btn btn-ghost" style="font-weight: 600; color: var(--primary-dark);">
                    <i class='bx bx-rocket' style="color: var(--primary);"></i> Start 48-Hour Free Trial
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
