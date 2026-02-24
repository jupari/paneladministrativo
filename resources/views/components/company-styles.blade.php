{{-- Componente para estilos dinámicos de empresa --}}
{{-- CSS de branding global --}}
<link rel="stylesheet" href="{{asset('assets/css/company-branding.css')}}">

{{-- CSS dinámico de empresa desde middleware --}}
@if(isset($companyDynamicCSS))
    {!! $companyDynamicCSS !!}
@endif
