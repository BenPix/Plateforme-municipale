<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_FPDF extends AlphaPDF
{
    protected $note_id;

    public function __construct ($note_id) {
        $this->note_id = $note_id;
        parent::__construct();
    }

    // En-tête
    function Header()
    {
        // Logo
        $this->Image(FCPATH.'assets/images/logo_entete.png',15,8,30);
        // Police Arial gras 15
        $this->SetFont('Arial','B',15);
        // Décalage en bas
        $this->Ln(5);
        // Décalage à droite
        $this->Cell(45);
        // Titre
        $this->Cell(120,10,iconv("UTF-8", "CP1252", 'NOTE DE SERVICE N°').$this->note_id, 1, 1, 'C');
        // Saut de ligne
        $this->Ln(20);
    }

    // Pied de page
    function Footer()
    {
        // Positionnement à 1,5 cm du bas
        $this->SetY(-15);
        // Police Arial italique 8
        $this->SetFont('Arial','I',8);
        // Numéro de page
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new My_FPDF($note->id);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetTitle(iconv("UTF-8", "CP1252", 'Note de Service N°'.$note->id));



////////////////////////////
// informations générales //
////////////////////////////



$pdf->SetFont('Arial','BU',10);
$pdf->Cell(45,5,iconv("UTF-8", "CP1252", 'Objet de la note:'), 0, 1, 'L');
$pdf->Ln(2);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,5,iconv("UTF-8", "CP1252", $note->objet), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial','BU',10);
$pdf->Cell(45,5,iconv("UTF-8", "CP1252", 'Contenu de la note:'), 0, 1, 'L');
$pdf->Ln(2);
$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,5,iconv("UTF-8", "CP1252", $note->note), 0, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial','I',10);
$consultation = "Note rédigée par ".$note->redacteur." le ".$note->horodateur;
$pdf->MultiCell(0, 5, iconv("UTF-8", "CP1252", $consultation), 0, 'L');
$pdf->Ln(5);



//////////////////
// commentaires //
//////////////////



// Titre
if (count($note->commentaires) > 0) {
    $pdf->SetFont('Arial','BU',10);
    $pdf->Cell(0,5,iconv("UTF-8", "CP1252", 'Commentaires:'), 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFillColor(224,235,255);
    $_commentaire_compteur = 1;
}

foreach ($note->commentaires as $row) {
    // avoiding the cut of this comment section
    $section_start = $pdf->GetY();
    $title_height = 6;
    $comment_height = (substr_count($row->commentaire, "\n") + 1) * 5;
    $section_height = $title_height + $comment_height;
    $section_end = $section_start + $section_height;

    if ($section_end > 265 && $section_height < 265) { // if the section will be cut (and is not as big as the page), we put lines until the bottom
        $pdf->Ln(275 - $section_start);

        if ($_commentaire_compteur > 1) {
            $pdf->SetFont('Arial','BU',10);
            $pdf->Cell(0,5,iconv("UTF-8", "CP1252", 'Commentaires (suite):'), 0, 1, 'L');
            $pdf->Ln(2);
        }
    }

    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(0,6,iconv("UTF-8", "CP1252", $row->utilisateur).' le '.iconv("UTF-8", "CP1252", $row->horodateur),0,1,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->MultiCell(0,5,iconv("UTF-8", "CP1252", $row->commentaire),0,'L',true);

    $pdf->Ln();

    $_commentaire_compteur++;
}



/////////////////////////////
// tableau des validations //
/////////////////////////////



// avoiding the cut of the validation section
$section_start = $pdf->GetY();
$title_height = 5 + 2; // title + space
$table_header_height = 6;
$table_rows_height = (count($note->workflow)) * 10;
$section_height = $title_height + $table_header_height + $table_rows_height;
$section_end = $section_start + $section_height;

if ($section_end > 265 && $section_height < 265) { // if the section will be cut (and is not as big as the page), we put lines until the bottom
    $pdf->Ln(275 - $section_start);
}

// Titre
$pdf->SetFont('Arial','BU',10);
$pdf->Cell(0,5,iconv("UTF-8", "CP1252", 'Validations:'), 0, 1, 'L');
$pdf->Ln(2);
// Titres des colonnes
$header = array('Étape', 'Nom', 'Validation', 'Date');
// Couleurs, épaisseur du trait et police grasse
$pdf->SetFillColor(128,128,128);
$pdf->SetTextColor(255);
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(.3);
$pdf->SetFont('','B');
// En-tête
$pdf->Cell(15,7,iconv("UTF-8", "CP1252", $header[0]),1,0,'C',true);
$pdf->Cell(80,7,iconv("UTF-8", "CP1252", $header[1]),1,0,'C',true);
$pdf->Cell(55,7,iconv("UTF-8", "CP1252", $header[2]),1,0,'C',true);
$pdf->Cell(40,7,iconv("UTF-8", "CP1252", $header[3]),1,0,'C',true);
$pdf->Ln();
// Restauration des couleurs et de la police
$pdf->SetFillColor(224,235,255);
$pdf->SetTextColor(0);
$pdf->SetFont('');
// Données
$fill = false;
$compteur = 1;

foreach ($note->workflow as $row) {
    $pdf->Cell(15,10,iconv("UTF-8", "CP1252", $row->etape),'LR',0,'C',$fill);
    $pdf->Cell(80,10,iconv("UTF-8", "CP1252", $row->workflow_utilisateur),'LR',0,'L',$fill);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell(55,10,'','LR',0,'C',$fill);

    $pdf->Image(FCPATH.'uploads/signatures/checkmark.png',$x+22,$y,11);
    /*
    * if signatures are inculded, use this below instead of the line just before
    *
    // fichier de la signature
    $signature = FCPATH.'signatures/signature'.$row->signature.'.png';

    if (file_exists($signature))
        $pdf->Image($signature,$x+0.4,$y+0.4,43.6,9.5);
    else
        $pdf->Image(FCPATH.'signatures/checkmark.png',$x+15,$y,11);
    */

    $pdf->Cell(40,10,$row->validation_date,'LR',0,'C',$fill);
    $pdf->Ln();
    $fill = !$fill;
    $compteur++;

    
}
// Trait de terminaison
$pdf->Cell(190,0,'','T');
$pdf->Ln(5);



////////////
// sortie //
////////////



$pdf->Output('', 'note_de_service'.$note->id);
