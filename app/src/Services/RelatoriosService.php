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
        $pdf->SetTitle('Relatório Mensal');

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        // ===== Paleta sóbria (neutra + 1 destaque) =====
        $ink        = [33, 37, 41];     // texto principal
        $muted      = [108, 117, 125];  // texto secundário
        $line       = [222, 226, 230];  // bordas/linhas
        $bgRow      = [248, 249, 250];  // zebra da tabela
        $paper      = [255, 255, 255];  // branco

        $accent     = [44, 92, 164];    // azul discreto (destaque)
        $accentSoft = [232, 238, 250];  // azul bem clarinho (fundos)

        // ===== Formatação do mês (mantida) =====
        $mesExibicao = $mes;
        if (preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $meses = [
                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
            ];
            $mesNum = substr($mes, 5, 2);
            $ano    = substr($mes, 0, 4);
            $mesExibicao = ($meses[$mesNum] ?? $mesNum) . ' de ' . $ano;
        }

        // ===== Cabeçalho sóbrio com título =====
        // Fundo branco do topo (mantém visual clean)
        $pdf->SetFillColor($paper[0], $paper[1], $paper[2]);
        $pdf->Rect(0, 0, 210, 48, 'F');

        // Linha fina de destaque
        $pdf->SetDrawColor($accent[0], $accent[1], $accent[2]);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(15, 16, 195, 16);

        // Título criativo + institucional
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
        $pdf->SetXY(15, 10);
        $pdf->Cell(180, 7, 'Panorama Mensal de Movimentações', 0, 1, 'L');

        // Subtítulo
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->SetXY(15, 19.5);
        $pdf->Cell(180, 5, 'Relatório consolidado do período selecionado', 0, 1, 'L');

        // Mês (chip discreto)
        $pdf->SetFillColor($accentSoft[0], $accentSoft[1], $accentSoft[2]);
        $pdf->SetDrawColor($line[0], $line[1], $line[2]);
        $pdf->RoundedRect(15, 28, 90, 8, 3.5, '1111', 'DF');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor($accent[0], $accent[1], $accent[2]);
        $pdf->SetXY(15, 29.6);
        $pdf->Cell(90, 5, $mesExibicao, 0, 1, 'C');

        // Info à direita (geração)
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->SetXY(110, 29.7);
        $pdf->Cell(85, 5, 'Gerado em: ' . date('d/m/Y H:i'), 0, 1, 'R');

        $pdf->Ln(14);

        // ===== Helpers =====
        $fmtMoney = function ($valor): string {
            $n = (float)str_replace(',', '.', (string)$valor);
            return 'R$ ' . number_format($n, 2, ',', '.');
        };

        $fmtDate = function (?string $createdAt): string {
            if (!$createdAt) return '-';

            $normalized = preg_replace('/\.\d+(\+\d{2}:?\d{2}|Z)$/', '$1', $createdAt);
            $normalized = preg_replace('/\.\d+$/', '', $normalized);

            try {
                $dt = new \DateTime($normalized);
                $dt->setTimezone(new \DateTimeZone('America/Sao_Paulo'));
                return $dt->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                return $createdAt;
            }
        };

        // ===== Resumo (mantido, visual mais neutro) =====
        $total = 0.0;
        foreach ($dados as $m) {
            $total += (float)($m['valor_total'] ?? 0);
        }

        $startY = $pdf->GetY();

        $cardW = 87;
        $cardH = 32;

        // Card 1 - Quantidade (neutro + detalhe azul)
        // Sombra leve
        $pdf->SetAlpha(0.08);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->RoundedRect(16, $startY + 1, $cardW, $cardH, 4, '1111', 'F');
        $pdf->SetAlpha(1);

        // Card principal
        $pdf->SetDrawColor($line[0], $line[1], $line[2]);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFillColor($paper[0], $paper[1], $paper[2]);
        $pdf->RoundedRect(15, $startY, $cardW, $cardH, 4, '1111', 'DF');

        // Barra lateral
        $pdf->SetFillColor($accent[0], $accent[1], $accent[2]);
        $pdf->RoundedRect(15, $startY, 4, $cardH, 2, '1000', 'F');

        // Conteúdo
        $pdf->SetXY(23, $startY + 8);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->Cell(0, 5, 'TOTAL DE REGISTROS', 0, 1);

        $pdf->SetXY(23, $startY + 16);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
        $pdf->Cell(0, 8, (string)count($dados), 0, 1);

        $pdf->SetXY(23, $startY + 25);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->Cell(0, 4, 'movimentações no período', 0, 1);

        // Card 2 - Valor Total (mesma linguagem visual)
        $cardX2 = 108;

        // Sombra leve
        $pdf->SetAlpha(0.08);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->RoundedRect($cardX2 + 1, $startY + 1, $cardW, $cardH, 4, '1111', 'F');
        $pdf->SetAlpha(1);

        // Card principal
        $pdf->SetDrawColor($line[0], $line[1], $line[2]);
        $pdf->SetFillColor($paper[0], $paper[1], $paper[2]);
        $pdf->RoundedRect($cardX2, $startY, $cardW, $cardH, 4, '1111', 'DF');

        // Barra lateral (mesma cor para reduzir ?colorido?)
        $pdf->SetFillColor($accent[0], $accent[1], $accent[2]);
        $pdf->RoundedRect($cardX2, $startY, 4, $cardH, 2, '1000', 'F');

        // Conteúdo
        $pdf->SetXY($cardX2 + 8, $startY + 8);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->Cell(0, 5, 'VALOR TOTAL', 0, 1);

        $pdf->SetXY($cardX2 + 8, $startY + 16);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
        $pdf->Cell(0, 8, $fmtMoney($total), 0, 1);

        $pdf->SetXY($cardX2 + 8, $startY + 25);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
        $pdf->Cell(0, 4, 'soma de todas as movimentações', 0, 1);

        $pdf->Ln(12);

        // ===== Título da tabela =====
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
        $pdf->Cell(0, 6, 'Detalhamento das Movimentações', 0, 1, 'L');
        $pdf->Ln(2);

        // ===== Tabela (mais ?corporativa?) =====
        $tableX = 15;
        $tableW = 180;

        // Cabeçalho da tabela (cinza claro, texto escuro)
        $pdf->SetFillColor($bgRow[0], $bgRow[1], $bgRow[2]);
        $pdf->SetDrawColor($line[0], $line[1], $line[2]);
        $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
        $pdf->SetFont('helvetica', 'B', 9);

        $colData  = 38;
        $colProd  = 80;
        $colValor = 32;
        $colId    = 30;

        $pdf->SetXY($tableX, $pdf->GetY());
        $pdf->Cell($colData, 9, 'DATA/HORA', 1, 0, 'L', true);
        $pdf->Cell($colProd, 9, 'PRODUTO',   1, 0, 'L', true);
        $pdf->Cell($colValor, 9, 'VALOR',    1, 0, 'R', true);
        $pdf->Cell($colId,   9, 'ID',       1, 1, 'C', true);

        // Linhas da tabela
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
        $pdf->SetDrawColor($line[0], $line[1], $line[2]);

        if (empty($dados)) {
            $pdf->SetFillColor($bgRow[0], $bgRow[1], $bgRow[2]);
            $pdf->Cell($tableW, 12, 'Nenhuma movimentação encontrada para este mês.', 1, 1, 'C', true);
        } else {
            foreach ($dados as $i => $m) {
                // Zebra sutil
                $fill = ($i % 2 === 0);
                if ($fill) {
                    $pdf->SetFillColor($bgRow[0], $bgRow[1], $bgRow[2]);
                } else {
                    $pdf->SetFillColor($paper[0], $paper[1], $paper[2]);
                }

                $data  = $fmtDate($m['created_at'] ?? null);
                $prod  = (string)($m['produto'] ?? '-');
                $valor = $fmtMoney($m['valor_total'] ?? 0);
                $id    = (string)($m['id'] ?? '-');

                $y0 = $pdf->GetY();

                // Data
                $pdf->SetX($tableX);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
                $pdf->Cell($colData, 9, $data, 'LRB', 0, 'L', $fill);

                // Produto (negrito leve)
                $xProd = $pdf->GetX();
                $yProd = $pdf->GetY();
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->MultiCell($colProd, 9, $prod, 'LRB', 'L', $fill, 0);

                // Valor (neutro, sem verde chamativo)
                $pdf->SetXY($xProd + $colProd, $yProd);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);
                $pdf->Cell($colValor, 9, $valor, 'LRB', 0, 'R', $fill);

                // ID (mais discreto)
                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);
                $pdf->Cell($colId, 9, $id, 'LRB', 1, 'C', $fill);

                // reset
                $pdf->SetTextColor($ink[0], $ink[1], $ink[2]);

                // Garante altura mínima
                if ($pdf->GetY() < $y0 + 9) {
                    $pdf->SetY($y0 + 9);
                }
            }
        }

        // ===== Rodapé (discreto) =====
        $pdf->SetY(-25);

        // Linha decorativa discreta
        $pdf->SetDrawColor($accent[0], $accent[1], $accent[2]);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor($muted[0], $muted[1], $muted[2]);

        $geradoEm = 'Documento gerado em ' . date('d/m/Y') . ' às ' . date('H:i');
        $pdf->Cell(0, 4, $geradoEm, 0, 1, 'C');

        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->Cell(0, 4, 'Sistema PDDE - Todos os direitos reservados', 0, 1, 'C');

        return $pdf->Output("relatorio_mensal_{$mes}.pdf", 'S');
    }

}
