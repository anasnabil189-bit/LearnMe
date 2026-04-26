<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Learnme - Interactive Multi-Language Learning Platform">
    <title>@yield('title', 'Learnme') — Learning Platform</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts (Modern Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- BoxIcons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-learnme.png') }}">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:       #14b8a6; /* Sea Teal */
            --primary-light: #2dd4bf;
            --primary-dark:  #0f766e;
            --accent:        #f59e0b; /* Amber */
            --danger:        #ef4444;
            --success:       #10b981;
            --info:          #3b82f6;
            --bg:            #f8fafc; /* Whisper Gray */
            --bg2:           #ffffff; /* Pure White */
            --bg3:           #f1f5f9;
            --surface:       #ffffff;
            --surface2:      #f9fafb;
            --border:        #e2e8f0;
            --text:          #0f172a; /* Deep Slate */
            --text-muted:    #64748b;
            --sidebar-w:     280px;  /* Slightly wider for prominence */
            --topbar-h:      72px;  /* Taller topbar */
            --radius:        16px;  /* More rounded */
            --shadow:        0 10px 40px rgba(0,0,0,0.06);
            --shadow-lg:     0 20px 50px rgba(0,0,0,0.1);
        }

        /* Entry Animation */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-up {
            animation: slideUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) both;
        }

        html, body { height: 100%; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            direction: ltr;
            display: flex;
            min-height: 100vh;
            font-size: 15px; /* Increased base size */
            line-height: 1.6;
        }

        /* =========================================================
           SIDEBAR
        ========================================================= */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--bg2);
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            padding: 32px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--border);
            margin-bottom: 10px;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 10px 16px; }

        .nav-section-title {
            font-size: 11px; font-weight: 700; letter-spacing: 1.2px;
            color: var(--text-muted); text-transform: uppercase;
            padding: 20px 12px 8px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px;
            border-radius: 12px;
            color: #475569;
            text-decoration: none;
            font-size: 15px; font-weight: 600;
            margin-bottom: 4px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .nav-item:hover  { background: #f1f5f9; color: var(--primary); transform: translateX(5px); }
        .nav-item.active { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; box-shadow: 0 8px 20px rgba(20,184,166,0.25); }
        .nav-item i { font-size: 22px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }
        .user-card {
            display: flex; align-items: center; gap: 11px;
            padding: 10px 12px;
            background: var(--bg3);
            border-radius: 10px;
        }
        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px; color: #fff; flex-shrink: 0;
        }
        .user-name  { font-size: 13.5px; font-weight: 600; color: var(--text); }
        .user-role  { font-size: 11px; color: var(--text-muted); }

        /* =========================================================
           TOPBAR
        ========================================================= */
        .topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: var(--topbar-h);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 40px;
            z-index: 99;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .topbar-title { font-size: 20px; font-weight: 800; color: var(--text); letter-spacing: -0.5px; }
        .topbar-actions { display: flex; align-items: center; gap: 12px; }

        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; border-radius: 12px; font-size: 15px;
            font-weight: 700; font-family: 'Inter', sans-serif;
            cursor: pointer; border: none; text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); white-space: nowrap;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; box-shadow: 0 10px 20px rgba(20,184,166,0.2); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(20,184,166,0.3); }
        .btn-danger  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fecaca; transform: translateY(-2px); }
        .btn-ghost   { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }
        .btn-ghost:hover { background: #f1f5f9; color: var(--primary); transform: translateY(-2px); }
        .btn-accent  { background: var(--accent); color: #fff; box-shadow: 0 10px 20px rgba(245,158,11,0.2); }
        .btn-sm { padding: 8px 16px; font-size: 14px; }

        /* =========================================================
           MAIN CONTENT
        ========================================================= */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            flex: 1;
            min-height: calc(100vh - var(--topbar-h));
            padding: 32px 36px;
        }

        /* =========================================================
           CARDS & STATS
        ========================================================= */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: var(--shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); }
        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px;
        }
        .card-title { font-size: 20px; font-weight: 800; color: var(--text); letter-spacing: -0.5px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            display: flex; flex-direction: column; gap: 12px;
            position: relative; overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }
        .stat-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; width: 6px; height: 100%;
            background: var(--primary);
        }
        .stat-card.accent::before { background: var(--accent); }
        .stat-card.info::before   { background: var(--info); }
        .stat-card.success::before { background: var(--success); }
        .stat-icon { font-size: 32px; color: var(--primary); opacity: 0.8; }
        .stat-card.accent .stat-icon { color: var(--accent); }
        .stat-card.info .stat-icon   { color: var(--info); }
        .stat-card.success .stat-icon { color: var(--success); }
        .stat-value { font-size: 36px; font-weight: 900; color: var(--text); letter-spacing: -1px; }
        .stat-label { font-size: 14px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* =========================================================
           TABLE
        ========================================================= */
        .table-wrap { 
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden; 
            box-shadow: var(--shadow);
        }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th {
            background: #f8fafc; color: #475569;
            font-size: 13px; font-weight: 700; letter-spacing: 1px;
            padding: 18px 24px; text-align: left;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 18px 24px; font-size: 15px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            transition: background 0.2s;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fbfcfd; }

        /* =========================================================
           FORMS
        ========================================================= */
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13.5px; font-weight: 600; color: var(--text-muted); margin-bottom: 7px; }
        input[type="text"], input[type="email"], input[type="password"],
        input[type="date"], input[type="number"], input[type="url"],
        select, textarea {
            width: 100%; padding: 11px 14px;
            background: var(--bg3); border: 1px solid var(--border);
            border-radius: 9px; color: var(--text);
            font-family: 'Inter', sans-serif; font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(20,184,166,0.15);
        }
        textarea { min-height: 100px; resize: vertical; }
        .form-error { color: #f87171; font-size: 12px; margin-top: 5px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* =========================================================
           BADGES
        ========================================================= */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 99px;
            font-size: 12px; font-weight: 600;
        }
        .badge-primary { background: rgba(15,118,110,0.2); color: var(--primary-light); }
        .badge-accent  { background: rgba(245,158,11,0.2); color: var(--accent); }
        .badge-success { background: rgba(34,197,94,0.2);  color: var(--success); }
        .badge-danger  { background: rgba(239,68,68,0.2);  color: var(--danger); }
        .badge-info    { background: rgba(59,130,246,0.2); color: var(--info); }

        /* =========================================================
           ALERTS
        ========================================================= */
        .alert {
            padding: 13px 16px; border-radius: 9px; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px; font-size: 14px;
        }
        .alert-success { background: rgba(34,197,94,0.1);  border: 1px solid rgba(34,197,94,0.3);  color: #86efac; }
        .alert-danger  { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.3);  color: #fca5a5; }
        .alert-info    { background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.3); color: #93c5fd; }

        /* =========================================================
           PAGINATION
        ========================================================= */
        .pagination { display: flex; gap: 6px; justify-content: center; margin-top: 24px; }
        .pagination a, .pagination span {
            padding: 8px 13px; border-radius: 8px; font-size: 13px;
            background: var(--bg3); color: var(--text-muted);
            text-decoration: none; border: 1px solid var(--border);
            transition: all 0.2s;
        }
        .pagination a:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination .active span { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* =========================================================
           EMPTY STATE
        ========================================================= */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--text-muted);
        }
        .empty-state i   { font-size: 52px; margin-bottom: 14px; opacity: 0.4; }
        .empty-state h3  { font-size: 18px; font-weight: 600; margin-bottom: 6px; }
        .empty-state p   { font-size: 14px; }

        /* =========================================================
           PAGE HEADER
        ========================================================= */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
        }
        .page-header h1 { font-size: 24px; font-weight: 800; color: var(--text); }
        .page-header p  { font-size: 14px; color: var(--text-muted); margin-top: 3px; }

        /* =========================================================
           SCROLLBAR
        ========================================================= */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

        /* =========================================================
           MOBILE
        ========================================================= */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; padding: 20px 16px; }
            .topbar { left: 0; }
            .form-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .mobile-logo { display: flex !important; }
        }


    </style>

    @stack('styles')
