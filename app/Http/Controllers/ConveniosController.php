<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConveniosController extends Controller
{
    public function getConvenios(){
        $data = json_decode(file_get_contents(base_path('simulador\convenios.json')), true);
        return response()->json($data);
    }
}
