<?php

declare(strict_types=1);

class MovimentacoesService
{
    public function __construct(MovimentacoesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function criarMovimentacao($dados)
    {
        $this->repository->criarMovimentacao($dados);
    }

    public function totalPorEscola($scolaId)
    {
        $movimentacoes = $this->repository->totalPorEscola($scolaId);

        return $movimentacoes;
    }
}