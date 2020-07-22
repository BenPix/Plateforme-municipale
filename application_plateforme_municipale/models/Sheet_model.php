<?php  
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// -----------------------------------------------------------------------------

class Sheet_model extends CI_Model
{

	public function designTheSheet($sheet)
	{
		$sheet->getStyle('A1:H1')->getFont()->setUnderline(true);
		$sheet->getStyle('A1:H500')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A1:H500')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
		$sheet->getStyle('E1:E500')->getAlignment()->setWrapText(TRUE);
		$sheet->getStyle('A1:H1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setWidth(80);
		$sheet->getColumnDimension('F')->setAutoSize(true);
		$sheet->getColumnDimension('G')->setAutoSize(true);
		$sheet->getColumnDimension('H')->setAutoSize(true);
		$sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
		$sheet->getStyle('A1:H1')->getFill()->getStartColor()->setARGB('ffcccccc');
		
		return $sheet;
	}

	public function designTheSheetConge($sheet)
	{
		$sheet->getStyle('A1:E1')->getFont()->setUnderline(true);
		$sheet->getStyle('A1:E500')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A1:E500')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
		$sheet->getStyle('A1:E1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getColumnDimension('E')->setAutoSize(true);
		$sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
		$sheet->getStyle('A1:E1')->getFill()->getStartColor()->setARGB('ffcccccc');
		
		return $sheet;
	}



	public function designTheSheetCongeAgent($sheet)
	{
		$sheet->getStyle('A1:D1')->getFont()->setUnderline(true);
		$sheet->getStyle('A1:D500')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
		$sheet->getStyle('A1:D500')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
		$sheet->getStyle('A1:D1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
		$sheet->getColumnDimension('A')->setAutoSize(true);
		$sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);
		$sheet->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
		$sheet->getStyle('A1:D1')->getFill()->getStartColor()->setARGB('ffcccccc');
		
		return $sheet;
	}

	public function sautDeLigne($tab)
	{
		foreach ($tab->result_id as $array) {
			foreach ($array as $key => $value) {
				//echo var_dump($array).'<br>';
				if ($key == 'demande') {
					$array[$key] = nl2br($value);
					//echo ($key).' ------ '.($value).'<br>';
				}
			}
		}
		return $tab;
	}

}