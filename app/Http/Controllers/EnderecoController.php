<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class EnderecoController extends Controller
{
    public function consultarCep(Request $request)
    {
        // Validação dos dados de entrada
        $request->validate([
            'cep' => 'required|numeric|digits:8'
        ]);

        // Obtenção do CEP enviado na requisição
        $cep = $request->cep;

        // Chamada à API do ViaCEP
        $client = new Client();
        $response = $client->get("https://viacep.com.br/ws/{$cep}/json/");

        // Verificação do status da resposta
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);

            // Salvar os dados em um banco de dados relacional (MySQL, MariaDB, PostgreSQL)
            $endereco = \App\Models\Endereco::updateOrCreate(
                ['cep' => $cep],
                [
                    'logradouro' => $data['logradouro'] ?? null,
                    'bairro' => $data['bairro'] ?? null,
                    'cidade' => $data['localidade'] ?? null,
                    'estado' => $data['uf'] ?? null
                ]
            );

            // Retornar os dados no formato JSON
            return response()->json($endereco);
        } else {
            // Tratamento de erro em caso de falha na requisição
            return response()->json(['error' => 'Falha na consulta do CEP'], 500);
        }
    }
}
