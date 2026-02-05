<?php
declare(strict_types=1);

require_once __DIR__ . '/../Repositories/RequisicaoRepository.php';
require_once __DIR__ . '/../Repositories/PddeRepository.php';
require_once __DIR__ . '/../Repositories/OfertaRepository.php';
require_once __DIR__ . '/../Repositories/AutorizacoesRepository.php';
require_once __DIR__ . '/../Services/AutorizacoesService.php';


class RequisicaoService
{
    private RequisicaoRepository $repo;
    private PddeRepository $pddeRepo;
    private CategoriaRepository $categoriaRepo;
    private OfertaRepository $ofertaRepo;


    public function __construct(RequisicaoRepository $repo, PddeRepository $pddeRepo, CategoriaRepository $categoriaRepo, OfertaRepository $ofertaRepo)
    {
        $this->repo = $repo;
        $this->pddeRepo = $pddeRepo;
        $this->categoriaRepo = $categoriaRepo;
        $this->ofertaRepo = $ofertaRepo;
    }

    /**
     * Lista requisiçõeses da escola do usurio
     */
    public function listarPorEscola(string $escolaId): array
    {
        $escolaId = $_SESSION['escola_id'] ?? null;

        return $this->repo->listarPorEscola($escolaId);
    }

        
    public function criar(array $data): string
        {
            $escolaId = $data['escola_id'] ?? '';
            $pddeId = $data['pdde_id'] ?? '';
            $categoriaId = $data['categoria_id'] ?? null;
            $produto = trim($data['produto'] ?? '');
            $quantidade = (int)($data['quantidade'] ?? 0);
            $obs = (string)($data['obs'] ?? '');

            if ($escolaId === '' || $pddeId === '') {
                throw new \InvalidArgumentException('Escola e PDDE são obrigatórios.');
            }
            if ($produto === '') {
                throw new \InvalidArgumentException('Informe o produto.');
            }
            if ($quantidade <= 0) {
                throw new \InvalidArgumentException('Quantidade deve ser maior que zero.');
            }

            if (!$this->pddeRepo->pertenceAEscola($pddeId, $escolaId)) {
                throw new \InvalidArgumentException('PDDE inválido para esta escola.');
            }

            if ($categoriaId !== null && $categoriaId !== '' && !$this->categoriaRepo->existe($categoriaId)) {
                throw new \InvalidArgumentException('Categoria inválida.');
            }

            $categoriaId = ($categoriaId === '') ? null : $categoriaId;

            return $this->repo->criar([
                'escola_id' => $escolaId,
                'pdde_id' => $pddeId,
                'categoria_id' => $categoriaId,
                'produto' => $produto,
                'quantidade' => $quantidade,
                'obs' => $obs,
            ]);
        }

        public function buscarDetalheParaEscola(string $requisicaoId, string $escolaId): array
        {
            if ($requisicaoId === '' || $escolaId === '') {
                throw new \InvalidArgumentException('Parâmetros inválidos.');
            }

            $req = $this->repo->buscarDetalhe($requisicaoId);

            if (!$req) {
                throw new \InvalidArgumentException('Requisição não encontrada.');
            }

            // regra de segurança
            if ((string)$req['escola_id'] !== $escolaId) {
                throw new \InvalidArgumentException('Você não tem acesso a esta requisição.');
            }

            return $req;
        }

public function listarOfertasDaRequisicaoParaEscola(string $requisicaoId, string $escolaId): array
{
    $this->buscarDetalheParaEscola($requisicaoId, $escolaId);

    return $this->ofertaRepo->listarPorRequisicao($requisicaoId);
}

public function selecionarOfertaParaEscola(string $requisicaoId, string $ofertaId, string $escolaId): void
{
    $req = $this->buscarDetalheParaEscola($requisicaoId, $escolaId);

    if (($req['status'] ?? '') !== 'aberta') {
        throw new \InvalidArgumentException('Só é possível trocar a oferta enquanto a requisição está aberta.');
    }

    if (!$this->ofertaRepo->pertenceARequisicao($ofertaId, $requisicaoId)) {
        throw new \InvalidArgumentException('Oferta inválida para esta requisição.');
    }

    $this->repo->selecionarOferta($requisicaoId, $ofertaId);
}

public function iniciarCompraParaEscola(string $requisicaoId, string $escolaId): void
{
    $req = $this->buscarDetalheParaEscola($requisicaoId, $escolaId);

    $oferta = $this->ofertaRepo->buscarSelecionadaPorRequisicao($requisicaoId);

    if (($req['status'] ?? '') !== 'aberta') {
        throw new \InvalidArgumentException('Só é possível iniciar compra quando a requisição está aberta.');
    }

    if (empty($req['oferta_selecionada_id'])) {
        throw new \InvalidArgumentException('Selecione uma oferta antes de iniciar a compra.');
    }

    $this->repo->iniciarCompra($req, $oferta);
}


public function concluirCompraParaEscola(string $requisicaoId, string $escolaId)
{
   $pdo = Database::getConnection();

   $autRepository = new AutorizacoesRepository($pdo);
   $autService = new AutorizacoesService($autRepository);

    $dados = $this->repo->buscarParaConclusao($requisicaoId, $escolaId);

    if (!$dados) {
        throw new \RuntimeException('Requisição não encontrada.');
    }

    if (($dados['status'] ?? '') !== 'em_compra') {
        throw new \InvalidArgumentException('Só é possível concluir quando a requisição está em compra.');
    }

    $qtd = (int)($dados['quantidade'] ?? 0);
    $valorUnit = (float)($dados['valor_unitario'] ?? 0);
    if ($qtd <= 0 || $valorUnit <= 0) {
        throw new \RuntimeException('Dados inválidos para concluir (quantidade/valor).');
    }

    $total = $qtd * $valorUnit;

    $pddeId = (string)($dados['pdde_id'] ?? '');
    if ($pddeId === '') {
        throw new \RuntimeException('PDDE inválido.');
    }

    $saldoBloqueadoPdde = $this->pddeRepo->buscarSaldoBloqueadoPorIdRequisicao($requisicaoId);
    (float)$saldoBloq = $saldoBloqueadoPdde['saldo_bloqueado'] ?? 0;
    $totalDesbloquear = $saldoBloq - $total;
    $this->pddeRepo->desbloquearSaldo($pddeId, $totalDesbloquear);


    $pdo->beginTransaction();
    try {
        $this->pddeRepo->debitarDisponivelEAdicionarGasto($pddeId, $total);

        $this->repo->marcarComoConcluida($requisicaoId);

        $autService->criarAutorizacao($requisicaoId, $dados);

        $pdo->commit();
    } catch (\Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

public function buscarParaMovimento(string $requisicaoId, string $escolaId): ?array
{

$pdo = Database::getConnection();

    $sql = "
      SELECT
        r.id,
        r.status,
        r.quantidade,
        r.pdde_id,
        r.oferta_selecionada_id,
        o.valor_unitario
      FROM requisicoes r
      JOIN ofertas o ON o.id = r.oferta_selecionada_id
      WHERE r.id = :rid
        AND r.escola_id = :eid
      LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['rid' => $requisicaoId, 'eid' => $escolaId]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    return $row ?: null;
}







}