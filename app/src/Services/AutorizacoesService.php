<?php 

declare(strict_types=1);

require_once __DIR__ . '/../Repositories/AutorizacoesRepository.php';
require_once __DIR__ . '/../../libs/TCPDF-main/tcpdf.php';

const BUCKET = 'autorizacoes';


class AutorizacoesService
{
    public function __construct(private AutorizacoesRepository $repository) {}

    public function criarAutorizacao(string $requisicaoId, array $dados): void
    {
    $supabaseUrl = '';
    $serviceRoleKey = '';

        $pdfBytes = $this->criarPdfAutorizacao($requisicaoId, $dados);

        $filename = "autorizacao_requisicao_{$requisicaoId}.pdf";
        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = "autorizacao_requisicao_{$requisicaoId}.pdf";

        $path = "autorizacoes/{$filename}";

        $publicUrl =
           $supabaseUrl .
           "/storage/v1/object/public/autorizacoes/" .
           $path;

        $this->uploadPdfSupabase($pdfBytes, BUCKET, $path, $supabaseUrl, $serviceRoleKey);
         

        $this->repository->criarAutorizacao($requisicaoId, $dados['oferta_selecionada_id'] ?? '', $publicUrl);



        if (ob_get_length()) { ob_end_clean(); }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfBytes));
        header('Cache-Control: private');
        header('Pragma: public');

    }

    public function criarPdfAutorizacao(string $requisicaoId, array $dados): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('PDDE');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Autorização de Fornecimento');

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        $primary = [41, 128, 185];
        $secondary = [52, 152, 219];
        $lightBg = [236, 240, 241];
        $cardBg = [255, 255, 255];
        $border = [189, 195, 199];
        $text = [44, 62, 80];
        $textLight = [127, 140, 141];
        $success = [46, 204, 113];

        // === CABEÇALHO COM GRADIENTE ===
        $pdf->SetFillColor($primary[0], $primary[1], $primary[2]);
        $pdf->Rect(0, 0, 210, 50, 'F');

        // Detalhe decorativo
        $pdf->SetFillColor($secondary[0], $secondary[1], $secondary[2]);
        $pdf->Rect(0, 45, 210, 5, 'F');

        // Título principal
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetXY(15, 18);
        $pdf->Cell(180, 8, 'AUTORIZAÇÃO DE FORNECIMENTO', 0, 1, 'C');

        // Subtítulo
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetXY(15, 28);
        $pdf->Cell(180, 6, 'Programa Dinheiro Direto na Escola', 0, 1, 'C');

        // Número da requisição em destaque
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(15, 36);
        $pdf->Cell(180, 6, 'Nº ' . $requisicaoId, 0, 1, 'C');

        $pdf->Ln(8);

        // === HELPER FUNCTION ===
        $v = function ($key, $fallback = '-') use ($dados) {
            $value = $dados[$key] ?? $fallback;
            $value = is_scalar($value) ? (string)$value : $fallback;
            $value = trim($value);
            return $value !== '' ? $value : $fallback;
        };

        // === CARD PRINCIPAL COM INFORMAÇÕES ===
        $startY = $pdf->GetY();
        $cardPadding = 8;
        $cardWidth = 180;

        // Sombra suave
        $pdf->SetAlpha(0.1);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->RoundedRect(16, $startY + 1, $cardWidth, 90, 4, '1111', 'F');
        $pdf->SetAlpha(1);

        // Card branco
        $pdf->SetDrawColor($border[0], $border[1], $border[2]);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFillColor($cardBg[0], $cardBg[1], $cardBg[2]);
        $pdf->RoundedRect(15, $startY, $cardWidth, 90, 4, '1111', 'DF');

        // Barra lateral colorida
        $pdf->SetFillColor($success[0], $success[1], $success[2]);
        $pdf->Rect(15, $startY, 4, 90, 'F');

        // Título da seção
        $pdf->SetXY(15 + $cardPadding, $startY + $cardPadding);
        $pdf->SetTextColor($primary[0], $primary[1], $primary[2]);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'DADOS DA AUTORIZAÇÃO', 0, 1);

        $pdf->Ln(4);

        // === INFORMAÇÕES EM GRID ===
        $labelW = 50;
        $valueW = $cardWidth - ($cardPadding * 2) - $labelW - 4; // 4 pela barra lateral
        $rowHeight = 12;
        $currentY = $pdf->GetY();

        $rows = [
            ['Escola',     $v('escola_nome'), 'school'],
            ['Produto',    $v('produto'), 'package'],
            ['Quantidade', $v('quantidade'), 'calculator'],
            ['Fornecedor', $v('fornecedor_nome'), 'truck'],
            ['Valor total', $v('valor_total_formatado'), 'money'],
        ];

        $pdf->SetFont('helvetica', '', 10);

        foreach ($rows as $index => [$label, $value, $icon]) {
            $rowY = $currentY + ($index * $rowHeight);

            // Fundo alternado para melhor leitura
            if ($index % 2 === 0) {
                $pdf->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
                $pdf->Rect(15 + 4, $rowY - 2, $cardWidth - 4, $rowHeight, 'F');
            }

            $pdf->SetXY(15 + $cardPadding + 4, $rowY);

            // Label
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
            $pdf->Cell($labelW, $rowHeight - 4, $label, 0, 0, 'L');

            // Value
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor($text[0], $text[1], $text[2]);

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Quebra de linha automática para textos longos
            $pdf->MultiCell($valueW, 5, $value, 0, 'L', false, 1);

            // Ajusta para próxima linha se MultiCell ocupou mais espaço
            $heightUsed = $pdf->GetY() - $y;
            if ($heightUsed > ($rowHeight - 4)) {
                $currentY += $heightUsed - ($rowHeight - 4);
            }
        }

        // === RODAPÉ DO CARD ===
        $pdf->SetY($startY + 90 - 18);
        $pdf->SetX(15 + $cardPadding + 4);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
        $pdf->MultiCell(
            $cardWidth - ($cardPadding * 2) - 4,
            4,
            "Documento gerado eletronicamente em " . date('d/m/Y') . " às " . date('H:i'),
            0,
            'L',
            false,
            1
        );

        // === SEÇÃO DE ASSINATURA ===
        $pdf->Ln(15);
        $signY = $pdf->GetY();

        // Box de assinatura
        $pdf->SetDrawColor($border[0], $border[1], $border[2]);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $pdf->RoundedRect(15, $signY, 180, 40, 4, '1111', 'D');

        // Linha de assinatura
        $lineY = $signY + 25;
        $pdf->SetDrawColor($text[0], $text[1], $text[2]);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(50, $lineY, 175, $lineY);

        // Labels
        $pdf->SetXY(15, $signY + 5);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor($text[0], $text[1], $text[2]);
        $pdf->Cell(180, 5, 'ASSINATURA E CARIMBO', 0, 1, 'C');

        $pdf->SetXY(15, $lineY + 3);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);
        $pdf->Cell(180, 5, 'Responsável pelo recebimento', 0, 1, 'C');

        // === INFORMAÇÕES DE RODAPÉ ===
        $pdf->SetY(-25);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor($textLight[0], $textLight[1], $textLight[2]);

        // Linha separadora
        $pdf->SetDrawColor($border[0], $border[1], $border[2]);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());

        $pdf->Ln(2);
        $pdf->Cell(0, 4, 'Este documento possui validade jurídica e deve ser arquivado conforme legislação vigente.', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Página 1 de 1', 0, 1, 'C');

        return $pdf->Output("autorizacao_requisicao_{$requisicaoId}.pdf", 'S');
    }


    function uploadPdfSupabase(
        string $pdfBytes,
        string $bucket,
        string $path,
        string $supabaseUrl,
        string $serviceRoleKey
    ): void {
                $url = rtrim($supabaseUrl, '/') . "/storage/v1/object/{$bucket}/{$path}";

                $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,

                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $pdfBytes,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$serviceRoleKey}",
                    "apikey: {$serviceRoleKey}",
                    "Content-Type: application/pdf",
                    "x-upsert: true",
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new RuntimeException("Erro cURL: {$error}");
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                throw new RuntimeException("Erro Supabase ({$httpCode}): {$response}");
            }

        }



}