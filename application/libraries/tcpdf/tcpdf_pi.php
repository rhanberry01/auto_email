<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

class NormalTCPDF extends TCPDF {
	public function Header()
	{

	}

	public function Footer()
	{

	}
}

// Extend the TCPDF class to create custom Header and Footer
class OnemoretakePDF extends TCPDF {

	var $top_margin = 40;

	public function Header()
	{

		$CI =& get_instance();
		$CI->load->model('settings/company_model');
		$company_details = $CI->company_model->get_company_details();

		// $this->Image(base_url().$company_details->logo,12,5,48,27,'','','',true);
		$this->SetFont('helvetica','B',14);
		$this->Ln(10);
        $this->Write(8, $company_details->name, '', 0, 'C', false, 0, false, false, 0);
		$this->Ln();
		$this->SetFont('helvetica','',11);
		$this->Write(5, $company_details->address, '', 0, 'C', false, 0, false, false, 0);
		$this->Ln();
		$this->Write(5, 'Tel. No. '.$company_details->phone, '', 0, 'C', false, 0, false, false, 0);
		$this->Ln();
		$this->Write(5, 'TIN : '.$company_details->tin, '', 0, 'C', false, 0, false, false, 0);
		$this->Ln(8);
		$this->SetLineWidth(0.2);
		$this->Line(11, $this->y, $this->w - 11, $this->y);
		$this->Ln(10);
		$this->top_margin = $this->GetY()+5;
    }

    public function Footer()
	{
      //footer
    }

	public function title($title, $from, $to)
	{
		$range = date('m/d/Y', strtotime($from))." - ".date('m/d/Y', strtotime($to));
		$this->Ln(10);
		$this->SetFont('helvetica', 'B', 12);
		$this->Write(5, $title, '', 0, 'L', false, 0, false, false, 0);
		$this->Ln();
		$this->SetFont('helvetica', '', 11);
		$this->Write(5, "Period: ".$range, '', 0, 'L', false, 0, false, false, 0);
		$this->Ln(10);
	}

	public function introTitle($title)
	{
		$this->Ln(18);
		$this->SetFont('helvetica', 'B', 10);
		$this->Write(5, $title, '', 0, 'C', false, 0, false, false, 0);
		$this->Ln(5);
	}



	public function error_message()
	{
		$this->SetFont('helvetica', '', 11);
		$this->Write(5, "There is no transaction in the given date.", '', 0, 'L', false, 0, false, false, 0);
	}
}

function tcpdf($orientation, $unit, $format)
{
	return new OnemoretakePDF ($orientation, $unit, $format, true);
}


class BonPDF extends TCPDF {
	function BonPDF($title,$filename,$page_size='A4',$params=array()) {
		if (!isset($params['a_meta_charset']))
			$params = array('a_meta_charset' => 'ISO-8859','a_meta_dir' => 'ltr', 'a_meta_langauge' => 'en_GB', 'w_page' => 'page');
		$enc = $params['a_meta_charset'];
		$uni = ($enc == 'UTF-8' || $enc == 'GB2313') ? true : false;
		if ($uni)
			ini_set('memory_limit','48M');
		$this->TCPDF('P', 'pt', $page_size, $uni, $enc);
		$this->SetTitle = $title;
		$this->SetFilename = $filename;
		$this->setLanguageArray($params);
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
		$this->setAutoPageBreak(false);
		$this->AddPage();

		$this->SetLineWidth(1);
		$this->cMargin = 0;
	}

	function Stream()
	{
		TCPDF::Output('', 'I');
	}

	function Output()
	{
		return TCPDF::Output('','S');
	}

	function calcTextWrap($txt, $width, $spacebreak=false)
	{
		$ret = "";
		$txt2 = $txt;
		$w = $this->GetStringWidth($txt);
		if ($w > $width && $w > 0 && $width != 0)
		{
			$n = strlen($txt);
			$k = intval($n * $width / $w);
			if ($k > 0 && $k < $n)
			{
				$txt2 = substr($txt, 0, $k);
				if ($spacebreak && (($pos = strrpos($txt2, " ")) !== false))
				{
					$txt2 = substr($txt2, 0, $pos);
					$ret = substr($txt, $pos+1);
				}
				else
					$ret = substr($txt, $k);
			}
		}
		return array($txt2, $ret);
	}

	function addTextWrap($xb, $yb, $w, $h, $txt, $align='left', $border=0, $fill=0, $spacebreak=false)
	{
		$ret = "";
		if (!$this->rtl)
		{
			if ($align == 'right')
				$align = 'R';
			elseif ($align == 'left')
				$align = 'L';
		}
		else
			$align = 'R';
		$txt = $this->calcTextWrap($txt, $w, $spacebreak);
		$ret = $txt[1];
		$txt = $txt[0];
		$this->SetXY($xb, $this->h - $yb - $h);
		$txt = TCPDF::unhtmlentities($txt);
		if ($this->isunicode && $this->encoding != "UTF-8")
			$txt = iconv($this->encoding, "UTF-8", $txt);
		$this->Cell($w, $h, $txt, $border, 0, $align, $fill);
		return $ret;
	}
}

?>