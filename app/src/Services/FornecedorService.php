<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../Repositories/OfertaRepository.php';

class FornecedorService
{
    public function __construct(
        private FornecedorRepository $fornecedorRepo,
        private RequisicaoRepository $requisicaoRepo,
        private OfertaRepository $ofertaRepo
    ) {}

    public function listarRequisicoesAbertas(string $fornecedorId): array
    {
        $categoriaIds = $this->fornecedorRepo->listarCategoriaIds($fornecedorId);
        return $this->requisicaoRepo->listarAbertasParaFornecedor($fornecedorId, $categoriaIds);
    }

    public function criarOferta(string $fornecedorId, string $requisicaoId, float $valorUnitario): string
    {
        if ($fornecedorId === '' || $requisicaoId === '') {
            throw new \InvalidArgumentException('Dados inválidos.');
        }
        if ($valorUnitario <= 0) {
            throw new \InvalidArgumentException('Valor unitário deve ser maior que zero.');
        }

        // Regra: só oferta em requisição aberta
        $req = $this->requisicaoRepo->buscarDetalhe($requisicaoId);
        if (!$req) {
            throw new \InvalidArgumentException('Requisição não encontrada.');
        }
        if (($req['status'] ?? '') !== 'aberta') {
            throw new \InvalidArgumentException('Só é possível ofertar em requisições abertas.');
        }

        // Regra: fornecedor não pode ofertar duas vezes
        if ($this->ofertaRepo->fornecedorJaOfertou($requisicaoId, $fornecedorId)) {
            throw new \InvalidArgumentException('Você já enviou uma oferta para esta requisição.');
        }

        return $this->ofertaRepo->criar($requisicaoId, $fornecedorId, $valorUnitario);
    }

    public function ofertasPorFornecedor(string $fornecedorId): array
    {
        return $this->ofertaRepo->ofertasPorFornecedor($fornecedorId);
    }
}
