@extends('layouts.child_module')

@section('data')
	@component("components.labels.title-divisor") BUSCAR PERSONA @endcomponent
	@component("components.forms.searchForm",["attributeEx" => "id=\"formsearch\"","variant"=>"deafult"])
		@slot("contentEx")
			<div class="col-span-2">
				@component('components.labels.label') Nombre:@endcomponent
				@component('components.inputs.input-text')
					@slot('attributeEx')
						type        = "text" 
						name        = "name" 
						id          = "input-search" 
						placeholder = "Ingrese un nombre" 
						value       = "{{ isset($name) ? $name : '' }}"
					@endslot
				@endcomponent
			</div>
			<div class="col-span-2">
				@component('components.labels.label')Caja Chica:@endcomponent
				@php
					$options = collect();
					if(isset($box) && ($box == '' || $box == 'all'))
					{
						$options = $options->concat([["value"=>"all", "selected"=>"selected", "description"=> "Todos"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"all", "description"=> "Todos"]]);
					}

					if(isset($box) && $box == '1')
					{
						$options = $options->concat([["value"=>"1", "selected"=>"selected", "description"=> "Con caja chica"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"1", "description"=> "Con caja chica"]]);
					}

					if(isset($box) && $box == '0')
					{
						$options = $options->concat([["value"=>"0", "selected"=>"selected", "description"=> "Sin caja chica"]]);
					}
					else
					{
						$options = $options->concat([["value"=>"0", "description"=> "Sin caja chica"]]);
					}

					$attributeEx = "title=\"Caja chica\" name=\"box\" multiple=\"multiple\"";
					$classEx     = "js-box";
				@endphp
				@component('components.inputs.select', ['attributeEx' => $attributeEx,'classEx' => $classEx, "options" => $options]) @endcomponent
			</div>
		@endslot
		@if (count($users) > 0)
			@slot("export")
				<div class="float-right">
					@component('components.labels.label')
						@component('components.buttons.button',['variant' => 'success'])
							@slot('attributeEx') 
								type=submit 
								formaction={{ route('report.balance.excel') }} @endslot
							@slot('label')
								<span>Exportar a Excel</span><span class="icon-file-excel"></span> 
							@endslot
						@endcomponent
					@endcomponent
				</div>
			@endslot
		@endif
	@endcomponent

	@if(count($users) > 0)
		@php
			$body 		= [];
			$modelBody	= [];
			$modelHead	= 
			[
				[
					["value" => "ID"],
					["value" => "Nombre"],
					["value" => "Caja chica"],
					["value" => "Acción"],
				]
			];
			foreach ($users as $u)
			{
				$body = 
				[
					[
						"content" => 
						[
							"label" => $u->id
						]
					],
					[ 
						"content" => 
						[ 
							"label" => $u->fullName()
						]
					],
					[
						"content" => 
						[ 
							"label" => $u->cash == 1 ? "$".number_format($u->cash_amount,2) :  "--"
						]
					],
					[
						"content" =>
						[
							[
								"kind"			=> "components.buttons.button",
								"buttonElement"	=> "button", 
								"label"			=> "<span class=\"icon-search\"></span>",
								"classEx"		=> "follow-btn user-details",
								"variant"		=> "secondary",
								"attributeEx"	=> "alt=\"Detalles\" title=\"Detalles\" type=\"button\" data-id=\"".$u->id."\" data-toggle=\"modal\" data-target=\"#myModal\""
							]
						]
					]
				];
				array_push($modelBody, $body);
			}
		@endphp
		@component('components.tables.table',[
			"modelHead" 			=> $modelHead,
			"modelBody" 			=> $modelBody
		])
			@slot('classExBody')
				request-validate
			@endslot
		@endcomponent
		{{ $users->appends($_GET)->links() }}

		@component("components.modals.modal",[ "variant" => "large" ])
			@slot("id")
				myModal
			@endslot
			@slot("attributeEx")
				tabindex="-1"
			@endslot
			@slot("modalHeader")
			@component("components.buttons.button")
				@slot("attributeEx")
					type="button"
					data-dismiss="modal"
				@endslot
				@slot('classEx')
					close
				@endslot
				<span aria-hidden="true">&times;</span>
			@endcomponent
			@endslot
			@slot("modalBody")

			@endslot
		@endcomponent
	@else
		@component('components.labels.not-found') @endcomponent
	@endif
@endsection

@section('scripts')
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="{{ asset('js/datepicker.js') }}"></script>
	<script type="text/javascript"> 
		$(document).ready(function()
		{
			@php
				$selects = collect(
				[
					[
						"identificator"			=> ".js-box",
						"placeholder"			=> "Seleccione una opción",
						"languaje"				=> "es",
						"maximumSelectionLength" => "1"
					]
				]);
			@endphp
			@component("components.scripts.selects",["selects" => $selects]) @endcomponent
			$(document).on('click','[data-toggle="modal"]', function()
			{
				id = $(this).attr('data-id');
				$.ajax(
				{
					type : 'get',
					url  : '{{ route("report.balance.detail") }}',
					data : {'id':id},
					success : function(data)
					{
						$('.modal-body').html(data);
					},
					error: function()
					{
						swal('','Sucedió un error, por favor intente de nuevo.','error');
						$('#myModal').modal('hide');
					}
				})
			})
			.on('click','.exit',function()
			{
				$('#myModal').hide();
			});
		})

	</script>
@endsection


