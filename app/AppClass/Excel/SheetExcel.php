<?php

namespace App\AppClass\Excel;
use Excel;

class SheetExcel {
    public $alphabet        = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    public $titles          = array();
    public $subTitles       = array();
    public $content         = array();
    public $colorFont       = array();
    public $backgroundColor = array();
    public $stylesCustom    = array();
    public $labels;
    public $labelsNew;
    public $name;
    
    public function __construct($content,$name){
        $this->content    = $content;
        $this->name         = $name;
    }

    public function AddHead($title,array $subTitles,$backgroundColor=null,$colorFont=null){
        $this->titles[]             = $title;
        $this->subTitles[]          = $subTitles;
        $this->colorFont[]          = ($colorFont!=null) ? $colorFont :'#ffffff';
        $this->backgroundColor[]    = ($backgroundColor!=null) ? $backgroundColor :'#000000';
        $this->GetLabels($subTitles);
    }

    public function GetLabels($subTitles){
        $labels=$this->labels;
        $sizeSubTitles= count($subTitles);
        if(!isset($labels)){
            $init    = 0;
            $final   = $sizeSubTitles-1;
            $labels  = array(array($init,$final));
        }else{
            $init   = $labels[count($labels)-1][1]+1;
            $final  = $init+$sizeSubTitles-1;
        }
        $this->labels[]=[$init,$final];
    }

    public function ChangeLabelsToAlphabet(){
        $labels     =$this->labels;
        $alphabet   =$this->alphabet;
        $sizeLabels=count($labels);

        for($i=0;$i<$sizeLabels;$i++){
            $labels[$i][0]=$this->ChangeNumbersToLetters($labels[$i][0]);
            $labels[$i][1]=$this->ChangeNumbersToLetters($labels[$i][1]);
        }
        $this->labelsNew=$labels;
    }

    public function ChangeNumbersToLetters($number){
        $alphabet                   = $this->alphabet;
        $sizeSubTitlesAlfabeto      = count($alphabet);
        $numberUnits                = $this->DivisionTrucate($number,$sizeSubTitlesAlfabeto);
        $number                     = $number-(($sizeSubTitlesAlfabeto)*$numberUnits);
        $Letter                     = $alphabet [$number];
        if($numberUnits>0) $Letter  = $alphabet [$numberUnits-1].''.$Letter;
        return $Letter;
    }

    public function DivisionTrucate($number,$sizeSubTitlesAlfabeto){
        $division           = $number/$sizeSubTitlesAlfabeto;
        $divisionTruncate   = round($division,0,PHP_ROUND_HALF_DOWN);
        $difference         = $division-$divisionTruncate;
        if($difference<0){
            return $division=$division-(1+$difference);
        }
        return $divisionTruncate;
    }

    public function AddStylesCustom($vector,$backgroundColor,$colorFont){
        $this->stylesCustom[]=[$vector,$backgroundColor,$colorFont];
    }


}
