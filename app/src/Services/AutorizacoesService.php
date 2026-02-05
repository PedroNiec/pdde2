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
    $supabaseUrl = 'https://fkfkhzfcyuuwvwufhrrj.supabase.co';
    $serviceRoleKey = 'sb_secret_3YMWMTq7PeZBcMw7e2J4YQ_hIuRvMu_';


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
        $pdf = new TCPDF();
        $pdf->SetCreator('PDDE');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Autorização de Fornecimento');

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        $pdf->Write(0, "AUTORIZAÇÃO DE FORNECIMENTO\n\n");
        $pdf->Write(0, "ID da Requisição: {$requisicaoId}\n");
        $pdf->Write(0, "Escola: " . ($dados['escola_nome'] ?? '-') . "\n");
        $pdf->Write(0, "Produto: " . ($dados['produto'] ?? '-') . "\n");
        $pdf->Write(0, "Quantidade: " . ($dados['quantidade'] ?? '-') . "\n");
        $pdf->Write(0, "Fornecedor: " . ($dados['fornecedor_nome'] ?? '-') . "\n");
        $pdf->Write(0, "\nGerado em: " . date('d/m/Y H:i'));

        return $pdf->Output(
            "autorizacao_requisicao_{$requisicaoId}.pdf",
            'S'
        );
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