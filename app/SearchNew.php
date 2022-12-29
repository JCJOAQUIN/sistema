<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class SearchNew
{
	protected $apiKey = 'cc8376d7bcmsh68b8ec810fdaa3dp1ee41ajsn053aa39b68fa';
	public function getData($url)
	{
		/*
			$curl = curl_init();

			curl_setopt_array($curl, 
			[
				CURLOPT_URL				=> $url,
				CURLOPT_RETURNTRANSFER	=> true,
				CURLOPT_FOLLOWLOCATION	=> true,
				CURLOPT_ENCODING		=> "",
				CURLOPT_MAXREDIRS		=> 10,
				CURLOPT_TIMEOUT			=> 30,
				CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST	=> "GET",
				CURLOPT_HTTPHEADER		=> 
				[
					"X-Proxy-Location: MX",
					"X-RapidAPI-Host: google-search3.p.rapidapi.com",
					"X-RapidAPI-Key: ".$this->apiKey,
					"X-User-Agent: desktop"
				],
			]);

			$response	= curl_exec($curl);
			curl_close($curl);

			$object	= json_decode($response, true);
			$object	= $object['entries'];
			
			return $object;
		*/

		$curl = curl_init();

		curl_setopt_array($curl, 
		[
			CURLOPT_URL				=> $url,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_FOLLOWLOCATION	=> true,
			CURLOPT_ENCODING		=> "",
			CURLOPT_MAXREDIRS		=> 10,
			CURLOPT_TIMEOUT			=> 30,
			CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST	=> "GET",
			CURLOPT_HTTPHEADER		=> 
			[
				"X-RapidAPI-Host: newscatcher.p.rapidapi.com",
				"X-RapidAPI-Key: ".$this->apiKey
			],
		]);

		$response	= curl_exec($curl);
		$err		= curl_error($curl);

		curl_close($curl);

		$object = json_decode($response, true);
		$object = isset($object['articles']) ? $object['articles'] : [];

		return $object;
	}

}
