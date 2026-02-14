<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repositories/FornecedorRepository.php';
require_once __DIR__ . '/../Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../Repositories/OfertaRepository.php';

class FornecedorService
{
    public $idFornecedor;
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

    public function criarOferta(string $fornecedorId, string $requisicaoId, float $valorUnitario, string $marca): string
    {
        if ($fornecedorId === '' || $requisicaoId === '') {
            throw new \InvalidArgumentException('Dados inválidos.');
        }
        if ($valorUnitario <= 0) {
            throw new \InvalidArgumentException('Valor unitário deve ser maior que zero.');
        }

        if ($marca == '') {
            throw new \InvalidArgumentException('Preencha a marca do produto');
        }

        $req = $this->requisicaoRepo->buscarDetalhe($requisicaoId);

        if (!$req) {
            throw new \InvalidArgumentException('Requisição não encontrada.');
        }
        if (($req['status'] ?? '') !== 'aberta') {
            throw new \InvalidArgumentException('Só é possível ofertar em requisições abertas.');
        }

        if ($this->ofertaRepo->fornecedorJaOfertou($requisicaoId, $fornecedorId)) {
            throw new \InvalidArgumentException('Você já enviou uma oferta para esta requisição.');
        }

        $quantidade = (int)($req['quantidade'] ?? 0);

        $valorTotal = $valorUnitario * $quantidade;

        return $this->ofertaRepo->criar($requisicaoId, $fornecedorId, $valorUnitario, $valorTotal, $marca);
    }

    public function ofertasPorFornecedor(string $fornecedorId): array
    {
        return $this->ofertaRepo->ofertasPorFornecedor($fornecedorId);
    }

    public function criarFornecedor($data)
    {
        $this->criarFornecedorBD($data);
        $this->criarVinculoCategoria($data);
        $this->criarUserFornecedor($data);
    }

    public function criarFornecedorBD($data)
    {
        $this->idFornecedor = $this->fornecedorRepo->criarFornecedorBD($data);

        if (!$this->idFornecedor) {
            throw new InvalidArgumentException('Fornecedor não criado.');
        }
    }

    public function criarVinculoCategoria($data)
    {
        $this->fornecedorRepo->criarVinculoCategoria($data, $this->idFornecedor);
    }

    public function criarUserFornecedor($data)
    {
        $this->fornecedorRepo->criarUserFornecedor($data, $this->idFornecedor);
    }
}
