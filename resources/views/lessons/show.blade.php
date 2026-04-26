@extends('layouts.app')

@section('title', 'View Lesson')
@section('page-title', 'Learning Path & Curriculum')

@section('topbar-actions')
    @php $isAdminLike = in_array(auth()->user()->type, ['admin', 'manager']); @endphp
    @if($isAdminLike || auth()->user()->type === 'teacher')
    <a href="{{ route(($isAdminLike ? 'admin' : 'teacher') . '.lessons.edit', $lesson->id) }}" class="btn btn-primary">
        <i class='bx bx-edit'></i> Edit Lesson
    </a>
    @endif
    <a href="{{ auth()->user()->type === 'user'
        ? ($lesson->grade_id ? route('user.teacher_content', ['teacher' => $lesson->user_id, 'language' => $lesson->school_language_id]) : route('user.levels.show', $lesson->level_id))
        : route(($isAdminLike ? 'admin' : 'teacher') . '.lessons.index') }}" class="btn btn-ghost">
        <i class='bx bx-left-arrow-alt'></i> Back to Curriculum
    </a>
@endsection

@section('content')
<div class="card" style="max-width: 900px; margin: 0 auto; overflow: hidden; padding: 0;">

    {{-- Lesson Header --}}
    <div style="background: linear-gradient(135deg, var(--bg2), var(--bg3)); padding: 40px; border-bottom: 1px solid var(--border);">
        <h1 style="font-size: 32px; font-weight: 800; color: var(--text); margin-bottom: 15px;">{{ $lesson->title }}</h1>
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted);">
                <i class='bx bx-book' style="color: var(--primary-light);"></i>
                <span>Path:</span>
                <span class="badge {{ $lesson->grade_id ? 'badge-primary' : 'badge-accent' }}">
                    {{ $lesson->grade->name ?? $lesson->level->name ?? 'Not Specified' }}
                </span>
            </div>
            <div style="width: 1px; height: 15px; background: var(--border);"></div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 14px; color: var(--text-muted);">
                <i class='bx bx-time'></i>
                <span>{{ $lesson->created_at->format('Y-m-d') }}</span>
            </div>
            @if($lesson->blocks->count() > 0)
            <span class="badge badge-info" style="margin-left:auto;">
                {{ $lesson->blocks->count() }} Content Blocks
            </span>
            @endif
        </div>
    </div>

    {{-- Lesson Video --}}
    @if($lesson->video_url)
    <div style="padding: 30px 40px 0;">
        @php
            $videoId = '';
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $lesson->video_url, $match)) {
                $videoId = $match[1];
            }
        @endphp
        @if($videoId)
        <div style="border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div style="position: relative; padding-bottom: 56.25%; height: 0;">
                <iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                    src="https://www.youtube.com/embed/{{ $videoId }}"
                    frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
        @else
        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-primary" style="width:100%; justify-content:center; padding:14px;">
            Watch Lesson Video <i class='bx bx-video'></i>
        </a>
        @endif
    </div>
    @endif

    {{-- Blocks Content --}}
    <div style="padding: 40px;" id="lesson-content">

        @php $imageIndex = 0; $allImagePaths = $lesson->blocks->where('type','image')->pluck('path')->values(); @endphp

        @forelse($lesson->blocks as $block)

            @if($block->type === 'text')
            {{-- Text Block --}}
            <div class="lesson-text-block" style="font-size: 18px; line-height: 1.9; color: var(--text); white-space: pre-line; margin-bottom: 30px;">
                {!! nl2br(e($block->content)) !!}
            </div>

            @elseif($block->type === 'image' && $block->path)
            {{-- Image Block --}}
            @php $imgIdx = $imageIndex++; @endphp
            <div style="margin-bottom: 30px; text-align: center;"
                 onclick="openLightbox({{ $imgIdx }})" style="cursor: zoom-in;">
                <div class="lesson-img-card" style="display:inline-block; max-width:100%; border-radius:16px; overflow:hidden;
                     border: 2px solid var(--border); cursor:zoom-in; position:relative;
                     box-shadow: 0 8px 24px rgba(0,0,0,0.2); transition: all 0.25s;">
                    <img src="{{ asset('storage/' . $block->path) }}"
                         alt="Illustrative Image"
                         style="max-width: 100%; max-height: 480px; object-fit: contain; display: block;"
                         onclick="openLightbox({{ $imgIdx }})">
                    <div class="img-zoom-hint" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%);
                         background:rgba(0,0,0,0.6); color:white; padding:5px 14px; border-radius:99px; font-size:12px;
                         display:flex; align-items:center; gap:5px; backdrop-filter:blur(4px); pointer-events:none;">
                        <i class='bx bx-zoom-in'></i> Click to Zoom
                    </div>
                </div>
            </div>
            @endif

        @empty
            <div class="empty-state">
                <i class='bx bx-book-open'></i>
                <h3>No content yet</h3>
                <p>No explanation has been added for this lesson yet.</p>
            </div>
        @endforelse
    </div>

    {{-- Student Footer --}}
    @if(auth()->user()->type === 'user')
    <div style="background: var(--bg2); padding: 30px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h4 style="margin-bottom: 5px;">Well done studying this content! 🎉</h4>
            <p style="font-size: 14px; color: var(--text-muted);">You can return to the path or start the quiz if available.</p>
        </div>
        <a href="{{ $lesson->grade_id ? route('user.teacher_content', ['teacher' => $lesson->user_id, 'language' => $lesson->school_language_id]) : route('user.levels.show', $lesson->level_id) }}"
           class="btn btn-primary">Complete Lesson & Return <i class='bx bx-check-double'></i></a>
