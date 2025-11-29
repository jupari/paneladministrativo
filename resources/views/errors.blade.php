@extends('adminlte::page')

@section('title', 'Usuarios')

@section('plugin.Datatables')

@section('plugin.Sweetalert2')
@section('plugin.Select2')

@section('content')
<div class="container-fluid">
   <!-- Main content -->
   <section class="content">
    <div class="error-page">
      <h2 class="headline text-danger">500</h2>

      <div class="error-content">
        <h3><i class="fas fa-exclamation-triangle text-danger"></i> Oops! Something went wrong.</h3>

        <p>
          {{ $error }}
        </p>

        <form class="search-form">
          <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search">

            <div class="input-group-append">
              <button type="submit" name="submit" class="btn btn-danger"><i class="fas fa-search"></i>
              </button>
            </div>
          </div>
          <!-- /.input-group -->
        </form>
      </div>
    </div>
    <!-- /.error-page -->

  </section>
</div>

@stop

{{-- @include('admin.cuentaabonado.modal') --}}

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
{{-- <script src="{{asset('assets/js/cuentaabonado/cuentaabonado.js') }}" type="text/javascript"></script> --}}
@stop
