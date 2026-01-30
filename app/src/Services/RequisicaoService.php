<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repositories/RequisicaoRepository.php';

class RequisicaoService
{
    private RequisicaoRepository $repo;

    public function __construct(RequisicaoRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Regra de aplicação (bem leve por enquanto):
     * - lista requisições da escola do usuário
     */
    public function listarPorEscola(string $escolaId): array
    {
        $escolaId = $_SESSION['escola_id'] ?? null;

        return $this->repo->listarPorEscola($escolaId);
    }
}
