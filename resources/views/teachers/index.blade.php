@extends('layouts.app')

@section('title', 'Teacher Management')
@section('page-title', 'Teachers List')

@section('topbar-actions')
    @if(!auth()->user()->isAdmin())
    <a href="{{ route(auth()->user()->type . '.teachers.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add Teacher
    </a>
    @endif
@endsection

@section('content')
<div class="teachers-dashboard">
    <div class="page-header" style="margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--text); letter-spacing: -0.5px;">
                @if(isset($school))
                    Teachers of {{ $school->name }}
                @elseif(isset($schools))
                    Select School to View Teachers
                @else
                    Teacher Management
                @endif
            </h1>
            <p style="color: var(--text-muted); font-size: 15px;">
                @if(isset($school))
                    Showing all registered instructors in this school.
                @elseif(isset($schools))
                    Browse and manage teachers organized by their respective schools.
                @else
                    Managing all registered teachers in your school.
                @endif
            </p>
        </div>
    </div>

    {{-- View for Admin: Schools List --}}
    @if(isset($schools))
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
        @foreach($schools as $s)
        <div class="card stat-card" style="padding: 24px; position: relative; border-radius: 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--text); margin-bottom: 4px;">{{ $s->name }}</h3>
                    <p style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                        Code: <span style="font-family: monospace; color: var(--primary);">{{ $s->code }}</span>
                    </p>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class='bx bxs-school'></i>
                </div>
            </div>
            
            <div style="margin-top: 20px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span style="font-size: 24px; font-weight: 900; color: var(--text);">{{ $s->teachers_count }}</span>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-left: 4px;">Teachers</span>
                </div>
                <a href="{{ route('admin.schools.teachers', $s->id) }}" class="btn btn-primary btn-sm" style="border-radius: 10px; padding: 10px 18px;">
                    View Teachers <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top: 30px;">{{ $schools->links() }}</div>

    {{-- View for Teachers List (School Admin or Specific School View for Admin) --}}
    @else
    <div class="card" style="border-radius: 20px; overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-md);">
        @if($teachers->count() > 0)
            <div class="table-wrap">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: rgba(var(--primary-rgb), 0.05);">
                            <th style="padding: 15px 25px;">#</th>
                            <th>Name</th>
                            <th>Teacher Code</th>
                            <th>Email</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $teacher)
                        <tr style="transition: background 0.2s;">
                            <td style="padding: 15px 25px;">{{ $loop->iteration }}</td>
                            <td style="font-weight: 600;">{{ $teacher->name }}</td>
                            <td style="font-family: monospace; font-weight: bold; color: var(--primary);">{{ $teacher->teacher_code }}</td>
                            <td style="color: var(--text-muted);">{{ $teacher->email }}</td>
                            <td>
                                <div style="display: flex; justify-content: center; gap: 10px;">
                                    @php $prefix = auth()->user()->type === 'admin' ? 'admin' : 'school'; @endphp
                                    <a href="{{ route($prefix . '.teachers.show', $teacher->id) }}" class="action-btn" title="View"><i class='bx bx-show'></i></a>
                                    
                                    @if(auth()->user()->isSchool())
                                    <a href="{{ route($prefix . '.teachers.edit', $teacher->id) }}" class="action-btn" style="--color: var(--accent-rgb)" title="Edit"><i class='bx bx-edit'></i></a>
                                    <form action="{{ route($prefix . '.teachers.destroy', $teacher->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="action-btn" style="--color: var(--danger-rgb); border:none; background:transparent;"><i class='bx bx-trash'></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 20px;">{{ $teachers->links() }}</div>
        @else
            <div class="empty-state" style="padding: 80px 20px;">
                <div style="width: 100px; height: 100px; background: rgba(var(--primary-rgb), 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class='bx bx-user-voice' style="font-size: 50px; color: var(--primary); opacity: 0.5;"></i>
                </div>
                <h3>No Teachers found.</h3>
                <p style="color: var(--text-muted);">There are no instructors registered for this selection yet.</p>
            </div>
        @endif
    </div>
    @endif
</div>

<style>
    .action-btn {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(var(--color, var(--primary-rgb)), 0.1);
        color: rgba(var(--color, var(--primary-rgb)), 1);
        transition: all 0.2s;
        cursor: pointer;
        font-size: 18px;
    }

    .action-btn:hover {
        background: rgba(var(--color, var(--primary-rgb)), 1);
        color: white;
        transform: translateY(-2px);
    }
</style>
@endsection
