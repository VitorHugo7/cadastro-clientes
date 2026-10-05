<?php
declare(strict_types=1);

final class CepController
{
    public function __construct(private ViaCepService $servico) {}

    public function consultar(string $cep): never
    {
        try {
            Http::json(['endereco' => $this->servico->consultar($cep)]);
        } catch (InvalidArgumentException $erro) {
            Http::json(['erro' => $erro->getMessage()], 422);
        } catch (RuntimeException $erro) {
            Http::json(['erro' => $erro->getMessage()], $erro->getCode());
        }
    }
}
