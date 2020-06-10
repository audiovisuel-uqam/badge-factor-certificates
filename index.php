<?php // Silence is golden
// ini_set('display_errors', 'on');
// error_reporting(E_ALL | E_STRICT);
// //echo 'hello world';
//
// use setasign\Fpdi\Tfpdf\Fpdi;
// require_once( 'vendor/autoload.php');
// $pdf_path = '../../uploads/2017/05/certificat-Concept-et-compétences.pdf';
// $pdf_file = ltrim(parse_url($pdf_path, PHP_URL_PATH), '/');
//
// //echo $pdf_path;
// $pdf = new Fpdi();
//
// $pdf->AddPage('L');
// $pdf->setSourceFile($pdf_file);
// $templateId = $pdf->importPage(1);
//
// $size = $pdf->getTemplateSize($templateId);
// $w = $size['width'];
// $h = $size['height'];
//
// $pdf->useTemplate($templateId, ['adjustPageSize' => true]);
//
// $pdf->SetFont('Arial','B',16);
// $pdf->Cell(40,10,'Hello World!');
// $pdf->Output();
// // //exit;
