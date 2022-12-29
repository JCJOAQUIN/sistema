@component("components.forms.form", ["attributeEx" => isset($attributeEx) ? $attributeEx : ""])
	@component("components.containers.container-form")
		@if(!isset($variant))
			@if(!isset($hidden) || (isset($hidden) && !in_array('folio',$hidden)))
				<div class="col-span-2">
					@component("components.labels.label") Folio: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="folio" 
							id="input-search" 
							placeholder="Ingrese el folio"
							value="{{ isset($values["folio"]) ? $values["folio"] : "" }}"
						@endslot
					@endcomponent
				</div>
			@endif
			@if(!isset($hidden) || (isset($hidden) && !in_array('name',$hidden)))
				<div class="col-span-2">
					@component("components.labels.label") Solicitante: @endcomponent
					@component("components.inputs.input-text")
						@slot("attributeEx")
							name="name"
							id="input-search" 
							placeholder="Ingrese el nombre del solicitante"
							value="{{ isset($values["name"]) ? $values["name"] : "" }}"
						@endslot
					@endcomponent
				</div>
			@endif
			@if(!isset($hidden) || (isset($hidden) && !in_array('rangeDate',$hidden)))
				<div class="col-span-2">
					@component("components.labels.label") Rango de fechas: @endcomponent
					
					@php
						$value_one ="";
						$value_two ="";					
						if(isset($values["minDate"]) && isset($values["maxDate"]))
						{ 
							$inputs= [
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$values["minDate"]."\"",
								],
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$values["maxDate"]."\"",
								]
							];
						}
						else if(!isset($values["minDate"]) && isset($values["maxDate"]))
						{
							$inputs= [
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\"",
								],
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".$values["maxDate"]."\"",
								]
							];
						}
						else if(isset($values["minDate"]) && !isset($values["maxDate"]))
						{
							$inputs= [
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".$values["minDate"]."\"",
								],
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\"",
								]
							];
						}
						else
						{
							$inputs= [
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\"",
								],
								[
									"input_classEx" => "input-text-date datepicker",
									"input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\"",
								]
							];
						}
					@endphp

					@component("components.inputs.range-input",["inputs" => $inputs])
					@endcomponent
				</div>
			@endif
			@if(!isset($hidden) || (isset($hidden) && !in_array('enterprise',$hidden)))
				<div class="col-span-2">
					@component("components.labels.label") Empresa: @endcomponent
					@php
						$options = collect();
						foreach(App\Enterprise::orderName()->where("status","ACTIVE")->whereIn("id",Auth::user()->inChargeEnt($values["enterprise_option_id"])->pluck("enterprise_id"))->get() as $enterprise)
						{
							$description = strlen($enterprise->name) >= 35 ? substr(strip_tags($enterprise->name),0,35)."..." : $enterprise->name;
							if(isset($values["enterprise_id"]) && $values["enterprise_id"] == $enterprise->id)
							{
								$options = $options->concat([["value"=>$enterprise->id, "selected"=>"selected", "description"=>$description]]);
							}
							else
							{
								$options = $options->concat([["value"=>$enterprise->id, "description"=>$description]]);
							}
						}
						$attributeEx = "title=\"Empresa\" name=\"enterpriseid\" multiple=\"multiple\" id=\"multiple-enterprises\"";
						$classEx = "js-enterprise";
					@endphp
					@component("components.inputs.select", ["options" => $options, "attributeEx" => $attributeEx, "classEx" => $classEx])
					@endcomponent
				</div>
			@endif
		@else
			{!!$slot!!}
		@endif
		{!! isset($contentEx) ? $contentEx : "" !!}	
		<div class="col-span-2 md:col-span-4 space-x-2 text-center md:text-left flex">
			@component("components.buttons.button-search", ["attributeEx" => $attributeExButtonSearch??'', "classEx" => $classExButtonSearch??'']) @endcomponent
			@component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
		</div>
	@endcomponent
	@isset($export)
		<div class="flex flex-row justify-end">
			{!!$export!!}
		</div>
	@endisset
@endcomponent
