<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Bill;
use App\Enterprise;
use Carbon\Carbon;
use Genkgo\Xsl\XsltProcessor;
use PDF;

class NominaQueue extends Command
{
	protected $signature = 'queue:nomina';

	protected $description = 'Timbrar CFDI de nómina pendientes';

	public function __construct()
	{
		parent::__construct();
	}

	public function handle()
	{
		$all	= Bill::where('type','N')->where('folioRequest',NULL)->where('status',6)->limit(100)->get();
		foreach ($all as $bill)
		{
			$bill	= Bill::find($bill->idBill);
			if($bill->expeditionDateCFDI == '' || $bill->expeditionDateCFDI == null)
			{
				$noCertificado				= Enterprise::where('rfc',$bill->rfc)->first()->noCertificado;
				$bill->expeditionDateCFDI	= Carbon::Now()->subMinute(10);
				$bill->folio				= $bill->cfdiFolio;
				$bill->noCertificate		= $noCertificado;
				$bill->save();
				$xslDoc						= new \DOMDocument();
				$xmlDoc						= new \DOMDocument();
				$transpiler					= new XsltProcessor();
				$xslDoc->load(\Storage::disk('reserved')->getDriver()->getAdapter()->getPathPrefix().'/v33/cadenaoriginal_3_3.xslt');
				$objCer					= str_replace('-----BEGIN CERTIFICATE-----','',str_replace('-----END CERTIFICATE-----','',preg_replace("/\r|\n/", "", \Storage::disk('reserved')->get('/cer/'.$bill->rfc.'.cer.pem'))));
				$xmlDoc->loadXML(view('administracion.facturacion.xml', ['bill' => $bill,'noCertificado' => $noCertificado]));
				$transpiler->importStylesheet($xslDoc);
				$originalChain			= $transpiler->transformToXML($xmlDoc);
				$privKey				= openssl_get_privatekey(\Storage::disk('reserved')->get('/cer/'.$bill->rfc.'.key.pem'));
				openssl_sign($originalChain,$certificate,$privKey,OPENSSL_ALGO_SHA256);
				$stamp					= base64_encode($certificate);
				$XML					= view('administracion.facturacion.xml', ['bill' => $bill,'noCertificado' => $noCertificado,'sello' => $stamp,'certificado' => $objCer]);
				$objToStamp				= array();
				$objToStamp['username']	= 'PIM110705A78';
				if(app()->env == 'production')
				{
					$objToStamp['password']	= '2K9c3KvPGHsRqMk36-H8';
					$strUrl					= 'https://sistema.timbox.com.mx/timbrado_cfdi33/wsdl';
				}
				else
				{
					$objToStamp['password']	= 'GF7vdJNwdxJbxB1ShwX7';
					$strUrl					= 'https://staging.ws.timbox.com.mx/timbrado_cfdi33/wsdl';
				}
				$objWebService			= new \SoapClient($strUrl, array('trace' => 1,'use' => SOAP_LITERAL));
				$objToStamp['sxml']		= base64_encode($XML);
				try
				{
					$responseWS				= $objWebService->__soapCall("timbrar_cfdi",$objToStamp);
					$xmlStamped				= new \DOMDocument();
					$xmlStamped->loadXML($responseWS->xml);
					$tfd					= $xmlStamped->getElementsByTagName('TimbreFiscalDigital');
					$bill->uuid				= $tfd[0]->getAttribute('UUID');
					$bill->satCertificateNo	= $tfd[0]->getAttribute('NoCertificadoSAT');
					$bill->stampDate		= str_replace('T', ' ', $tfd[0]->getAttribute('FechaTimbrado'));
					$bill->originalChain	= $originalChain;
					$bill->digitalStampCFDI	= $tfd[0]->getAttribute('SelloCFD');
					$bill->digitalStampSAT	= $tfd[0]->getAttribute('SelloSAT');
					$bill->status			= 1;
					$bill->error			= null;
					$bill->statusCFDI		= 'Vigente';
					$bill->save();
					\Storage::disk('reserved')->put('/stamped/'.$bill->uuid.'.xml',$responseWS->xml);
					$pdf					= PDF::loadView('administracion.facturacion.'.$bill->rfc,['bill'=>$bill]);
					\Storage::disk('reserved')->put('/stamped/'.$bill->uuid.'.pdf',$pdf->stream());
				}
				catch (\Exception $exception)
				{
					$bill->expeditionDateCFDI	= null;
					$bill->status				= 7;
					$bill->folio				= null;
					$bill->error				= $exception->getCode().': '.nl2br($exception->getMessage(),true);
					$bill->save();
				}
			}
			sleep(5);
		}
	}
}
