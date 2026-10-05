<?php
declare(strict_types=1);

final class ViaCepService
{
    public function consultar(string $cep): array
    {
        if (!preg_match('/^\d{8}$/D', $cep)) {
            throw new InvalidArgumentException('Informe um CEP com 8 dígitos.');
        }
        // O destino é fixo. Apenas os oito dígitos do CEP entram na URL.
        $requisicao = curl_init('https://viacep.com.br/ws/' . $cep . '/json/');
        curl_setopt_array($requisicao, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'CadastroClientes-Academico/1.0',
        ]);
        $conteudo = curl_exec($requisicao);
        $status = curl_getinfo($requisicao, CURLINFO_RESPONSE_CODE);
        $falha = curl_errno($requisicao);
        curl_close($requisicao);

        if ($conteudo === false || $status !== 200) {
            $mensagem = $falha === CURLE_OPERATION_TIMEDOUT
                ? 'A consulta ao CEP demorou demais. Preencha o endereço manualmente.'
                : 'O serviço de CEP está indisponível. Preencha o endereço manualmente.';
            throw new RuntimeException($mensagem, $falha === CURLE_OPERATION_TIMEDOUT ? 504 : 502);
        }
        try {
            $resposta = json_decode($conteudo, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $erro) {
            throw new RuntimeException('O serviço de CEP retornou uma resposta inválida. Preencha o endereço manualmente.', 502);
        }
        if (!is_array($resposta)) {
            throw new RuntimeException('O serviço de CEP retornou uma resposta inválida.', 502);
        }
        if (!empty($resposta['erro'])) {
            throw new RuntimeException('CEP não encontrado. Confira os dígitos ou preencha o endereço manualmente.', 404);
        }
        foreach (['logradouro', 'bairro', 'localidade', 'uf', 'cep'] as $campo) {
            if (!isset($resposta[$campo]) || !is_string($resposta[$campo])) {
                throw new RuntimeException('O serviço de CEP retornou uma resposta incompleta. Preencha o endereço manualmente.', 502);
            }
        }
        return [
            'cep' => $cep,
            'logradouro' => $resposta['logradouro'],
            'bairro' => $resposta['bairro'],
            'cidade' => $resposta['localidade'],
            'uf' => $resposta['uf'],
        ];
    }
}