</head>
<body>

{{-- =================== SIDEBAR =================== --}}
@auth
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('home') }}">
            <x-logo-brand size="md" />
        </a>
    </div>

    <nav class="sidebar-nav">
        {{-- Admin --}}
        @if(auth()->user()->isAdmin())
        <div class="nav-section-title">Administration</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        <a href="{{ route('admin.schools.index') }}" class="nav-item {{ request()->routeIs('admin.schools.*') ? 'active' : '' }}">
            <i class='bx bxs-school'></i> Schools
        </a>
        
        {{-- Admin Students Dropdown --}}
        <div x-data="{ open: {{ request()->routeIs('admin.students.*') ? 'true' : 'false' }} }" class="nav-dropdown">
            <button @click="open = !open" class="nav-item" style="width: 100%; justify-content: space-between; background: transparent; border: none; cursor: pointer;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class='bx bxs-graduation'></i> 
                    <span>Students</span>
                </div>
                <i class='bx' :class="open ? 'bx-chevron-up' : 'bx-chevron-down'"></i>
            </button>
            <div x-show="open" x-transition style="padding-left: 20px; display: flex; flex-direction: column; gap: 2px; margin-top: 2px;">
                <a href="{{ route('admin.students.courses') }}" class="nav-item {{ request()->routeIs('admin.students.courses') ? 'active' : '' }}" style="font-size: 13.5px; padding: 8px 16px;">
                    <i class='bx bx-user' style="font-size: 18px;"></i> Course Learners
                </a>
                <a href="{{ route('admin.students.schools') }}" class="nav-item {{ request()->routeIs('admin.students.schools') ? 'active' : '' }}" style="font-size: 13.5px; padding: 8px 16px;">
                    <i class='bx bxs-school' style="font-size: 18px;"></i> School Students
                </a>
            </div>
        </div>
        
        <a href="{{ route('admin.organizations.index') }}" class="nav-item {{ request()->routeIs('admin.organizations.*') ? 'active' : '' }}">
            <i class='bx bx-buildings'></i> Organizations
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="nav-item {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
            <i class='bx bxs-user-badge'></i> Teachers
        </a>
        <a href="{{ route('admin.staff.index') }}" class="nav-item {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <i class='bx bxs-user-detail'></i> Manage Staff
        </a>
        @endif

        {{-- Manager --}}
        @if(auth()->user()->isManager())
        <div class="nav-section-title">Manager Tools</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        <a href="{{ route('admin.students.courses') }}" class="nav-item {{ request()->routeIs('admin.students.courses') ? 'active' : '' }}">
            <i class='bx bxs-user'></i> Course Learners
        </a>
        <a href="{{ route('admin.languages.index') }}" class="nav-item {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
            <i class='bx bx-world'></i> Manage Languages
        </a>
        <div class="nav-section-title" style="margin-top:12px">Course Management</div>
        <a href="{{ route('admin.levels.index') }}" class="nav-item {{ request()->routeIs('admin.levels.*') ? 'active' : '' }}">
            <i class='bx bxs-layer'></i> Levels
        </a>
        <a href="{{ route('admin.lessons.index') }}" class="nav-item {{ request()->routeIs('admin.lessons.*') ? 'active' : '' }}">
            <i class='bx bxs-book-content'></i> Lessons List
        </a>
        <a href="{{ route('admin.quizzes.index') }}" class="nav-item {{ request()->routeIs('admin.quizzes.*') ? 'active' : '' }}">
            <i class='bx bxs-edit-alt'></i> Quizzes List
        </a>
        <a href="{{ route('admin.results.index') }}" class="nav-item {{ request()->routeIs('admin.results.*') ? 'active' : '' }}">
            <i class='bx bxs-bar-chart-alt-2'></i> Student Results
        </a>
        <a href="{{ route('admin.leaderboard') }}" class="nav-item {{ request()->routeIs('admin.leaderboard') ? 'active' : '' }}">
            <i class='bx bxs-trophy' style="color: var(--accent);"></i> Global Leaderboard
        </a>
        @endif

        {{-- School --}}
        @if(auth()->user()->isSchool())
        <div class="nav-section-title">Academic Structure</div>
        <a href="{{ route('school.dashboard') }}" class="nav-item {{ request()->routeIs('school.dashboard') ? 'active' : '' }}">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        <a href="{{ route('school.grades.index') }}" class="nav-item {{ request()->routeIs('school.grades.*') ? 'active' : '' }}">
            <i class='bx bxs-layer'></i> Grades
        </a>
        <a href="{{ route('school.school-languages.index') }}" class="nav-item {{ request()->routeIs('school.school-languages.*') ? 'active' : '' }}">
            <i class='bx bx-world'></i> School Languages
        </a>
        <a href="{{ route('school.teacher-assignments.index') }}" class="nav-item {{ request()->routeIs('school.teacher-assignments.*') ? 'active' : '' }}">
            <i class='bx bxs-user-detail'></i> Teacher Assignments
        </a>

        <div class="nav-section-title" style="margin-top:12px">School Users</div>
        <a href="{{ route('school.teachers.index') }}" class="nav-item {{ request()->routeIs('school.teachers.*') && !request()->routeIs('school.teacher-assignments.*') ? 'active' : '' }}">
            <i class='bx bxs-user-badge'></i> Teachers List
        </a>
        <a href="{{ route('school.students.index') }}" class="nav-item {{ request()->routeIs('school.students.*') ? 'active' : '' }}">
            <i class='bx bxs-graduation'></i> Students
        </a>
        <a href="{{ route('school.leaderboard') }}" class="nav-item {{ request()->routeIs('school.leaderboard') ? 'active' : '' }}">
            <i class='bx bxs-trophy' style="color: var(--accent);"></i> Leaderboard
        </a>
        @endif

        {{-- Teacher --}}
        @if(auth()->user()->isTeacher())
        <div class="nav-section-title">Content</div>
        <a href="{{ route('teacher.dashboard') }}" class="nav-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        <a href="{{ route('teacher.lessons.index') }}" class="nav-item {{ request()->routeIs('teacher.lessons.*') ? 'active' : '' }}">
            <i class='bx bxs-book-content'></i> Lessons
        </a>
        <a href="{{ route('teacher.quizzes.index') }}" class="nav-item {{ request()->routeIs('teacher.quizzes.*') ? 'active' : '' }}">
            <i class='bx bxs-edit-alt'></i> Quizzes
        </a>
        <a href="{{ route('teacher.results.index') }}" class="nav-item {{ request()->routeIs('teacher.results.*') ? 'active' : '' }}">
            <i class='bx bxs-bar-chart-alt-2'></i> Student Results
        </a>
        @endif

        {{-- User --}}
        @if(auth()->user()->isUser())
        <div class="nav-section-title">Learning</div>
        <a href="{{ route('user.dashboard') }}" class="nav-item {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
            <i class='bx bxs-dashboard'></i> Dashboard
        </a>
        
        <div class="nav-section-title" style="margin-top: 10px;">Progress</div>
        <a href="{{ route('user.results.index') }}" class="nav-item {{ request()->routeIs('user.results.index') ? 'active' : '' }}">
            <i class='bx bxs-bar-chart-alt-2'></i> My Results
        </a>
        <a href="{{ route('leaderboard') }}" class="nav-item {{ request()->routeIs('leaderboard') ? 'active' : '' }}">
            <i class='bx bxs-trophy' style="color: var(--accent);"></i> 
            {{ auth()->user()->school_id ? 'School Leaderboard' : 'Courses Leaderboard' }}
        </a>
        <a href="{{ route('user.challenges.index') }}" class="nav-item {{ request()->routeIs('user.challenges.*') ? 'active' : '' }}">
            <i class='bx bxs-trophy'></i> Challenges
        </a>

        @if(!auth()->user()->school_id)
        <div class="nav-section-title" style="margin-top: 10px;">Languages</div>
        <a href="{{ route('user.languages.index') }}" class="nav-item {{ request()->routeIs('user.languages.index') ? 'active' : '' }}">
            <i class='bx bx-world'></i> Switch Language
        </a>
        @endif
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            <div style="flex:1; min-width:0;">
                <div class="user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                <div class="user-role">
                    @switch(auth()->user()->type)
                        @case('admin') Administrator @break
                        @case('manager') Manager @break
                        @case('school') School Admin @break
                        @case('teacher') Teacher @break
                        @case('user') Student @break
                    @endswitch
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:18px;" title="Logout">
                    <i class='bx bx-log-out'></i>
                </button>
            </form>
        </div>
    </div>
