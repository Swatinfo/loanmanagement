{{-- Mobile create-actions FAB — expanding launcher for New Quotation / Task / Visit.
     Visible < xl only (d-xl-none). Included from layouts/app.blade.php with a
     route guard that suppresses it on loan deep-workflow pages. --}}

@php
    $user = auth()->user();
    $fabActions = [];

    if ($user->hasPermission('create_quotation')) {
        $fabActions[] = [
            'key' => 'quotation',
            'label' => 'New Quotation',
            'url' => route('quotations.create'),
            'trigger' => null,
            'icon_class' => 'shf-fab-icon-quotation',
            'icon_path' => 'M9 12h6m-3-3v6m-4 6h8a2 2 0 002-2V7.414A1 1 0 0017.707 6.707l-3.414-3.414A1 1 0 0013.586 3H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        ];
    }

    // General tasks: any authenticated user can create a task.
    $fabActions[] = [
        'key' => 'task',
        'label' => 'New Task',
        'url' => route('general-tasks.index', ['create' => 1]),
        'trigger' => null,
        'icon_class' => 'shf-fab-icon-task',
        'icon_path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    ];

    if ($user->hasPermission('create_dvr')) {
        $fabActions[] = [
            'key' => 'visit',
            'label' => 'New Visit',
            'url' => route('dvr.index', ['create' => 1]),
            'trigger' => null,
            'icon_class' => 'shf-fab-icon-visit',
            'icon_path' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
        ];
    }
@endphp

@if (count($fabActions) > 0)
    <div class="shf-fab-backdrop" aria-hidden="true"></div>

    <div class="shf-fab-wrap">
        @if (count($fabActions) === 1)
            {{-- Single action: tap goes straight there, no expand --}}
            @php($only = $fabActions[0])
            <a href="{{ $only['url'] }}" class="shf-fab-main"
                aria-label="{{ $only['label'] }}" title="{{ $only['label'] }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </a>
        @else
            {{-- 2+ actions: expanding menu --}}
            <div class="shf-fab-menu" role="menu">
                @foreach ($fabActions as $action)
                    <a href="{{ $action['url'] }}" class="shf-fab-item" role="menuitem">
                        <span>{{ $action['label'] }}</span>
                        <span class="shf-fab-item-icon {{ $action['icon_class'] }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $action['icon_path'] }}" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <button type="button" class="shf-fab-main" id="shfFabMain"
                aria-label="Create menu" aria-expanded="false">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        @endif
    </div>
@endif
