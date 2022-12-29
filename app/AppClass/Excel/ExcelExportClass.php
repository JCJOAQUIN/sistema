<?php

namespace App\AppClass\Excel;
use Excel;
use App\AppClass\Excel\SheetExcel;

class ExcelExportClass {

    public $sheetsExcel = array();
	public $name;
    public function __construct($name){
		$this->name=$name;
    }

    public function AddSheets($sheetsExcel){
        $this->sheetsExcel[]=$sheetsExcel;
    }

    public function DownloadExcel(){
        Excel::create($this->name, function($excel){
            foreach($this->sheetsExcel as $item){

                $excel= $this->sheetExcel($excel,$item);
            }    
        })->export('xlsx');
    }

    public function sheetExcel($excel,$sheetObject){
		$excel->sheet($sheetObject->name,function($sheet) use ($sheetObject){
            $sheetObject->ChangeLabelsToAlphabet();
			
			$sheet->setStyle([
				'font' => [
					'name'	=> 'Calibri',
					'size'	=> 12
				],
				'alignment' => [
					'vertical' => 'center',
				]
			]);
			$label=$sheetObject->labelsNew;
			$colorFont=$sheetObject->colorFont;
			$backgroundColor=$sheetObject->backgroundColor;
			for ($i=0;$i<count($label);$i++) {
				$sheet->mergeCells($label[$i][0].'1:'.$label[$i][1].'1');
				$sheet->cell($label[$i][0].'1:'.$label[$i][1].'2', function($cells) use ($i,$colorFont,$backgroundColor){
					$cells->setBackground($backgroundColor[$i]);
					$cells->setFontColor($colorFont[$i]);
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
				});
			}

			foreach($sheetObject->stylesCustom as $item){
				$sheet->cell($item[0], function($cells) use ($item){
					$cells->setBackground($item[1]);
					$cells->setFontColor($item[2]);
					$cells->setFontWeight('bold');
					$cells->setAlignment('center');
					$cells->setFont(array('family' => 'Calibri','size' => '16','bold' => true));
				});
			}
			

			
			$titles		=$sheetObject->titles;
			$subTitles	=$sheetObject->subTitles;
			$head 		= array();
			$subHead	= array();
			for($i=0;$i<count($titles);$i++){
				$head[]=$titles[$i];
				for($x=0;$x<count($subTitles[$i]);$x++){
					if($x!=0)$head[]='';
					$subHead[]=$subTitles[$i][$x];
				}
			}

			$sheet->row(1,$head);
			$sheet->row(2,$subHead);

			$sheetObject->labels[0][1];
			$columns=array();
			for($i=0;$i<=$sheetObject->labels[0][1];$i++){
				$columns[]=$sheetObject->ChangeNumbersToLetters($i);
			}

			foreach($sheetObject->content as $item){
				$sheet->appendRow($item['row']);
				if($item['mixes']!=null){
					$sheet->setMergeColumn(array(
						'columns'   => $columns,	
						'rows'      => array($item['mixes']),
					));
				}
			}
			$sheet->cell('A3:'.$label[count($label)-1][1].''.(3+count($sheetObject->content)),function($cell){//a la hora de convertir en clase el 3 tiene que se una variable relacionada con los encabezados
				$cell->setAlignment('justify');
			});
		});
		return $excel;
	}
    
}