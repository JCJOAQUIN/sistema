<?php

namespace App\Functions;
use Illuminate\Support\Facades\Storage;

class Files
{
	static function rename($filename,$folio)
	{
		$paths = [
			'/docs/purchase/',
			'/docs/expenses/',
			'/docs/movements/',
			'/docs/payments/',
			'/docs/loan/',
			'/docs/refounds/',
			'/docs/purchase-record/',
			'/docs/credit_card/',
			'/docs/tickets/',
			'/docs/warehouse/',
			'/images/news/',
			'/docs/requisition/',
			'/docs/income/',
			'/docs/nomina/',
			'/docs/resource/'
		];

		if ($pos = strrpos($filename, '.'))
		{
			$name	= substr($filename, 0, $pos);
			$ext	= substr($filename, $pos);
		}
		else
		{
			$name	= $filename;
		}

		$newname	= $filename;
		foreach ($paths as $path)
		{
			$currentpath = $path.$filename;

			if (Storage::disk('public')->exists($currentpath))
			{
				$newname	= $folio.'_'.$filename;
				$newpath	= $path.$newname;
				Storage::disk('public')->move($currentpath,$newpath);
				break;
			}
		}
		return $newname;
	}
}