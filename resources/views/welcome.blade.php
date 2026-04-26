<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnme - Smart Language Learning Platform</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-learnme.png') }}">
    <style>
        :root {
            --primary:       #14b8a6; /* Sea Teal */
            --primary-dark:  #0f766e;
            --accent:        #f59e0b; /* Amber */
            --bg:            #f8fafc; /* Whisper Gray */
            --surface:       #ffffff;
            --text:          #0f172a; /* Deep Slate */
            --text-muted:    #64748b;
            --border:        #e2e8f0;
            --radius:        24px;
            --shadow:        0 10px 40px rgba(0,0,0,0.06);
            --shadow-lg:     0 20px 50px rgba(0,0,0,0.1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both; }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        body { background: var(--bg); color: var(--text); overflow-x: hidden; line-height: 1.6; }
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        
        /* Navbar */
        .navbar {
            position: fixed; top: 0; width: 100%; padding: 20px 8%;
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(16px); z-index: 1000;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a { color: var(--text-muted); font-weight: 600; font-size: 15px; }
        .nav-links a:hover { color: var(--primary); }
        .auth-buttons { display: flex; gap: 12px; align-items: center; }
        
        /* Buttons */
        .btn { 
            padding: 12px 28px; border-radius: 14px; font-weight: 700; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: inline-flex; align-items: center; gap: 10px; border: none; cursor: pointer;
        }
        .btn-ghost { background: transparent; color: var(--text); border: 1px solid var(--border); }
        .btn-ghost:hover { background: #f1f5f9; color: var(--primary); transform: translateY(-2px); }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 10px 20px rgba(20, 184, 166, 0.2); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(20, 184, 166, 0.3); }
        
        /* Hero */
        .hero {
            min-height: 100vh; display: flex; align-items: center; justify-content: space-between;
            padding: 160px 8%;
            background: radial-gradient(at 0% 0%, rgba(20, 184, 166, 0.05) 0px, transparent 50%),
                        radial-gradient(at 100% 100%, rgba(245, 158, 11, 0.05) 0px, transparent 50%);
            position: relative;
            overflow: hidden;
            gap: 40px;
        }
        .hero-content {
            position: relative;
            z-index: 1;
            flex: 1;
            max-width: 600px;
        }
        .hero-content h1 { font-size: 64px; margin-bottom: 24px; font-weight: 900; line-height: 1.1; letter-spacing: -2px; color: var(--text); }
        .hero-content h1 span { color: var(--primary); }
        .hero-content p { font-size: 20px; color: var(--text-muted); margin: 0 0 40px 0; font-weight: 500; }
        .hero-buttons { display: flex; gap: 16px; flex-wrap: wrap; }
        
        .hero-visual {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .hero-visual-wrapper {
            position: relative;
            width: 100%;
            max-width: 500px;
            aspect-ratio: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .hero-blob {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.15), rgba(245, 158, 11, 0.15));
            filter: blur(60px);
            border-radius: 50%;
            z-index: 0;
            animation: pulseBlob 8s infinite alternate ease-in-out;
        }
        .hero-logo-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 40px;
            padding: 48px;
            box-shadow: 0 30px 60px -15px rgba(20, 184, 166, 0.15);
            animation: floatLogo 6s infinite ease-in-out;
        }
        .hero-logo-container img {
            width: 100%;
            max-width: 300px;
            height: auto;
            display: block;
        }
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        @keyframes pulseBlob {
            0% { transform: scale(0.8); opacity: 0.6; }
            100% { transform: scale(1.1); opacity: 0.9; }
        }
        
        /* Sections */
        section { padding: 120px 8%; }
        .section-header { text-align: center; margin-bottom: 72px; }
        .section-header h2 { font-size: 48px; font-weight: 900; letter-spacing: -1px; margin-bottom: 16px; }
        .section-header p { font-size: 18px; color: var(--text-muted); max-width: 600px; margin: 0 auto; font-weight: 500; }
        
        /* Features Grid */
        .features { background: #fff; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px; }
        .card { 
            background: var(--bg); padding: 48px; border-radius: var(--radius); text-align: left;
            border: 1px solid var(--border); transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            position: relative; overflow: hidden;
        }
        .card:hover { transform: translateY(-12px); border-color: var(--primary); box-shadow: var(--shadow-lg); background: #fff; }
        .card-icon { 
            width: 72px; height: 72px; border-radius: 20px; background: rgba(20, 184, 166, 0.1);
            display: flex; align-items: center; justify-content: center; margin-bottom: 32px;
            font-size: 36px; color: var(--primary);
        }
        .card h3 { font-size: 24px; margin-bottom: 16px; font-weight: 800; letter-spacing: -0.5px; }
        .card p { color: var(--text-muted); font-size: 16px; font-weight: 500; }
        
        /* Contact Cards */
        .contact { background: var(--bg); }
        .contact-grid { display: flex; justify-content: center; gap: 32px; flex-wrap: wrap; }
        .contact-item { 
            background: #fff; padding: 40px; border-radius: var(--radius); border: 1px solid var(--border);
            text-align: center; min-width: 300px; transition: 0.3s; box-shadow: var(--shadow);
        }
        .contact-item:hover { transform: translateY(-8px); border-color: var(--primary); }
        .contact-item i { font-size: 48px; color: var(--primary); margin-bottom: 24px; padding: 20px; background: rgba(20, 184, 166, 0.05); border-radius: 50%; }
        .contact-item h4 { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
        .contact-item p { color: var(--text-muted); font-weight: 600; }
        
        /* Footer */
        footer { padding: 64px 8%; background: #fff; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .social-links { display: flex; gap: 16px; }
        .social-links a { width: 48px; height: 48px; border-radius: 16px; background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--text-muted); }
        .social-links a:hover { background: var(--primary); color: #fff; transform: translateY(-4px); }

        @media (max-width: 992px) {
            .hero { flex-direction: column; text-align: center; padding-top: 140px; }
            .hero-content { max-width: 100%; }
            .hero-content p { margin: 0 auto 40px; }
            .hero-buttons { justify-content: center; }
            .hero-content h1 { font-size: 50px; }
            .nav-links { display: none; }
            .hero-visual { margin-top: 40px; width: 100%; }
            .hero-logo-container img { max-width: 250px; }
        }
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 38px; }
            footer { flex-direction: column; gap: 32px; text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="/">
            <x-logo-brand size="md" />
        </a>
        <div class="nav-links">
            <a href="#hero">Home</a>
            <a href="#features">Why Learnme?</a>
            <a href="#contact">Contact Support</a>
        </div>
        <div class="auth-buttons">
            @auth
                @php
                    $dashRoute = auth()->user()->type === 'manager' ? 'admin.dashboard' : auth()->user()->type . '.dashboard';
                @endphp
                <a href="{{ route($dashRoute) }}" class="btn btn-primary"><i class='bx bxs-dashboard'></i> Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Join Free <i class='bx bx-right-arrow-alt'></i></a>
            @endauth
        </div>
    </nav>

    <section id="hero" class="hero">
        <div class="hero-content animate-up">
            <h1>Invest in your future, learn languages <span>smartly!</span></h1>
            <p>An elite interactive platform powered by AI and gamification, designed to unleash your potential and streamline excellence for schools and teachers.</p>
            <div class="hero-buttons">
                @guest
                    <a href="{{ route('register') }}" class="btn btn-primary" style="padding: 16px 40px; font-size: 17px;">Get Started Free</a>
                @else
                    <a href="{{ route($dashRoute) }}" class="btn btn-primary" style="padding: 16px 40px; font-size: 17px;">Continue Your Journey</a>
                @endguest
                <a href="#features" class="btn btn-ghost" style="padding: 16px 40px; font-size: 17px;">Explore Features</a>
            </div>
        </div>
        <div class="hero-visual animate-up" style="animation-delay: 0.2s;">
            <div class="hero-visual-wrapper">
                <div class="hero-blob"></div>
                <div class="hero-logo-container">
                    <img src="{{ asset('images/logo-learnme.png') }}" alt="Learnme Logo">
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="section-header animate-up">
            <h2>The Future of Smart Learning</h2>
            <p>A sophisticated ecosystem serving institutions and individual learners with cutting-edge technology.</p>
        </div>
        
        <div class="grid animate-up">
            <div class="card">
                <div class="card-icon"><i class='bx bx-sitemap'></i></div>
                <h3>Institutional Excellence</h3>
                <p>Advanced portal for schools to manage departments, educators, and results with comprehensive academic reporting.</p>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class='bx bxs-terminal'></i></div>
                <h3>Dynamic Studio</h3>
                <p>Proprietary tools for educators to craft immersive visual lessons and automated assessments with precision.</p>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class='bx bx-game'></i></div>
                <h3>Gamified Progress</h3>
                <p>Master skills through an addictive XP system. Level up, compete on leaderboards, and turn study into a victory.</p>
            </div>
  
            <div class="card">
                <div class="card-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                <h3>Vibrant Analytics</h3>
                <p>Real-time performance metrics (KPIs) providing instant clarity on progress and areas for strategic improvement.</p>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class='bx bx-devices'></i></div>
                <h3>Premium Experience</h3>
                <p>Fluid, responsive interfaces meticulously crafted for any device. Beautiful light and dark aesthetics for clear focus.</p>
            </div>
            
            <div class="card">
                <div class="card-icon"><i class='bx bx-trophy'></i></div>
                <h3>Elite Challenges</h3>
                <p>Timed, high-stakes competitions designed to push your boundaries and solidify language mastery through practice.</p>
            </div>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="section-header animate-up">
            <h2>Connect with Us</h2>
            <p>Our dedicated team is ready to support your educational success and answer any technical inquiries.</p>
        </div>
        
        <div class="contact-grid animate-up">
            <div class="contact-item">
                <i class='bx bx-envelope'></i>
                <h4>Technical Support</h4>
                <p>support@learnme.com</p>
            </div>
            
            <div class="contact-item">
                <i class='bx bx-phone-call'></i>
                <h4>Direct Line</h4>
                <p>+966 50 123 4567</p>
            </div>
            
            <div class="contact-item">
                <i class='bx bx-map-pin'></i>
                <h4>Strategic Presence</h4>
                <p>Riyadh, Saudi Arabia</p>
            </div>
        </div>
    </section>

    <footer>
        <a href="/">
            <x-logo-brand size="sm" />
        </a>
        <div style="font-weight: 600;">&copy; {{ date('Y') }} Learnme Platform. Excellence in Learning.</div>
        <div class="social-links">
            <a href="#"><i class='bx bxl-twitter'></i></a>
            <a href="#"><i class='bx bxl-instagram'></i></a>
            <a href="#"><i class='bx bxl-linkedin'></i></a>
        </div>
    </footer>

</body>
</html>
