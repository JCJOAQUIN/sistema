<div class="form-act pt-6">    
	<div class="accordion">
		<div class="bg bg-orange-400 flex">
			<div class="w-full md:pl-20 pl-14">
				@component("components.labels.label")
					@slot('classEx')
						text-white text-center text-lg font-bold w-full align-middle py-2 count_acts
					@endslot
					Acto Inseguro: # {{ $count_acts }}
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="hidden" 
						name="ua_id[]" 
						value="{{ isset($ua) ? $ua->id: 'x' }}"
					@endslot
				@endcomponent
			</div>
			<div class="float-right flex">
				@component('components.buttons.button',["variant" => "secondary"])
					@slot('label')
						<span class="indication fas icon-show-down"></span>
					@endslot
					@slot('attributeEx')
						type="button" 
						data-id-ua="{{ isset($ua) ? $ua->id : 'x' }}"
					@endslot
				@endcomponent
				@component('components.buttons.button',["variant" => "red"])
					@slot('label')
						<span class="icon-x"></span>
					@endslot
					@slot('attributeEx')
						type="button" 
						data-id-ua="{{ isset($ua) ? $ua->id : 'x' }}"
					@endslot
					@slot('classEx')
						delete_ua
					@endslot
				@endcomponent
			</div>
		</div>
	</div>
	<div class="accordion-content hide">
		@component("components.containers.container-form")
			@slot('classEx')
				my-0
			@endslot
			<div class="col-span-2">
				@component("components.labels.label")
					Categoría:
				@endcomponent
				@php
					$optionsCategory = collect();
					foreach (App\AuditCategory::orderBy('id','asc')->get() as $category)
					{
						$optionsCategory = $optionsCategory->concat(
						[
							[
								"value" 		=> $category->id,
								"description" 	=> $category->name,
								"selected" 		=> (isset($ua) && $ua->category_id == $category->id ? "selected" : "")
							]
						]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $optionsCategory])
					@slot('attributeEx')
						multiple="multiple" 
						name="temp_ua_category_id[]"
					@endslot
					@slot('classEx')
						id_category removeselect
					@endslot
				@endcomponent
				<div class="ua_category"></div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Subcategoría:
				@endcomponent
				@php
					$optionsSubCategory = collect();
					if(isset($ua))
					{
						foreach (App\AuditSubcategory::where('audit_category_id',$ua->category_id)->orderBy('id','asc')->get() as $subCategory)
						{
							$optionsSubCategory = $optionsSubCategory->concat(
							[
								[
									"value" 		=> $subCategory->id,
									"description" 	=> $subCategory->name,
									"selected" 		=> ($ua->subcategory_id == $subCategory->id ? "selected" : "")
								]
							]);
						}
					}
				@endphp
				@component("components.inputs.select", ["options" => $optionsSubCategory])
					@slot('attributeEx')
						multiple="multiple" 
						name="temp_ua_subcategory_id[]"
					@endslot
					@slot('classEx')
						subcategory removeselect
					@endslot
				@endcomponent
				<div class="ua_subcategory"></div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Valor de peligrosidad:
				@endcomponent
				@php
					$optionsDanger =  collect();
					foreach(["1/3", "1" ,"3"] as $item)
					{
						$optionsDanger = $optionsDanger->concat(
						[
							[
								"value" => $item,
								"description" => $item,
								"selected" => (isset($ua) && $ua->dangerousness == $item ? "selected" : "")
							]
						]);
					}
				@endphp
				@component("components.inputs.select", ["options" => $optionsDanger])
					@slot('attributeEx')
						multiple="multiple"
						name="temp_ua_dangerousness[]"
					@endslot
					@slot('classEx')
						dangerousness removeselect
					@endslot
				@endcomponent
				<div class="ua_dangerousness"></div>
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Descripción:
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="ua_description[]" 
						id="description" 
						rows="3" 
						cols="20" 
						placeholder="Ingrese una descripción" 
					@endslot
					{{ isset($ua) ? $ua->description : '' }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Acción correctiva inmediata:
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="ua_action[]" 
						id="action" 
						rows="3" 
						cols="20" 
						placeholder="Ingrese la acción correctiva inmediata"
					@endslot
					{{ isset($ua) ? $ua->action : '' }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Acción para prevenir repetición:
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="ua_prevent[]" 
						id="prevent" 
						rows="3" 
						cols="20"
						placeholder="Ingrese la acción para prevenir la repetición"
					@endslot
					{{ isset($ua) ? $ua->prevent : '' }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					RE:
				@endcomponent
				@component("components.inputs.text-area")
					@slot('attributeEx')
						name="ua_re[]" 
						id="re" 
						rows="3" 
						cols="20" 
						class="form-control" 
						placeholder="Ingrese el RE" 
					@endslot
					{{ isset($ua) ? $ua->re : '' }}
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					FV:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="ua_fv[]" 
						placeholder="Ingrese la fecha" 
						readonly="readonly" 
						value="{{ isset($ua) ? Carbon\Carbon::createFromFormat('Y-m-d',$ua->fv)->format('d-m-Y') : '' }}"
					@endslot
					@slot('classEx')
						fv removeselect datepicker2
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component("components.labels.label")
					Estado del Reporte:
				@endcomponent
				@php
					$optionsStatus =  collect();
					$value= 1;
					foreach(["Abierto", "Cerrado"] as $item)
					{
						$optionsStatus = $optionsStatus->concat(
						[
							[
								"value" =>  $value,
								"description" => $item,
								"selected" => (isset($ua) && $ua->status == $value ? "selected" : "")
							]
						]);
						$value++;
					}
				@endphp
				@component("components.inputs.select", ["options" => $optionsStatus])
					@slot('attributeEx')
						multiple="multiple" 
						name="temp_ua_status[]"
					@endslot
					@slot('classEx')
						status removeselect
					@endslot
				@endcomponent
				<div class="ua_status"></div>
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Responsable de dicha situación:
				@endcomponent
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="text" 
						name="ua_responsable[]" 
						placeholder="Ingrese el responsable de dicha situación" 
						value="{{ isset($ua) ? $ua->responsable : '' }}"
					@endslot
					@slot('classEx')
						removeselect
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Imágenes antes de resolver:
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@if(isset($ua))
					@php
						$documentsBody = [];
						$modelHead = ["Nombre", "Documento"];
						$count = 1;
						foreach($ua->beforeDocuments as $doc)
						{
							$row =
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										["kind" => "components.labels.label", "label" => "Documento ".$count]
									]
								],
								[
									"content" => 
									[
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "secondary",
											"buttonElement"	=> "a",
											"attributeEx"	=> "target=\"_blank\" title=\"".$doc->path."\"".' '."href=\"".asset('/docs/audits/'.$doc->path)."\"",
											"label"			=> "Archivo"
										]
									]
								]
							];
							$documentsBody[] = $row;
							$count ++;
						}
					@endphp
					<div class="table-responsive">
						@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $documentsBody,"variant" => "default", "attributeEx" => "id=\"table-documents-before\""]) @endcomponent
					</div>
				@endif
			</div>
			<div class="documents-before hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>
			<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="hidden"
						value={{$count_acts}}
					@endslot
					@slot('classEx')
						numberAct_before
					@endslot
				@endcomponent
				@component("components.buttons.button", ["variant" => "warning", "classEx" => "addDocBefore"]) 
					@slot("attributeEx")
						type="button"
						name="addDocBefore{{$count_acts}}"
					@endslot
					<span class="icon-plus"></span>
					<span>Nueva imagen</span>
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')
					Imágenes después de resolver:
				@endcomponent
			</div>
			<div class="col-span-2 md:col-span-4">
				@if(isset($ua))
					@php
						$documentsBody = [];
						$modelHead = ["Nombre", "Documento"];
						$count = 1;
						foreach($ua->afterDocuments as $doc)
						{
							$row =
							[
								"classEx" => "tr",
								[
									"content" => 
									[
										["kind" => "components.labels.label", "label" => "Documento ".$count]
									]
								],
								[
									"content" => 
									[
										[
											"kind"			=> "components.buttons.button",
											"variant"		=> "secondary",
											"buttonElement"	=> "a",
											"attributeEx"	=> "target=\"_blank\" title=\"".$doc->path."\"".' '."href=\"".asset('/docs/audits/'.$doc->path)."\"",
											"label"			=> "Archivo"
										]
									]
								]
							];
							$documentsBody[] = $row;
							$count ++;
						}
					@endphp
					<div class="table-responsive">
						@component('components.tables.alwaysVisibleTable',["modelHead" => $modelHead, "modelBody" => $documentsBody,"variant" => "default", "attributeEx" => "id=\"table-documents-after\""]) @endcomponent
					</div>
				@endif
			</div>
			<div class="documents-after hidden col-span-2 md:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6 p-2"></div>
			<div class="md:col-span-4 col-span-2 grid justify-items-center md:justify-items-start">
				@component("components.inputs.input-text")
					@slot('attributeEx')
						type="hidden"
						value={{$count_acts}}
					@endslot
					@slot('classEx')
						numberAct_after
					@endslot
				@endcomponent
				@component("components.buttons.button", ["variant" => "warning", "classEx" => "addDocAfter"]) 
					@slot("attributeEx")
						type="button"
						name="addDocAfter" 
						data-id-ua="{{$count_acts}}"
					@endslot
					<span class="icon-plus"></span>
					<span>Nueva imagen</span>
				@endcomponent
			</div>
		@endcomponent
	</div>
</div>