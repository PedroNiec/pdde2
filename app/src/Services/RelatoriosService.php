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

    public function relatorioMensal(string $mes): void
    {
        $dados = $this->repository->relatorioMensal($mes);
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

        // ===== Paleta de cores moderna =====
        $primary     = [24, 90, 157];      // Azul profundo
        $primaryDark = [18, 70, 130];      // Azul escuro
        $accent      = [0, 184, 148];      // Verde-água
        $accentDark  = [0, 150, 120];      // Verde-água escuro
        $cardBg      = [255, 255, 255];    // Branco
        $bgLight     = [248, 249, 250];    // Cinza claro
        $border      = [222, 226, 230];    // Cinza borda
        $text        = [33, 37, 41];       // Texto escuro
        $textLight   = [108, 117, 125];    // Texto claro
        $success     = [40, 167, 69];      // Verde sucesso
        $highlight   = [255, 193, 7];      // Amarelo destaque

        // ===== Cabeçalho com gradiente simulado =====
        // Camadas de retângulos para simular gradiente
        for ($i = 0; $i < 40; $i++) {
            $ratio = $i / 40;
            $r = $primary[0] + ($primaryDark[0] - $primary[0]) * $ratio;
            $g = $primary[1] + ($primaryDark[1] - $primary[1]) * $ratio;
            $b = $primary[2] + ($primaryDark[2] - $primary[2]) * $ratio;

            $pdf->SetFillColor($r, $g, $b);
            $pdf->Rect(0, $i, 210, 1, 'F');
        }

        // Linha de destaque no topo
        $pdf->SetFillColor($highlight[0], $highlight[1], $highlight[2]);
        $pdf->Rect(0, 0, 210, 3, 'F');

        // Elemento decorativo lateral
        $pdf->SetFillColor($accent[0], $accent[1], $accent[2]);
        $pdf->SetAlpha(0.15);
        $pdf->Circle(200, 10, 35, 0, 360, 'F');
        $pdf->Circle(10, 35, 25, 0, 360, 'F');
        $pdf->SetAlpha(1);

        // Título principal
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetXY(15, 12);
        $pdf->Cell(180, 8, 'RELATÓRIO MENSAL', 0, 1, 'C');

        // Subtítulo
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetAlpha(0.9);
        $pdf->SetXY(15, 22);
        $pdf->Cell(180, 5, 'Análise detalhada de movimentações financeiras', 0, 1, 'C');
        $pdf->SetAlpha(1);

        // Formatação do mês
        $mesExibicao = $mes;
        if (preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $meses = [
                '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
            ];
            $mesNum = substr($mes, 5, 2);
            $ano = substr($mes, 0, 4);
            $mesExibicao = ($meses[$mesNum] ?? $mesNum) . ' de ' . $ano;
        }

        // Badge do mês
        $pdf->SetFillColor($accent[0], $accent[1], $accent[2]);
        $pdf->SetDrawColor($accent[0], $accent[1], $accent[2]);
        $pdf->RoundedRect(70, 30, 70, 7, 3.5, '1111', 'DF');

        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(70, 31.5);
        $pdf->Cell(70, 5, $mesExibicao, 0, 1, 'C');

        $pdf->Ln(12);

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

        // ===== Cards de resumo modernos =====
        $total = 0.0;
        foreach ($dados as $m) {
            $total += (float)($m['valor_total'] ?? 0);
        }

        $startY = $pdf->GetY();

        // Card 1 - Quantidade
        $cardW = 87;
        $cardH = 32;

        // Sombra do card
        $pdf->SetAlpha(0.1);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->RoundedRect(16, $startY + 1, $cardW, $cardH, 4, '1111', 'F');
        $pdf->SetAlpha(1);

        // Card principal
        $pdf->SetDrawColor($border[0], $border[1], $border[2]);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFillColor($cardBg[0], $cardBg[1], $cardBg[2]);
        $pdf->RoundedRect(15, $startY, $cardW, $cardH, 4, '1111', 'DF');

        // Barra lateral colorida
        $pdf->SetFillColor($primary[0], $primary[1], $primary[2]);
        $pdf->RoundedRect(15, $startY, 4, $cardH, 2, '1000', 'F');

        // Ícone de fundo
        $pdf->SetAlpha(0.08);
        $pdf->SetFillColor($primary[0], $primary[1], $primary[2]);
        $pdf->Circle(90, $startY + 16, 18, 0, 360, 'F');
        $pdf->SetAlpha(1);

        // Conteúdo
        $pdf->SetXY(23, $startY + 8);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
        $pdf->Cell(0, 5, 'TOTAL DE REGISTROS', 0, 1);

        $pdf->SetXY(23, $startY + 16);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor($primary[0], $primary[1], $primary[2]);
        $pdf->Cell(0, 8, (string)count($dados), 0, 1);

        $pdf->SetXY(23, $startY + 25);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
        $pdf->Cell(0, 4, 'movimentações no período', 0, 1);

        // Card 2 - Valor Total
        $cardX2 = 108;

        // Sombra
        $pdf->SetAlpha(0.1);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->RoundedRect($cardX2 + 1, $startY + 1, $cardW, $cardH, 4, '1111', 'F');
        $pdf->SetAlpha(1);

        // Card principal
        $pdf->SetDrawColor($border[0], $border[1], $border[2]);
        $pdf->SetFillColor($cardBg[0], $cardBg[1], $cardBg[2]);
        $pdf->RoundedRect($cardX2, $startY, $cardW, $cardH, 4, '1111', 'DF');

        // Barra lateral
        $pdf->SetFillColor($success[0], $success[1], $success[2]);
        $pdf->RoundedRect($cardX2, $startY, 4, $cardH, 2, '1000', 'F');

        // Ícone de fundo
        $pdf->SetAlpha(0.08);
        $pdf->SetFillColor($success[0], $success[1], $success[2]);
        $pdf->Circle(183, $startY + 16, 18, 0, 360, 'F');
        $pdf->SetAlpha(1);

        // Conteúdo
        $pdf->SetXY($cardX2 + 8, $startY + 8);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
        $pdf->Cell(0, 5, 'VALOR TOTAL', 0, 1);

        $pdf->SetXY($cardX2 + 8, $startY + 16);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor($success[0], $success[1], $success[2]);
        $pdf->Cell(0, 8, $fmtMoney($total), 0, 1);

        $pdf->SetXY($cardX2 + 8, $startY + 25);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
        $pdf->Cell(0, 4, 'soma de todas as movimentações', 0, 1);

        $pdf->Ln(12);

        // ===== Título da tabela =====
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor($text[0], $text[1], $text[2]);
        $pdf->Cell(0, 6, 'Detalhamento das Movimentações', 0, 1, 'L');
        $pdf->Ln(2);

        // ===== Tabela moderna =====
        $tableX = 15;
        $tableW = 180;

        // Cabeçalho da tabela
        $pdf->SetFillColor($primary[0], $primary[1], $primary[2]);
        $pdf->SetDrawColor($primary[0], $primary[1], $primary[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 9);

        $colData  = 38;
        $colProd  = 80;
        $colValor = 32;
        $colId    = 30;

        $pdf->SetXY($tableX, $pdf->GetY());
        $pdf->Cell($colData, 9, 'DATA/HORA', 1, 0, 'L', true);
        $pdf->Cell($colProd, 9, 'PRODUTO', 1, 0, 'L', true);
        $pdf->Cell($colValor, 9, 'VALOR', 1, 0, 'R', true);
        $pdf->Cell($colId, 9, 'ID', 1, 1, 'C', true);

        // Linhas da tabela
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor($text[0], $text[1], $text[2]);
        $pdf->SetDrawColor($border[0], $border[1], $border[2]);

        if (empty($dados)) {
            $pdf->SetFillColor($bgLight[0], $bgLight[1], $bgLight[2]);
            $pdf->Cell($tableW, 12, 'Nenhuma movimentação encontrada para este mês.', 1, 1, 'C', true);
        } else {
            foreach ($dados as $i => $m) {
                // Alternância de cores mais sutil
                $fill = ($i % 2 === 0);
                if ($fill) {
                    $pdf->SetFillColor($bgLight[0], $bgLight[1], $bgLight[2]);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }

                $data  = $fmtDate($m['created_at'] ?? null);
                $prod  = (string)($m['produto'] ?? '-');
                $valor = $fmtMoney($m['valor_total'] ?? 0);
                $id    = (string)($m['id'] ?? '-');

                $y0 = $pdf->GetY();

                // Data
                $pdf->SetX($tableX);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell($colData, 9, $data, 'LRB', 0, 'L', $fill);

                // Produto
                $xProd = $pdf->GetX();
                $yProd = $pdf->GetY();
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->MultiCell($colProd, 9, $prod, 'LRB', 'L', $fill, 0);

                // Valor
                $pdf->SetXY($xProd + $colProd, $yProd);
                $pdf->SetFont('helvetica', 'B', 9);
                $pdf->SetTextColor($success[0], $success[1], $success[2]);
                $pdf->Cell($colValor, 9, $valor, 'LRB', 0, 'R', $fill);

                // ID
                $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell($colId, 9, $id, 'LRB', 1, 'C', $fill);

                $pdf->SetTextColor($text[0], $text[1], $text[2]);

                // Garante altura mínima
                if ($pdf->GetY() < $y0 + 9) {
                    $pdf->SetY($y0 + 9);
                }
            }
        }

        // ===== Rodapé elegante =====
        $pdf->SetY(-25);

        // Linha decorativa
        $pdf->SetDrawColor($accent[0], $accent[1], $accent[2]);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

        $pdf->Ln(3);

        // Informações do rodapé
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);

        $geradoEm = 'Documento gerado em ' . date('d/m/Y') . ' às ' . date('H:i');
        $pdf->Cell(0, 4, $geradoEm, 0, 1, 'C');

        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->Cell(0, 4, 'Sistema PDDE - Todos os direitos reservados', 0, 1, 'C');

        return $pdf->Output("relatorio_mensal_{$mes}.pdf", 'S');
    }
}
