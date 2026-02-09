<?php

declare(strict_types=1);

require_once __DIR__ . '/../../libs/TCPDF-main/tcpdf.php';

class RelatoriosService
{
    private RelatoriosRepository $repository;

    public function __construct(RelatoriosRepository $repository)
    {
        $this->repository = $repository;
    }

    public function relatorioMensalPorEscola(string $mes, string $escola_id): void
    {
        $dados = $this->repository->relatorioMensal($mes, $escola_id);
        $pdfString = $this->criarPdfRelatorioMensal($mes, $dados);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="relatorio.pdf"');
        header('Content-Length: ' . strlen($pdfString));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfString;
        exit;
    }



    public function criarPdfRelatorioMensal(string $mes, array $dados): string
{
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    $pdf->SetCreator('PDDE');
    $pdf->SetAuthor('Sistema');
    $pdf->SetTitle('Relatório Mensal Analítico');

    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->AddPage();

    /* ===== Paleta ===== */
    $ink    = [33, 37, 41];
    $muted  = [108, 117, 125];
    $line   = [222, 226, 230];
    $paper  = [255, 255, 255];
    $bgRow  = [248, 249, 250];
    $accent = [76, 110, 245];

    /* ===== Helpers ===== */
    $fmtMoney = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');

    $fmtDate = function (?string $createdAt): string {
        if (!$createdAt) return '-';
        try {
            $dt = new DateTime(preg_replace('/\.\d+.*/', '', $createdAt));
            $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            return $dt->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $createdAt;
        }
    };

    /* ===== Mês ===== */
    $mesExibicao = $mes;
    if (preg_match('/^\d{4}-\d{2}$/', $mes)) {
        $map = [
            '01'=>'Janeiro','02'=>'Fevereiro','03'=>'Março','04'=>'Abril',
            '05'=>'Maio','06'=>'Junho','07'=>'Julho','08'=>'Agosto',
            '09'=>'Setembro','10'=>'Outubro','11'=>'Novembro','12'=>'Dezembro'
        ];
        $mesExibicao = ($map[substr($mes,5,2)] ?? substr($mes,5,2)) . ' de ' . substr($mes,0,4);
    }

    /* ===== Processamento dos dados ===== */
    $totalValor = 0;
    $produtos = [];
    $datas = [];

    foreach ($dados as $d) {
        $totalValor += (float)$d['valor_total'];
        $prod = $d['produto'] ?? 'Indefinido';
        $produtos[$prod] = ($produtos[$prod] ?? 0) + 1;
        $datas[] = $d['created_at'];
    }

    arsort($produtos);

    $totalRegistros = count($dados);
    $mediaValor = $totalRegistros ? $totalValor / $totalRegistros : 0;
    $produtoTop = array_key_first($produtos) ?? '-';

    sort($datas);
    $primeira = $fmtDate($datas[0] ?? null);
    $ultima   = $fmtDate(end($datas) ?: null);

    /* ===== Cabeçalho ===== */
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
    $pdf->Cell(0, 8, 'Relatório Mensal Analítico', 0, 1);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(0, 6, 'Visão consolidada das movimentações financeiras', 0, 1);

    $pdf->Ln(6);

    /* ===== Card Mês ===== */
    $pdf->SetDrawColor($line[0], $line[1], $line[2]);
    $pdf->SetFillColor($paper[0], $paper[1], $paper[2]);
    $pdf->RoundedRect(15, $pdf->GetY(), 180, 18, 4, '1111', 'DF');

    $pdf->SetXY(18, $pdf->GetY() + 4);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, "Período: {$mesExibicao}", 0, 1);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(0, 4, "Gerado em: " . date('d/m/Y H:i'), 0, 1);

    $pdf->Ln(14);

    /* ===== Resumo ===== */
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
    $pdf->Cell(0, 6, 'Resumo Geral', 0, 1);

    $pdf->Ln(3);

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(90, 6, "Total de registros: {$totalRegistros}", 0, 0);
    $pdf->Cell(90, 6, "Valor total: " . $fmtMoney($totalValor), 0, 1);

    $pdf->Cell(90, 6, "Valor médio por registro: " . $fmtMoney($mediaValor), 0, 0);
    $pdf->Cell(90, 6, "Produto mais recorrente: {$produtoTop}", 0, 1);

