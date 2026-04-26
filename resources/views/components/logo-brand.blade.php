@props(['size' => 'md', 'showText' => true])

@php
    $sizes = [
        'sm' => ['icon' => '36px', 'font' => '20px', 'gap' => '10px', 'star' => '12px'],
        'md' => ['icon' => '48px', 'font' => '28px', 'gap' => '14px', 'star' => '16px'],
        'lg' => ['icon' => '64px', 'font' => '36px', 'gap' => '18px', 'star' => '20px'],
        'xl' => ['icon' => '80px', 'font' => '44px', 'gap' => '22px', 'star' => '24px'],
    ];
    $current = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="learnme-logo-comp {{ $size }}" style="gap: {{ $current['gap'] }}">
    <div class="learnme-icon-box" style="width: {{ $current['icon'] }};">
        <img src="{{ asset('images/logo-learnme.png') }}" alt="Learnme Logo" class="learnme-img">
    </div>
    @if($showText)
        <div class="learnme-brand-text" style="font-size: {{ $current['font'] }};">
            <span class="part-learn" style="color: var(--text);">Learn</span><span class="part-me" style="color: var(--primary);">me</span>
        </div>
    @endif
</div>

<style>
    .learnme-logo-comp {
        display: flex;
        align-items: center;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .learnme-logo-comp:hover {
        transform: translateY(-2px) scale(1.02);
    }
    .learnme-icon-box {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .learnme-img {
        width: 100%;
        height: auto;
        display: block;
        filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));
    }
    .learnme-logo-comp:hover .learnme-img {
        filter: drop-shadow(0 8px 15px rgba(20, 184, 166, 0.2));
    }
    .learnme-brand-text {
        font-family: 'Inter', sans-serif;
        font-weight: 900;
        letter-spacing: -1.5px;
        line-height: 1;
        display: flex;
        align-items: center;
    }
    .part-learn { color: var(--text); }
    .part-me { color: var(--primary); }
</style>



