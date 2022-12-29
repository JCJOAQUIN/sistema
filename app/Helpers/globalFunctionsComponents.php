<?php
	use Illuminate\Support\Facades\Cookie;

	function slotsItem($item) // Method to set any slot to any component.
	{
		$componentOptions = [];

		$keys = array_keys($item);

		for($i = 0; $i<count($keys); $i++)
		{
			$componentOptions[$keys[$i]] = $item[$keys[$i]];
		}
		return $componentOptions;
	}

	function replaceClassEX($classEx, $mainClasses, $arrayFind) //Validatement and replacement of duplicated classes inside "classEx" slot.
	{
		if($classEx != "")
		{
			if(isset($classEx) && isset($mainClasses) && isset($arrayFind))
			{
				$secondaryArray = explode(" ", $classEx);
				$mainClassArr   = explode(" ", $mainClasses);
				
				$keys   = array_keys($arrayFind);
	
				for($i = 0; $i<count($secondaryArray); $i++)
				{
					if(!empty($secondaryArray[$i]) && !strstr($mainClasses, $secondaryArray[$i]))
					{
						for($j = 0; $j<count($keys); $j++)
						{
							for($k = 0; $k<count($arrayFind[$keys[$j]]); $k++)
							{
								if(strstr($secondaryArray[$i],$arrayFind[$keys[$j]][$k]))
								{
									if($arrayFind[$keys[$j]][$k] == "text-")
									{
										$textColors = array("text-transparent", "text-current", "text-black", "text-white", 
															"text-gray-50", "text-gray-100", "text-gray-200", "text-gray-300", "text-gray-400", "text-gray-500", "text-gray-600", "text-gray-700", "text-gray-800", "text-gray-900",
															"text-red-50", "text-red-100", "text-red-200", "text-red-300", "text-red-400", "text-red-500", "text-red-600", "text-red-700", "text-red-800", "text-red-900",
															"text-yellow-50", "text-yellow-100", "text-yellow-200", "text-yellow-300", "text-yellow-400", "text-yellow-500", "text-yellow-600", "text-yellow-700", "text-yellow-800", "text-yellow-900",
															"text-green-50", "text-green-100", "text-green-200", "text-green-300", "text-green-400", "text-green-500", "text-green-600", "text-green-700", "text-green-800", "text-green-900",
															"text-blue-50", "text-blue-100", "text-blue-200", "text-blue-300", "text-blue-400", "text-blue-500", "text-blue-600", "text-blue-700", "text-blue-800", "text-blue-900",
															"text-indigo-50", "text-indigo-100", "text-indigo-200", "text-indigo-300", "text-indigo-400", "text-indigo-500", "text-indigo-600", "text-indigo-700", "text-indigo-800", "text-indigo-900",
															"text-purple-50", "text-purple-100", "text-purple-200", "text-purple-300", "text-purple-400", "text-purple-500", "text-purple-600", "text-purple-700", "text-purple-800", "text-purple-900",
															"text-pink-50", "text-pink-100", "text-pink-200", "text-pink-300", "text-pink-400", "text-pink-500", "text-pink-600", "text-pink-700", "text-pink-800", "text-pink-900"
														);
										if(in_array($secondaryArray[$i], $textColors))
										{
											unset($mainClassArr[$keys[$j]]);
										}
									}
									else
									{
										unset($mainClassArr[$keys[$j]]);
									}
								}
							}
						}
						$mainClassArr[]= $secondaryArray[$i];
					}
				}
				$mainClasses = implode(" ",$mainClassArr);
	
				return $mainClasses; 
			}
		}
	}

	function getAttribute($attributeEx, $attr) // Get any attribute value specified in the function received values, from "attributeEx" slot.
    {   
        if(isset($attributeEx))
        {
			// $attributeEx = str_replace(" ", "\n", $attributeEx);			// Linea original
			// $attributeEx = preg_replace("/\s+/", "\n", $attributeEx);	// Implementación de preg_replace, con problema de (n) valores y json
			$attributeEx = preg_replace("/\"\s+/", "\"\n", $attributeEx);	// Propuesta de cambio para entrada de atributo con (n) valores " atributo = 'valor1 valor2 valor3' " ó entrada de atributo con valor json.
            
            if(strstr($attributeEx, $attr."="))
            {
                $attributes = explode("\n", $attributeEx);
                for($i = 0; $i<count($attributes); $i++)
                {
                    if(strstr($attributes[$i], $attr."="))
                    {
                        $mainArray = explode("=", $attributes[$i]);
                        $r = $mainArray[1];
						$r = preg_replace("/(\r)*(')*(\")*/", "", $r);
                    }
                }
                
                return $r;
            }
        }
    }
	
	function getUrlRedirect($submoduleId)
	{
		$urlCookie = Cookie::get('urlSearch');
		$urlArray = json_decode($urlCookie, true);
		if(isset($urlCookie) && is_array($urlArray))
		{
			if(isset($urlArray[$submoduleId]) && $urlArray[$submoduleId] != '')
			{
				return $urlArray[$submoduleId];
			}
			else
			{
				return App\Module::find($submoduleId)->url;
			}
		}
		else
		{
			return App\Module::find($submoduleId)->url;
		}
	}

	function storeUrlCookie($submodule_id) // store the search's url in "revisión" and "authorization" or other submodules
    { 
		$urlCookie = Cookie::get('urlSearch');
		$urlArray = json_decode($urlCookie, true);
		if(isset($urlCookie) && is_array($urlArray))
		{
			if(!isset($urlArray[$submodule_id]) && count($urlArray) > 5)
			{
				$submoduleToDelete = array_key_first($urlArray);
				unset($urlArray[$submoduleToDelete]);
			}
			$urlArray[$submodule_id] = url()->full();
		}
		else
		{
			$urlArray = array();
			$urlArray[$submodule_id] = url()->full();
		}
		return json_encode($urlArray);
	}

	function searchRedirect($submodule_id, $alert, $alternativeRoute) // redirect to the searchform's url in "revision" and "authorization" or other submodules 
	{
		$urlCookie = Cookie::get('urlSearch');
		$urlArray  = json_decode($urlCookie, true);
		if(isset($urlCookie) && is_array($urlArray))
		{
			if(isset($urlArray[$submodule_id]) && $urlArray[$submodule_id] != '')
			{
				$moduleUrl = $urlArray[$submodule_id];
				unset($urlArray[$submodule_id]);
				$urlCookie = json_encode($urlArray);
				return redirect($moduleUrl)
					->with('alert',$alert)
					->cookie(
						'urlSearch', $urlCookie, 2880
					);
			}
			else
			{
				if(isset($alternativeRoute))
				{
					if($alternativeRoute == "back")
					{
						return back()->with('alert',$alert);
					}
					else
					{
						return redirect($alternativeRoute)->with('alert',$alert);
					}
				}
				else
				{
					return back()->with('alert',$alert);
				}
			}
		}
		else
		{
			if(isset($alternativeRoute))
			{
				if($alternativeRoute == "back")
				{
					return back()->with('alert',$alert);
				}
				else
				{
					return redirect($alternativeRoute)->with('alert',$alert);
				}
			}
			else
			{
				return back()->with('alert',$alert);
			}
		}
	}
?>