</div>
@endif
</div>

{{-- Lightbox --}}
@if($allImagePaths->count() > 0)
<div id="lightbox" onclick="closeLightbox()" style="display:none; position:fixed; inset:0; z-index:9999;
     background:rgba(0,0,0,0.93); backdrop-filter:blur(8px);
     align-items:center; justify-content:center; flex-direction:column;">

    <div onclick="event.stopPropagation()" style="position:absolute; top:20px; left:0; right:0;
         display:flex; justify-content:center; align-items:center; gap:10px; flex-wrap:wrap; padding:0 20px;">
        <button onclick="zoomOut()" class="lb-btn"><i class='bx bx-zoom-out'></i></button>
        <span id="zoom-level" style="color:white; font-size:14px; min-width:48px; text-align:center;">100%</span>
        <button onclick="zoomIn()" class="lb-btn"><i class='bx bx-zoom-in'></i></button>
        <div style="width:1px; height:24px; background:rgba(255,255,255,0.2);"></div>
        <button onclick="prevImage()" class="lb-btn" style="padding:0 18px; border-radius:8px;"><i class='bx bx-chevron-left'></i> Prev</button>
        <span id="img-counter" style="color:rgba(255,255,255,0.6); font-size:13px;">1 / {{ $allImagePaths->count() }}</span>
        <button onclick="nextImage()" class="lb-btn" style="padding:0 18px; border-radius:8px;">Next <i class='bx bx-chevron-right'></i></button>
        <div style="width:1px; height:24px; background:rgba(255,255,255,0.2);"></div>
        <button onclick="closeLightbox()" class="lb-btn" style="background:rgba(239,68,68,0.3); border-color:rgba(239,68,68,0.5);"><i class='bx bx-x'></i></button>
    </div>

    <div style="overflow:hidden; display:flex; align-items:center; justify-content:center;
                width:100%; height:100%; padding-top:70px; padding-bottom:20px;">
        <img id="lightbox-img" src="" alt="" onclick="event.stopPropagation()"
             style="max-width:95%; max-height:90%; object-fit:contain; transform-origin:center;
                    transition: transform 0.2s; border-radius:10px; cursor:grab;">
    </div>
</div>

<script>
    const lbImages = @json($allImagePaths);
    let lbCurrent = 0, lbScale = 1;

    function openLightbox(idx) {
        lbCurrent = idx; lbScale = 1;
        updateLb();
        document.getElementById('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').style.display = 'none';
        document.body.style.overflow = '';
        lbScale = 1;
    }
    function updateLb() {
        const img = document.getElementById('lightbox-img');
        img.src = '/storage/' + lbImages[lbCurrent];
        img.style.transform = `scale(${lbScale})`;
        document.getElementById('zoom-level').textContent = Math.round(lbScale * 100) + '%';
        document.getElementById('img-counter').textContent = (lbCurrent + 1) + ' / ' + lbImages.length;
    }
    function zoomIn()    { lbScale = Math.min(lbScale + 0.25, 4); updateLb(); }
    function zoomOut()   { lbScale = Math.max(lbScale - 0.25, 0.25); updateLb(); }
    function nextImage() { lbCurrent = (lbCurrent + 1) % lbImages.length; lbScale = 1; updateLb(); }
    function prevImage() { lbCurrent = (lbCurrent - 1 + lbImages.length) % lbImages.length; lbScale = 1; updateLb(); }

    document.addEventListener('keydown', e => {
        if (document.getElementById('lightbox').style.display === 'flex') {
            if (e.key === 'Escape')      closeLightbox();
            if (e.key === 'ArrowRight')  nextImage();
            if (e.key === 'ArrowLeft')   prevImage();
            if (e.key === '+')           zoomIn();
            if (e.key === '-')           zoomOut();
        }
    });
    document.getElementById('lightbox-img').addEventListener('wheel', e => {
        e.preventDefault();
        e.deltaY < 0 ? zoomIn() : zoomOut();
    }, { passive: false });
</script>
@endif

<style>
.lesson-img-card:hover { border-color: var(--primary-light) !important; transform: translateY(-3px); box-shadow: 0 14px 32px rgba(0,0,0,0.3) !important; }
.lb-btn {
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;
    width: 40px; height: 40px; border-radius: 50%; font-size: 18px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.lb-btn:hover { background: rgba(255,255,255,0.25); }
</style>
@endsection