</aside>
@endauth

{{-- =================== TOPBAR =================== --}}
@auth
<header class="topbar">
    <div style="display: flex; align-items: center; gap: 12px;">
        <a href="{{ route('home') }}" class="mobile-logo" style="display: none;">
            <x-logo-brand size="sm" />
        </a>
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
    </div>
    <div class="topbar-actions">
        {{-- Language Switcher Dropdown (Courses Only) --}}
        @if(auth()->user()->isUser() && !auth()->user()->school_id)
        <div x-data="{ open: false }" class="relative" style="position: relative;">
            <button @click="open = !open" class="btn btn-ghost btn-sm">
                <i class='bx bx-world'></i>
                @php
                    $activeId = app(\App\Services\LanguageService::class)->getActiveLanguageId(auth()->user());
                    $activeLang = \App\Models\Language::find($activeId);
                @endphp
                {{ $activeLang ? $activeLang->name : 'Select Language' }}
                <i class='bx bx-chevron-down'></i>
            </button>
            <div x-show="open" @click.away="open = false" 
                 class="card" 
                 style="position: absolute; top: 100%; right: 0; min-width: 200px; z-index: 1000; padding: 10px; margin-top: 8px;">
                @foreach(\App\Models\Language::all() as $lang)
                    <form action="{{ route('user.languages.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="language_id" value="{{ $lang->id }}">
                        <button type="submit" class="nav-item" style="width: 100%; text-align: left; border: none; background: transparent; cursor: pointer; color: {{ $activeId == $lang->id ? 'var(--primary-light)' : 'var(--text-muted)' }}">
                            {{ $lang->name }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        @endif
        @yield('topbar-actions')
    </div>
</header>
@endauth

{{-- =================== MAIN =================== --}}
<main class="main-wrapper animate-up">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success"><i class='bx bx-check-circle' style="font-size:18px;flex-shrink:0"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger"><i class='bx bx-error-circle' style="font-size:18px;flex-shrink:0"></i> {{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info"><i class='bx bx-info-circle' style="font-size:18px;flex-shrink:0"></i> {!! session('info') !!}</div>
    @endif
    @if(session('fawry_code'))
        <div style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 20px rgba(249,115,22,0.3);">
            <div style="background: rgba(255,255,255,0.2); border-radius: 12px; padding: 14px; flex-shrink: 0;">
                <i class='bx bx-store' style="font-size: 32px;"></i>
            </div>
            <div style="flex: 1;">
                <p style="font-weight: 800; font-size: 16px; margin-bottom: 4px;">ادفع عبر فوري لإتمام تفعيل اشتراكك!</p>
                <p style="font-size: 13px; opacity: 0.85; margin-bottom: 12px;">توجه لأي منفذ فوري وأعطهم هذا الكود:</p>
                <div style="background: rgba(255,255,255,0.95); color: #ea580c; font-size: 28px; font-weight: 900; letter-spacing: 6px; padding: 12px 20px; border-radius: 10px; display: inline-block; font-family: monospace;">
                    {{ session('fawry_code') }}
                </div>
                <p style="font-size: 11px; opacity: 0.75; margin-top: 10px;">سيتم تفعيل حسابك تلقائياً بمجرد تأكيد الدفع من فوري.</p>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<script>
    // Mobile sidebar toggle
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const toggle  = document.getElementById('sidebar-toggle');
        if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    });
</script>

@stack('scripts')

@if(auth()->check() && auth()->user()->isUser() && !auth()->user()->school_id)
    @include('components.subscription-modal')
@endif

</body>
</html>
