<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CotizarController extends Controller
{
    public function index(){
        return view('cotizar.index');
    }

    public function create(){
        return view('cotizar.documento');
    }
}
