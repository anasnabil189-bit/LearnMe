@extends('layouts.app')

@section('title', 'Student Management')
@section('page-title', 'Students List')

@section('topbar-actions')
    {{-- School admins cannot add students directly --}}
@endsection

@section('content')
<div class="students-dashboard">
    <div class="page-header" style="margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--text); letter-spacing: -0.5px;">
                @if(isset($viewTitle))
                    {{ $viewTitle }}
                @else
                    Students Management
                @endif
            </h1>
            <p style="color: var(--text-muted); font-size: 15px;">
                @if(isset($viewType) && $viewType === 'school')
                    Managing all registered institutional learners.
                @elseif(isset($viewType) && $viewType === 'course')
                    Managing independent course students.
                @else
                    List of students in your school.
                @endif
            </p>
        </div>
    </div>

    <div class="card" style="border-radius: 20px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-md);">
        @if($students->count() > 0)
            <div class="table-wrap">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: rgba(var(--primary-rgb), 0.05);">
                            <th style="padding: 15px 25px;">#</th>
                            <th>Student Code</th>
                            <th>Name</th>
                            @if(isset($viewType) && $viewType === 'school')
                                <th>School</th>
                            @endif
                            @if(!isset($viewType) || $viewType === 'school')
                                <th>Grade</th>
                            @endif
                            @if(isset($viewType) && $viewType === 'course')
                                <th>Email</th>
                            @endif
                            <th>Points (XP)</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr style="transition: background 0.2s;">
                            <td style="padding: 15px 25px;">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                            <td style="font-family: monospace; font-weight: bold; color: var(--primary);">{{ $student->id }}</td>
                            <td style="font-weight: 600;">{{ $student->name }}</td>
                            
                            @if(isset($viewType) && $viewType === 'school')
                            <td>
                                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 6px 12px; border-radius: 20px; font-size: 12px;">
                                    <i class='bx bxs-school'></i> {{ optional($student->school)->name ?? 'Not Assigned' }}
                                </span>
                            </td>
                            @endif

                            @if(!isset($viewType) || $viewType === 'school')
                            <td>
                                @foreach($student->gradesAsStudent as $grade)
                                    <span class="badge badge-accent">{{ $grade->name }}</span>
                                @endforeach
                                @if($student->gradesAsStudent->isEmpty())
                                    <span style="color:var(--text-muted); font-size:12px;">Not Assigned</span>
                                @endif
                            </td>
                            @endif

                            @if(isset($viewType) && $viewType === 'course')
                            <td style="font-family: monospace; color: var(--text-muted);">{{ $student->email }}</td>
                            @endif

                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class='bx bxs-star' style="color: var(--accent); font-size: 18px;"></i>
                                    <span style="font-weight: 800; color: var(--text);">{{ $student->userLanguages->sum('learning_xp') + ($student->challenge_xp ?? 0) }} XP</span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 10px;">
                                    @php 
                                        $prefix = auth()->user()->type === 'manager' ? 'admin' : auth()->user()->type; 
                                        // Ensure prefix is consistent with routes
                                        if (auth()->user()->isAdmin()) $prefix = 'admin';
                                    @endphp
                                    <a href="{{ route($prefix . '.students.show', $student->id) }}" class="action-btn" title="View"><i class='bx bx-show'></i></a>
                                    
                                    @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isSchool())
                                    <form action="{{ route($prefix . '.students.destroy', $student->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn" style="--color: var(--danger); border:none; background:transparent;"><i class='bx bx-trash'></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 20px;">{{ $students->links() }}</div>
        @else
            <div class="empty-state" style="padding: 80px 20px;">
                <div style="width: 100px; height: 100px; background: rgba(var(--primary-rgb), 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class='bx bx-group' style="font-size: 50px; color: var(--primary); opacity: 0.5;"></i>
                </div>
                <h3>No students found.</h3>
                <p style="color: var(--text-muted);">There are no students registered in this category yet.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .tab-active {
        background: var(--primary) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(20, 184, 166, 0.2) !important;
    }
    
    .tab-inactive {
        background: transparent !important;
        color: var(--text-muted) !important;
    }
    
    .tab-inactive:hover {
        background: #f1f5f9 !important;
        color: var(--primary) !important;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--color, var(--primary-rgb)), 0.1);
        color: var(--color, var(--primary));
        transition: all 0.2s;
        cursor: pointer;
        font-size: 18px;
    }

    .action-btn:hover {
        background: var(--color, var(--primary));
        color: white;
        transform: translateY(-2px);
    }

    .table-wrap tr:hover {
        background: rgba(var(--primary-rgb), 0.02);
    }

    .fade-in {
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
