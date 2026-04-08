@extends('layouts.app')

@section('header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h2 class="font-display fw-semibold text-white" style="font-size: 1.25rem; margin: 0;"><svg style="width:16px;height:16px;display:inline;margin-right:6px;color:rgba(255,255,255,0.85);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Timeline — {{ $loan->loan_number }}</h2>
        <a href="{{ route('loans.show', $loan) }}" class="btn-accent-outline btn-accent-sm btn-accent-outline-white"><svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back</a>
    </div>
@endsection

@section('content')
<div class="py-4">
    <div class="px-3 px-sm-4 px-lg-5" style="max-width: 48rem;">

        @if($timeline->isEmpty())
            <p class="text-muted text-center py-4">No timeline entries yet.</p>
        @else
            <div class="position-relative">
                {{-- Vertical line --}}
                <div style="position:absolute;left:20px;top:0;bottom:0;width:2px;background:var(--bs-border-color);"></div>

                @foreach($timeline as $entry)
                    @php
                        $colorMap = [
                            'primary' => '#2563eb',
                            'success' => '#16a34a',
                            'warning' => '#f59e0b',
                            'danger' => '#dc2626',
                            'info' => '#0ea5e9',
                            'secondary' => '#6b7280',
                        ];
                        $dotColor = $colorMap[$entry['color']] ?? '#6b7280';
                    @endphp
                    <div class="d-flex gap-3 mb-3 position-relative">
                        {{-- Dot --}}
                        <div class="flex-shrink-0 d-flex align-items-start" style="width:42px;padding-top:4px;">
                            <div style="width:14px;height:14px;border-radius:50%;background:{{ $dotColor }};border:3px solid #fff;box-shadow:0 0 0 2px {{ $dotColor }}33;margin-left:14px;position:relative;z-index:1;"></div>
                        </div>

                        {{-- Content --}}
                        <div class="card border-0 shadow-sm flex-grow-1">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong style="font-size:0.9rem;">{{ $entry['title'] }}</strong>
                                        @if($entry['user'] !== '—')
                                            <small class="text-muted ms-2">by {{ $entry['user'] }}</small>
                                        @endif
                                    </div>
                                    <small class="text-muted text-nowrap ms-2">
                                        {{ $entry['date']?->format('d M Y') }}<br>
                                        <span style="font-size:0.7rem;">{{ $entry['date']?->format('h:i A') }}</span>
                                    </small>
                                </div>
                                @if($entry['description'])
                                    <p class="mb-0 small text-muted mt-1">{{ $entry['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
