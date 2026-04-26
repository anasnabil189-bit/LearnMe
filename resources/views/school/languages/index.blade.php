@extends('layouts.app')

@section('title', 'Manage School Languages')
@section('page-title', 'School Languages')

@section('topbar-actions')
    <a href="{{ route('school.school-languages.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Language
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">School Languages Overview</h2>
    </div>

    @if($languages->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">#</th>
                        <th style="background: none; border: none;">Language Name</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $lang)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px;">{{ $loop->iteration }}</td>
                        <td style="font-weight: 700;">{{ $lang->name }}</td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route('school.school-languages.edit', $lang->id) }}" class="btn btn-sm btn-ghost"><i class='bx bx-edit' style="color: var(--accent);"></i></a>
                            <form action="{{ route('school.school-languages.destroy', $lang->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this language from your school?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost"><i class='bx bx-trash' style="color: var(--danger);"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-globe'></i>
            <h3>No Languages Found</h3>
            <p>Setup the languages taught at your school.</p>
            <a href="{{ route('school.school-languages.create') }}" class="btn btn-primary" style="margin-top: 15px;">Add First Language</a>
        </div>
    @endif
</div>
@endsection
