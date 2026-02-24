{{-- Layout personalizado que hereda de AdminLTE e incluye estilos de empresa --}}
@extends('adminlte::page')

{{-- Incluir estilos de empresa en todas las páginas que usen este layout --}}
@section('adminlte_css')
    <x-company-styles />
    @parent
@endsection
