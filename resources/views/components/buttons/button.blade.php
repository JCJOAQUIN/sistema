@php
	!isset($variant) ? $v = "primary" : $v = $variant;
	!isset($buttonElement) ? $bE = "button" : $bE = $buttonElement;
	$background = 
	[
		"primary"   	  => "bg-lime-500",
		"secondary" 	  => "bg-light-blue-500",
		"reset"     	  => "bg-gray-100",
		"success"   	  => "bg-green-600",
		"warning"   	  => "bg-orange-400",
		"dark"      	  => "bg-black",
		"gray"      	  => "bg-gray-600",
		"dark-red"  	  => "bg-red-600",
		"red"			  => "bg-red-500",
		"link"			  => "bg-inherit",
		"none"			  => "bg-white",
		"transparent"	  => "bg-transparent"
	];
	$hover = 
	[
		"primary"   	=> "hover:bg-lime-400",
		"secondary" 	=> "hover:bg-light-blue-400",
		"reset"     	=> "hover:bg-gray-50",
		"success"   	=> "hover:bg-green-500",
		"warning"   	=> "hover:bg-orange-300",
		"dark"      	=> "hover:bg-warm-gray-700",
		"gray"      	=> "hover:bg-gray-500",
		"dark-red"  	=> "hover:bg-red-500",
		"red"			=> "hover:bg-red-400",
		"link"			=> "hover:bg-blue",
		"none"			=> "hover:text-red-500",
		"transparent"	=> "hover:bg-transparent"
	];
	$text = 
	[
		"reset"        => "text-black",
		"none"         => "text-black",
		"link"         => "text-blue-700 underline",
		"white"        => "text-white"
	];

	if(isset($variant))
	{
		if($variant == 'none' || $variant == 'link' || $variant == 'reset')
		{
			$Text 		= $text[$v];
		}
		else
		{
			$Text 		= $text["white"];
		}
		$HoverClass = $hover[$v];
		$ColorClass = $background[$v];
		$class 		= $background[$v];
	}
	else
	{
		if($bE ==  "a")
		{
			$class 		= $background['secondary'];
			$HoverClass = $hover['secondary'];
			$Text 		= $text['white'];
		}
		elseif($bE ==  "noVariant")
		{
			$bE         = "button";
		}
		else
		{
			$HoverClass = $hover[$v]?? $hover['primary'];
			$ColorClass = $background[$v]?? $background['primary'];
			$Text 		= $text[$v]?? $text['white'];
		}
	}

	$HoverClass = isset($HoverClass) ? $HoverClass : '';
	$ColorClass = isset($ColorClass) ? $ColorClass : '';
	$Text 		= isset($Text) ? $Text : '';
	$class 		= isset($class) ? $class : '';
	if(isset($massiveVariant))
	{
		$mainClasses = "cursor-pointer p-2 rounded-full focus:outline-none appearance-none m-1 flex justify-center items-center space-x-1 leading-4 ".$HoverClass." ".$ColorClass." ".$Text." ".$class;	
	}
	else
	{
		$mainClasses = "cursor-pointer p-2 rounded-lg focus:outline-none appearance-none m-1 flex justify-center items-center space-x-1 leading-4 ".$HoverClass." ".$ColorClass." ".$Text." ".$class." ".(in_array($v, ["warning","success"]) ? "normal-case" : "uppercase");
	}
	$arrayFind = ['1' => ['p-', 'px-'], '2' => ['p-', 'py-'], '3' => ['rounded', 'rounded-'], '5' => ['m-', 'mx-', 'my-', 'mt-', 'mb-'], '9' => ['bg-']];
@endphp
<{{ $bE }}
	class="@isset($massiveVariant) rounded-full {{$mainClasses}} @else rounded-lg @if(isset($classEx) && $classEx != "") {{replaceClassEX($classEx, $mainClasses, $arrayFind)}} @else {{$mainClasses}} @endif @endisset"
	@isset($attributeEx) {!!$attributeEx!!} @endisset @isset($attr) {!!$attr!!} @endisset>
		@isset($label ) {!!$label!!} @else {!! $v == 'reset-search' ? "Borrar campos" : $slot; !!}  @endisset
</{{ $bE }}>
