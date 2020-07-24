<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstituicoesController extends Controller
{
    public function getInstituicoes(){
        $data = json_decode(file_get_contents(base_path('simulador\instituicoes.json')), true);
        return response()->json($data);
    }
}
