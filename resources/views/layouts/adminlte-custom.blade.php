{{-- Layout personalizado que hereda de AdminLTE e incluye estilos de empresa --}}
@extends('adminlte::page')

{{-- Incluir estilos de empresa en todas las páginas que usen este layout --}}
@section('adminlte_css')
    <x-company-styles />
    @parent
@endsection

{{-- Interceptores globales: redirige al login cuando la sesión expira (401) --}}
@push('js')
<script>
    (function () {
        var loginUrl = "{{ route('login') }}";

        // jQuery / DataTables AJAX
        $(document).ajaxError(function (event, xhr) {
            if (xhr.status === 401) {
                window.location.href = loginUrl;
            }
        });

        // Axios
        if (window.axios) {
            window.axios.interceptors.response.use(
                function (response) { return response; },
                function (error) {
                    if (error.response && error.response.status === 401) {
                        window.location.href = loginUrl;
                    }
                    return Promise.reject(error);
                }
            );
        }
    })();
</script>
@endpush
