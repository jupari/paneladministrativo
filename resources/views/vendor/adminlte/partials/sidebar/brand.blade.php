{{-- Brand Logo --}}
<a href="{{ url(config('adminlte.dashboard_url', 'dashboard')) }}" 
   class="brand-link {{ config('adminlte.classes_brand', '') }}"
   @if(isset($companyConfig))
       data-company="{{ $companyConfig['name'] ?? 'Sistema' }}"
   @endif>

    {{-- Brand Image --}}
    @if(isset($companyConfig) && $companyConfig['logo_url'])
        <img src="{{ $companyConfig['logo_url'] }}" 
             alt="{{ $companyConfig['name'] ?? 'Logo' }}" 
             class="brand-image img-circle elevation-3" 
             style="opacity: .8; max-height: 33px; width: auto;">
    @elseif(config('adminlte.logo_img'))
        <img src="{{ asset(config('adminlte.logo_img')) }}" 
             alt="{{ config('adminlte.logo_img_alt', 'Logo') }}" 
             class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}">
    @endif

    {{-- Brand Text --}}
    <span class="brand-text font-weight-light">
        @if(isset($companyConfig))
            {{ $companyConfig['name'] ?? config('adminlte.logo', 'AdminLTE') }}
        @else
            {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
        @endif
    </span>

</a>

{{-- Custom CSS for company branding --}}
@if(isset($companyConfig))
<style>
    :root {
        --company-primary: {{ $companyConfig['primary_color'] ?? '#007bff' }};
        --company-secondary: {{ $companyConfig['secondary_color'] ?? '#6c757d' }};
    }
    
    .brand-link .brand-image {
        max-height: 35px !important;
        width: auto !important;
        margin-top: -3px !important;
        border-radius: 50% !important;
    }
    
    .brand-link .brand-text {
        color: #c2c7d0 !important;
        font-weight: 600 !important;
        margin-left: 8px;
    }
    
    .brand-link:hover .brand-text {
        color: #fff !important;
    }
    
    /* Company theme colors */
    .btn-primary {
        background-color: var(--company-primary) !important;
        border-color: var(--company-primary) !important;
    }
    
    .card-primary.card-outline {
        border-top: 3px solid var(--company-primary) !important;
    }
    
    .text-primary {
        color: var(--company-primary) !important;
    }
</style>
@endif