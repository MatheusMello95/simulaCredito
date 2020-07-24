<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmprestimoController extends Controller
{

    public function rules(){
        return [
            'valor_emprestimo' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'instituicoes' => 'array',
            'convenios' => 'array',
            'parcela' => 'numeric'
        ];
    }

    public function simulaCred(Request $request){
        $validator = \Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if($request->has('instituicoes')){
            foreach($request->instituicoes as $instituicao){
                $instituicao = strtoupper($instituicao);
                if(!$this->validaInstituicao($instituicao)){
                    return response()->json([
                        'message'=> 'Instituicao invalida / Institucao deve ser escrita maiuscula'
                    ], 400);
                }
                if($request->has('convenios')){
                    $request->convenios = strtoupper($request->convenios);
                    if(!$this->validaConvenioBanco($instituicao, $request->convenios)){
                        return response()->json([
                            'message'=> 'Convenio invalido'
                        ], 400);
                    }
                    if($request->has('parcela')){
                        if(!$this->validaParcelaInst($instituicao, $request->parcela)){
                            return response()->json([
                                'message'=> 'Numero de parcelas invalidos'
                            ], 400);
                        }
                        $result = $this->calculaParcelaConvenioBanco($request->valor_emprestimo, $request->parcela, $request->convenios, $instituicao);
                        return response()->json([
                            'data' => $result
                        ],200);
                    }else{
                        $result =  $this->calculaParcelasConvenio($instituicao, $request->valor_emprestimo, $request->convenios);
                        return response()->json([
                            'data' => $result
                        ],200);
                    }
                }else{
                    if($request->has('parcela')){
                        if(!$this->validaParcelaInst($instituicao, $request->parcela)){
                            return response()->json([
                                'message'=> 'Numero de parcelas invalidos'
                            ], 400);
                        }
                        $result = $this->calculaParcelaBanco($instituicao, $request->valor_emprestimo, $request->parcela);
                        return response()->json([
                            'data' => $result
                        ],200);
                    }else{
                        $result =  $this->calculaParcelas($instituicao, $request->valor_emprestimo);
                        return response()->json([
                            'data' => $result
                        ],200);
                    }
                }
            }
        }else if($request->has('convenios')){
            foreach($request->convenios as $convenio){
                $convenio = strtoupper($convenio);
                if(!$this->validaConvenio($convenio)){
                    return response()->json([
                        'message'=> 'Convenios invalido'
                    ], 400);
                }
                if($request->has('parcela')){
                    if(!$this->validaConvParcela($convenio, $request->parcela)){
                        return response()->json([
                            'message'=> 'Numero de parcelas invalidos'
                        ], 400);
                    }
                    $result = $this->calculaParcelaConv($request->valor_emprestimo, $convenio, $request->parcela);
                    return response()->json([
                        'data' => $result
                    ],200);
                }else{
                    $result = $this->calculaConv($convenio, $request->valor_emprestimo);
                    return response()->json([
                        'data' => $result
                    ],200);
                }
            }
        } else if($request->has('parcela')){
            if(!$this->validaParcela($request->parcela)){
                return response()->json([
                    'message'=> 'Numero de parcelas invalidos'
                ], 400);
            }
            $result = $this->calculaParc($request->parcela, $request->valor_emprestimo);
            return response()->json([
                'data'=>$result
            ],200);
        }else{
            $result = $this->calculaValor($request->valor_emprestimo);
            return response()->json([
                'data'=>$result
            ],200);
        }
    }

    public function validaInstituicao($instituicao){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $item){
            if($aux >0) break;
            if($item['instituicao'] == $instituicao){
                $result = true;
                $aux++;
            }else{
                $result = false;
            }
        }
        return $result;
    }

    public function validaConvenioBanco($instituicao, $convenios){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $item){
            if($aux > 0) break;
            foreach($convenios as $convenio)
                if($item['instituicao']== $instituicao && $item['convenio']==$convenio){
                    $result = true;
                    $aux++;
                }else{
                    $result = false;
                }
            }

        return $result;
    }

    public function validaConvenio($convenio){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $item){
            if($aux > 0) break;
                if($item['convenio'] == $convenio){
                    $result = true;
                    $aux++;
                }else{
                    $result =false;
                }
        }
        return $result;
    }

    public function validaConvParcela($convenio, $parcela){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $item){
            if($aux > 0) break;
            if($item['convenio']== $convenio && $item['parcelas']== $parcela){
                $result = true;
                $aux++;
            }else{
                $result = false;
            }
        }
        return $result;

    }

    public function validaParcelaInst($instituicao, $parcela){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $item){
            if($aux > 0) break;
            if($item['instituicao']== $instituicao && $item['parcelas']== $parcela){
                $result = true;
                $aux++;
            }else{
                $result = false;
            }
        }
        return $result;
    }

    public function validaParcela($parcela){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $item){
            if($aux > 0) break;
            if($item['parcelas']== $parcela){
                $result = true;
                $aux++;
            }else{
                $result = false;
            }
        }
        return $result;
    }

    public function calculaParc($parcela, $valorEmprestimo){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $result=[];

        foreach($data as $key => $item){
            if($item['parcelas'] == $parcela){
                $valor = round($valorEmprestimo * $item['coeficiente'], 2);
                $object = [
                    "taxa" => $item['taxaJuros'],
                    "parcela"=> $item['parcelas'],
                    "valor_parcela"=> $valor,
                    "convenio" => $item['convenio']
                ];
                $result[$item['instituicao']][]= $object;
            }
        }
        return $result;
    }

    public function calculaParcelaConv($valorEmprestimo, $convenio, $parcela){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $result=[];

        foreach($data as $key => $item){
            if($item['convenio'] == $convenio && $item['parcelas'] == $parcela){
                $valor = round($valorEmprestimo * $item['coeficiente'], 2);
                $object = [
                    "taxa" => $item['taxaJuros'],
                    "parcela"=> $item['parcelas'],
                    "valor_parcela"=> $valor,
                    "convenio" => $item['convenio']
                ];
                $result[$item['instituicao']][]= $object;
            }
        }
        return $result;
    }


    public function calculaConv($convenio, $valorEmprestimo){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $result=[];

        foreach($data as $key => $item){
            if($item['convenio'] == $convenio){
                $valor = round($valorEmprestimo * $item['coeficiente'], 2);
                $object = [
                    "taxa" => $item['taxaJuros'],
                    "parcela"=> $item['parcelas'],
                    "valor_parcela"=> $valor,
                    "convenio" => $item['convenio']
                ];
                $result[$item['instituicao']][]= $object;
            }
        }
        return $result;
    }

    public function calculaParcelaConvenioBanco($valorEmprestimo, $parcela, $convenios, $instituicao){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $result = [];
        $aux =0;
        foreach($data as $key => $item){
            if($aux > 0) break;
            foreach($convenios as $convenio){
                if($item['convenio'] == $convenio){
                    if(($item['parcelas'] ==$parcela && $item['instituicao']== $instituicao)){
                        $valor = round($valorEmprestimo * $item['coeficiente'],2);
                            $object = [
                                "taxa" => $item['taxaJuros'],
                                "parcela"=> $parcela,
                                "valor_parcela"=> $valor,
                                "convenio" => $convenio
                            ];
                        $result[$item['instituicao']][] = $object;
                        $aux++;
                    }
                }
            }
        }
        return $result;
    }

    public function calculaParcelaBanco($instituicao, $valorEmprestimo, $parcela){
        $data = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $aux = 0;
        foreach($data as $key => $item){
            if($aux > 0) break;
            if($item['parcelas'] ==$parcela && $item['instituicao']== $instituicao){
                $valor = round($valorEmprestimo * $item['coeficiente'],2);
                $object = [
                    "taxa" => $item['taxaJuros'],
                    "parcela"=> $parcela,
                    "valor_parcela"=> $valor,
                    "convenio" => $item['convenio']
                ];
                $result[$item['instituicao']][] = $object;
                $aux ++;
            }
        }
        return $result;
    }

    public function calculaParcelasConvenio($instituicao, $valorEmprestimo, $convenios){
        $itens = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);

        $result = [];
        foreach($itens as $key =>$item){
            foreach($convenios as $convenio){
                if($item['instituicao']==$instituicao && $item['convenio'] == $convenio){
                    $valor = round($valorEmprestimo * $item['coeficiente'], 2);
                    $object = [
                        "taxa" => $item['taxaJuros'],
                        "parcela"=> $item['parcelas'],
                        "valor_parcela"=> $valor,
                        "convenio" => $item['convenio']
                    ];
                    $result[$item['instituicao']][] = $object;
                }
            }
        }

        return $result;
    }

    public function calculaParcelas($instituicao, $valorEmprestimo){
        $itens = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);

        $result = [];
        foreach($itens as $key => $item){
            if($item['instituicao']==$instituicao){
                $valor = round($valorEmprestimo * $item['coeficiente'], 2);
                $object = [
                    "taxa" => $item['taxaJuros'],
                    "parcela"=> $item['parcelas'],
                    "valor_parcela"=> $valor,
                    "convenio" => $item['convenio']
                ];
                $result[$item['instituicao']][] = $object;
            }
        }

        return $result;
    }

    public function calculaValor($valorEmprestimo){
        $itens = json_decode(file_get_contents(base_path('simulador\taxas_instituicoes.json')), true);
        $result=[];

        foreach($itens as $key => $item){
                $valor = round($valorEmprestimo * $item['coeficiente'], 2);
                $object = [
                    "taxa" => $item['taxaJuros'],
                    "parcela"=> $item['parcelas'],
                    "valor_parcela"=> $valor,
                    "convenio" => $item['convenio']
                ];
            $result[$item['instituicao']][]= $object;
        }
        return $result;
    }
}