    $pdf->Cell(90, 6, "Primeira movimentação: {$primeira}", 0, 0);
    $pdf->Cell(90, 6, "Última movimentação: {$ultima}", 0, 1);

    $pdf->Ln(10);

    /* ===== Ranking de Produtos ===== */
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, 'Distribuição por Produto', 0, 1);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', '', 9);
    foreach ($produtos as $produto => $qtde) {
        $pdf->Cell(140, 6, $produto, 1, 0);
        $pdf->Cell(40, 6, $qtde . ' registro(s)', 1, 1, 'R');
    }

    $pdf->Ln(10);

    /* ===== Tabela Detalhada ===== */
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 6, 'Movimentações Detalhadas', 0, 1);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetFillColor($bgRow[0], $bgRow[1], $bgRow[2]);

    $pdf->Cell(40, 8, 'DATA', 1, 0, 'L', true);
    $pdf->Cell(90, 8, 'PRODUTO', 1, 0, 'L', true);
    $pdf->Cell(50, 8, 'VALOR', 1, 1, 'R', true);

    $pdf->SetFont('helvetica', '', 9);

    foreach ($dados as $i => $d) {
        $fill = $i % 2 === 0;
        $pdf->SetFillColor(
            $fill ? $bgRow[0] : $paper[0],
            $fill ? $bgRow[1] : $paper[1],
            $fill ? $bgRow[2] : $paper[2]
        );

        $pdf->Cell(40, 8, $fmtDate($d['created_at']), 1, 0, 'L', $fill);
        $pdf->Cell(90, 8, $d['produto'], 1, 0, 'L', $fill);
        $pdf->Cell(50, 8, $fmtMoney($d['valor_total']), 1, 1, 'R', $fill);
    }

    /* ===== Rodapé ===== */
    $pdf->SetY(-20);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
    $pdf->Cell(0, 5, 'Documento gerado em ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Sistema PDDE - Relatório Mensal Analítico', 0, 1, 'C');

    return $pdf->Output("relatorio_mensal_{$mes}.pdf", 'S');
}


    public function relatorioPorPdde(string $pddeId): void
    {
        $pdo = Database::getConnection();
        $pddeRepository = new PddeRepository($pdo);

        $dados = $this->repository->relatorioPorPdde($pddeId);

        $pddeDados = $pddeRepository->buscarDadosPorID($pddeId) ?? 'PDDE ' . $pddeId;

        $pddeNome = $pddeDados['nome'];;

        $pdfString = $this->criarPdfRelatorioPorPdde($pddeNome, $pddeId, $dados);

         while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="relatorio.pdf"');
        header('Content-Length: ' . strlen($pdfString));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfString;
        exit;
    }

    public function criarPdfRelatorioPorPdde(
        string $pddeNome,
        string $pddeId,
        array $dados
        ): string {

            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            $pdf->SetCreator('PDDE');
            $pdf->SetAuthor('Sistema');
            $pdf->SetTitle('Relatório por PDDE');

            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            $pdf->AddPage();

            /* ===== Paleta ===== */
            $ink    = [33, 37, 41];
            $muted  = [108, 117, 125];
            $line   = [222, 226, 230];
            $paper  = [255, 255, 255];
            $bgRow  = [248, 249, 250];
            $accent = [76, 110, 245]; // azul diferente do mensal

            /* ===== Helpers ===== */
            $fmtMoney = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');

            $fmtDate = function (?string $createdAt): string {
                if (!$createdAt) return '-';
                try {
                    $dt = new DateTime(preg_replace('/\.\d+.*/', '', $createdAt));
                    $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                    return $dt->format('d/m/Y H:i');
                } catch (\Throwable $e) {
                    return $createdAt;
                }
            };

            /* ===== Processamento dos dados ===== */
            $totalValor = 0;
            $produtos = [];
            $datas = [];

            foreach ($dados as $d) {
                $totalValor += (float)$d['valor_total'];
                $prod = $d['produto'] ?? 'Indefinido';
                $produtos[$prod] = ($produtos[$prod] ?? 0) + 1;
                $datas[] = $d['created_at'];
            }

            arsort($produtos);

            $totalRegistros = count($dados);
            $mediaValor = $totalRegistros ? $totalValor / $totalRegistros : 0;
            $produtoTop = array_key_first($produtos) ?? '-';

            sort($datas);
            $primeira = $fmtDate($datas[0] ?? null);
            $ultima   = $fmtDate(end($datas) ?: null);

            /* ===== Cabeçalho ===== */
            $pdf->SetFont('helvetica', 'B', 18);
            $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
            $pdf->Cell(0, 8, 'Relatório Analítico por PDDE', 0, 1);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
            $pdf->Cell(0, 6, 'Visão consolidada das movimentações do PDDE. ', 0, 1);

            $pdf->Ln(6);

            /* ===== Card PDDE ===== */
            $pdf->SetDrawColor($line[0], $line[1], $line[2]);
            $pdf->SetFillColor($paper[0], $paper[1], $paper[2]);
            $pdf->RoundedRect(15, $pdf->GetY(), 180, 18, 4, '1111', 'DF');


            $pdf->SetXY(18, $pdf->GetY() + 4);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, "PDDE: {$pddeNome}", 0, 1);

            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
            $pdf->Cell(0, 4, "ID: {$pddeId}", 0, 1);

            $pdf->Ln(14);

            /* ===== Resumo ===== */
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
            $pdf->Cell(0, 6, 'Resumo Geral', 0, 1);

            $pdf->Ln(3);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(90, 6, "Total de registros: {$totalRegistros}", 0, 0);
            $pdf->Cell(90, 6, "Valor total: " . $fmtMoney($totalValor), 0, 1);

            $pdf->Cell(90, 6, "Valor médio por registro: " . $fmtMoney($mediaValor), 0, 0);
            $pdf->Cell(90, 6, "Produto mais recorrente: {$produtoTop}", 0, 1);

            $pdf->Cell(90, 6, "Primeira movimentação: {$primeira}", 0, 0);
            $pdf->Cell(90, 6, "Última movimentação: {$ultima}", 0, 1);

            $pdf->Ln(10);

            /* ===== Ranking de Produtos ===== */
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, 'Distribuição por Produto', 0, 1);
            $pdf->Ln(2);

            $pdf->SetFont('helvetica', '', 9);
            foreach ($produtos as $produto => $qtde) {
                $pdf->Cell(140, 6, $produto, 1, 0);
                $pdf->Cell(40, 6, $qtde . ' registro(s)', 1, 1, 'R');
            }

            $pdf->Ln(10);

            /* ===== Tabela Detalhada ===== */
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 6, 'Movimentações Detalhadas', 0, 1);
            $pdf->Ln(2);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor($bgRow[0], $bgRow[1], $bgRow[2]);

            $pdf->Cell(40, 8, 'DATA', 1, 0, 'L', true);
            $pdf->Cell(90, 8, 'PRODUTO', 1, 0, 'L', true);
            $pdf->Cell(50, 8, 'VALOR', 1, 1, 'R', true);

            $pdf->SetFont('helvetica', '', 9);

            foreach ($dados as $i => $d) {
                $fill = $i % 2 === 0;
                $pdf->SetFillColor(
                    $fill ? $bgRow[0] : $paper[0],
                    $fill ? $bgRow[1] : $paper[1],
                    $fill ? $bgRow[2] : $paper[2]
                );

                $pdf->Cell(40, 8, $fmtDate($d['created_at']), 1, 0, 'L', $fill);
                $pdf->Cell(90, 8, $d['produto'], 1, 0, 'L', $fill);
                $pdf->Cell(50, 8, $fmtMoney($d['valor_total']), 1, 1, 'R', $fill);
            }

            /* ===== Rodapé ===== */
            $pdf->SetY(-20);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
            $pdf->Cell(0, 5, 'Documento gerado em ' . date('d/m/Y H:i'), 0, 1, 'C');
            $pdf->Cell(0, 5, 'Sistema PDDE - Relatório por PDDE', 0, 1, 'C');

            return $pdf->Output(
                'relatorio_pdde_' . substr($pddeId, 0, 8) . '.pdf',
                'S'
            );
        }


}
