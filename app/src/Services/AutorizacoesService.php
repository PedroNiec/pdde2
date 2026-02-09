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
         

        $this->repository->criarAutorizacao($requisicaoId, $dados['oferta_selecionada_id'] ?? '', $publicUrl, $dados['fornecedor_id']);



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

        // ===== Helpers =====
        $v = function (string $key, string $fallback = '-') use ($dados): string {
            $value = $dados[$key] ?? $fallback;
            $value = is_scalar($value) ? (string)$value : $fallback;
            $value = trim($value);
            return $value !== '' ? $value : $fallback;
        };

        $onlyDigits = fn(string $s): string => preg_replace('/\D+/', '', $s);

        $fmtMoney = function ($valor): string {
            $n = (float) str_replace(',', '.', (string)($valor ?? 0));
            return 'R$ ' . number_format($n, 2, ',', '.');
        };

        $fmtQtd = function ($qtd): string {
            $n = (float) str_replace(',', '.', (string)($qtd ?? 0));
            // se for inteiro, não mostra casas
            return (floor($n) == $n) ? (string)(int)$n : str_replace('.', ',', rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.'));
        };

        // ===== Número/Ano no padrão do modelo =====
        // Se quiser puxar o ano do PDDE/registro, me diga onde ele vem.
        $ano = date('Y');

        // Você pode querer zero-padding no Nº:
        // ex: 12 -> 012
        $numFormatado = str_pad($onlyDigits($requisicaoId), 3, '0', STR_PAD_LEFT);

        // ===== TÍTULO (igual ao modelo) =====
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('times', 'B', 18);
        $pdf->MultiCell(0, 8, "AUTORIZAÇÃO DE\nFORNECIMENTO Nº {$numFormatado}/{$ano}", 0, 'L', false, 1);

        $pdf->Ln(2);

        // ===== 1. DADOS DA UNIDADE ESCOLAR =====
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(0, 7, '1. DADOS DA UNIDADE ESCOLAR', 0, 1, 'L');
        $pdf->Ln(1);

        $pdf->SetFont('times', '', 11);

        $linhasEscola = [
            ['Nome da Escola:', $v('escola_nome')],
            ['Endereço:', $v('endereco_escola')],
            ['Telefone:', $v('telefone_escola')],
            // você não tem e-mail no array; deixei como "-"
            ['E-mail:', $v('email_escola', '-')],
            ['Responsável pelo Contato:', $v('responsavel_escola')],
        ];

        foreach ($linhasEscola as [$label, $value]) {
            $pdf->Cell(5, 6, chr(149), 0, 0, 'L'); // •
            $pdf->SetFont('times', 'B', 11);
            $pdf->Cell(55, 6, $label, 0, 0, 'L');
            $pdf->SetFont('times', '', 11);
            $pdf->MultiCell(0, 6, $value, 0, 'L', false, 1);
        }

        $pdf->Ln(4);

        // ===== 2. DADOS DO FORNECEDOR =====
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(0, 7, '2. DADOS DO FORNECEDOR', 0, 1, 'L');
        $pdf->Ln(1);

        $pdf->SetFont('times', '', 11);

        $linhasFornecedor = [
            ['Razão Social/Nome:', $v('fornecedor_nome')],
            ['CNPJ/CPF:', $v('cnpj_fornecedor')],
            ['Endereço:', $v('endereco_fornecedor')],
            ['Telefone:', $v('telefone_fornecedor')],
            // não tem email no array; deixei "-"
            ['E-mail:', $v('email_fornecedor', '-')],
            // usei responsavel_fornecedor como "Contato"
            ['Contato:', $v('responsavel_fornecedor')],
        ];

        foreach ($linhasFornecedor as [$label, $value]) {
            $pdf->Cell(5, 6, chr(149), 0, 0, 'L'); // •
            $pdf->SetFont('times', 'B', 11);
            $pdf->Cell(55, 6, $label, 0, 0, 'L');
            $pdf->SetFont('times', '', 11);
            $pdf->MultiCell(0, 6, $value, 0, 'L', false, 1);
        }

        $pdf->Ln(6);

        // ===== 3. DESCRIÇÃO DOS ITENS =====
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(0, 7, '3. DESCRIÇÃO DOS ITENS', 0, 1, 'L');
        $pdf->Ln(3);

        // ===== TABELA =====
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);

        // Larguras (total 180mm)
        $wItem  = 12;
        $wDesc  = 80;
        $wUnid  = 14;
        $wQtd   = 14;
        $wUnit  = 30;
        $wTotal = 30;

        // Cabeçalho da tabela
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($wItem, 7, 'ITEM', 1, 0, 'C');
        $pdf->Cell($wDesc, 7, 'DESCRIÇÃO DOS PRODUTOS / SERVIÇOS', 1, 0, 'C');
        $pdf->Cell($wUnid, 7, 'UNID.', 1, 0, 'C');
        $pdf->Cell($wQtd, 7, 'QTD.', 1, 0, 'C');
        $pdf->Cell($wUnit, 7, 'VALOR UNIT. (R$)', 1, 0, 'C');
        $pdf->Cell($wTotal, 7, 'VALOR TOTAL (R$)', 1, 1, 'C');

        $pdf->SetFont('times', '', 9);

        // Seu "item" (você tem só um produto no array)
        $descricao = $v('produto', '');
        $unid      = $v('unidade', 'UN'); // você não tem unidade; padrão "UN"
        $qtd       = $v('quantidade', '');
        $valorUnit = (float)($dados['valor_unitario'] ?? 0);
        $valorTot  = (float)($dados['valor_total'] ?? ($valorUnit * (float)$qtd));

        // Vamos imprimir 5 linhas (modelo), preenchendo a 1ª
        $totalGeral = 0.0;
        $linhasFixas = 5;

        for ($i = 1; $i <= $linhasFixas; $i++) {
            $isPrimeira = ($i === 1);

            $descLinha  = $isPrimeira ? $descricao : '';
            $unidLinha  = $isPrimeira ? $unid : '';
            $qtdLinha   = $isPrimeira ? $fmtQtd($qtd) : '';
            $unitLinha  = $isPrimeira ? $fmtMoney($valorUnit) : '';
            $totLinha   = $isPrimeira ? $fmtMoney($valorTot) : '';

            if ($isPrimeira) {
                $totalGeral += $valorTot;
            }

            // --- Altura dinâmica da linha baseada na descrição ---
            // define a fonte antes de medir
            $pdf->SetFont('times', '', 9);

            // altura mínima padrão da linha
            $minH = 8;

            // calcula altura necessária para a descrição (considerando quebra de linha)
            $hDesc = $pdf->getStringHeight($wDesc, $descLinha, false, true, '', 1);

            // garante que a linha tenha altura mínima
            $rowH = max($minH, $hDesc);

            // se estiver perto do fim da página, quebra antes pra não “cortar” borda
            if ($pdf->GetY() + $rowH > ($pdf->getPageHeight() - $pdf->getBreakMargin())) {
                $pdf->AddPage();
                // (opcional) se quiser repetir cabeçalho da tabela aqui, eu te passo também
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // ITEM
            $pdf->Cell($wItem, $rowH, str_pad((string)$i, 2, '0', STR_PAD_LEFT), 1, 0, 'C');

            // DESCRIÇÃO (MultiCell com a mesma altura da linha)
            $pdf->SetXY($x + $wItem, $y);
            $pdf->MultiCell($wDesc, $rowH, $descLinha, 1, 'L', false, 0);

            // UNID.
            $pdf->SetXY($x + $wItem + $wDesc, $y);
            $pdf->Cell($wUnid, $rowH, $unidLinha, 1, 0, 'C');

            // QTD.
            $pdf->Cell($wQtd, $rowH, $qtdLinha, 1, 0, 'C');

            // VALOR UNIT.
            $pdf->Cell($wUnit, $rowH, $unitLinha, 1, 0, 'R');

            // VALOR TOTAL
            $pdf->Cell($wTotal, $rowH, $totLinha, 1, 1, 'R');
        }


        // Linha TOTAL GERAL DA REQUISIÇÃO (modelo)
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell($wItem + $wDesc + $wUnid + $wQtd + $wUnit, 8, 'TOTAL GERAL DA REQUISIÇÃO', 1, 0, 'L');
        $pdf->Cell($wTotal, 8, $fmtMoney($totalGeral), 1, 1, 'R');

        $pdf->Ln(14);

        // ===== Assinaturas (modelo) =====
        $pdf->SetFont('times', '', 11);

        $pdf->Cell(0, 6, 'Local/Data', 0, 1, 'L');
        $pdf->Ln(14);
        $pdf->Cell(0, 6, 'Responsável pela autorização', 0, 1, 'L');

        return $pdf->Output("autorizacao_fornecimento_{$numFormatado}_{$ano}.pdf", 'S');
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