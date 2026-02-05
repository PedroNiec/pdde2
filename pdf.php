<?php

require 'C:/Users/pedro/OneDrive/Área de Trabalho/pdde1/pdde2/app/libs/TCPDF-main/tcpdf.php';

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, 'TCPDF funcionando no Windows!');

$pdf->Output('teste.pdf', 'I'); // abre no navegador