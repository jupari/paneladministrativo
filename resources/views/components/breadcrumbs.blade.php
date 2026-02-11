{{-- Breadcrumbs component mejorado --}}
@php
    $breadcrumbs = $breadcrumbs ?? [];
    $currentTitle = $currentTitle ?? 'Página Actual';
    $currentIcon = $currentIcon ?? 'fas fa-circle';
@endphp

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-gradient-light px-4 py-3 rounded-lg shadow-sm border-0">
        {{-- Enlace a Dashboard siempre presente --}}
        <li class="breadcrumb-item">
            <a href="{{ url('/dashboard') }}" class="text-decoration-none text-primary">
                <i class="fas fa-home me-2"></i>Inicio
            </a>
        </li>

        {{-- Breadcrumbs dinámicos --}}
        @foreach($breadcrumbs as $breadcrumb)
            <li class="breadcrumb-item">
                @if(isset($breadcrumb['url']) && $breadcrumb['url'])
                    <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none text-primary">
                        @if(isset($breadcrumb['icon']))<i class="{{ $breadcrumb['icon'] }} me-2"></i>@endif
                        {{ $breadcrumb['title'] }}
                    </a>
                @else
                    @if(isset($breadcrumb['icon']))<i class="{{ $breadcrumb['icon'] }} me-2"></i>@endif
                    {{ $breadcrumb['title'] }}
                @endif
            </li>
        @endforeach

        {{-- Página actual --}}
        <li class="breadcrumb-item active fw-bold" aria-current="page">
            <i class="{{ $currentIcon }} me-2 text-warning"></i>{{ $currentTitle }}
        </li>
    </ol>
</nav>
