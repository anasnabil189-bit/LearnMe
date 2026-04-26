@extends('layouts.app')

@section('title', 'Manage Languages')
@section('page-title', 'Languages & Localization')

@section('topbar-actions')
    <a href="{{ route('admin.languages.create') }}" class="btn btn-primary">
        <i class='bx bx-plus'></i> Add New Language
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">All Languages</h2>
    </div>

    @if($languages->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">Name</th>
                        <th style="background: none; border: none;">Code</th>
                        <th style="background: none; border: none;">Status</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $lang)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px; font-weight: 700; color: var(--text);">
                            {{ $lang->name }}
                        </td>
                        <td style="font-family: monospace; color: var(--accent);">
                            {{ $lang->code }}
                        </td>
                        <td>
                            <span class="badge badge-success">Active</span>
                        </td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('admin.languages.edit', $lang->id) }}" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px;">
                                    <i class='bx bx-edit' style="color: var(--primary-light);"></i>
                                </a>
                                <form action="{{ route('admin.languages.destroy', $lang->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this language? ALL levels/lessons in this language will become inaccessible.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-ghost" style="padding: 4px 8px; border-radius: 6px;">
                                        <i class='bx bx-trash' style="color: var(--danger);"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-globe'></i>
            <h3>No languages added yet</h3>
            <p>Start by adding languages you want to offer to your learners.</p>
        </div>
    @endif
</div>
@endsection
