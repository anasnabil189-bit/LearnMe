@extends('layouts.app')

@section('title', 'Manage ' . $entityType)
@section('page-title', $pageTitle)

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Select a Language for {{ $entityType }}</h2>
        <p style="color:var(--text-muted); font-size:14px; margin-top:5px;">Manage specific {{ strtolower($entityType) }} organized by language.</p>
    </div>

    @if($languages->count() > 0)
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="background: none; border: none;">Language</th>
                        <th style="background: none; border: none;">Number of {{ $entityType }}</th>
                        <th style="background: none; border: none;">Number of Users Learning</th>
                        <th style="background: none; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $language)
                    <tr style="border-bottom: 8px solid var(--bg); background: var(--bg2); border-radius: 12px;">
                        <td style="border-radius: 12px 0 0 12px;">
                            <div style="display: flex; align-items: center; gap: 8px; font-weight: 700;">
                                <i class='bx bx-globe' style="color: var(--primary);"></i>
                                {{ $language->name }}
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--accent);">{{ $language->{$entityCountAttr} }}</strong> {{ strtolower($entityType) }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class='bx bx-group' style="color: var(--info);"></i>
                                {{ $language->user_languages_count }} Users
                            </div>
                        </td>
                        <td style="border-radius: 0 12px 12px 0;">
                            <a href="{{ route($manageRouteName, ['language_id' => $language->id]) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 14px; border-radius: 6px; font-weight: 600;">
                                <i class='bx bx-cog'></i> Manage
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class='bx bx-globe'></i>
            <h3>No languages found</h3>
            <p>Please add some languages first in the system.</p>
        </div>
    @endif
</div>
@endsection
