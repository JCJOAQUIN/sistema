<div id="staff-table" @if(isset($request) && in_array($request->requisition->requisition_type,[1,2,4,5,6])) class="hidden"  @elseif(!isset($request)) class="hidden" @endif>
	@component('components.labels.title-divisor') 
		CARGA MASIVA (OPCIONAL)
	@endcomponent
	<div class="justify-center">
		@component("components.labels.not-found", ["variant" => "note"])
			@slot("slot")
				@component("components.labels.label")
					Dé clic en el siguiente botón para descargar la plantilla para la carga masiva:
					@component('components.buttons.button', ["variant" => "success", "attributeEx" => "href=\"".route('requisition.download-layout-personal')."\"", "buttonElement" => "a", "label" => "Plantilla para empleados"]) @endcomponent
				@endcomponent 
				@component("components.labels.label")
					Dé clic en el siguiente botón para descargar la lista de catálogos para el llenado de la plantilla:
					@component('components.buttons.button', ["variant" => "success", "attributeEx" => "href=\"".route('requisition.export.catalogs')."\"", "buttonElement" => "a", "label" => "Catálogos para plantilla"]) @endcomponent
				@endcomponent
				@component("components.labels.label")
					En la plantilla se específica como se debe llenar cada campo para que no exista algún error al momento de cargarla al sistema.
				@endcomponent
			@endslot
		@endcomponent
		<div class="flex justify-center">
			<div class="w-full">
				@php
					$buttons = [
						"separator" =>
						[
							[
								"kind" 			=> "components.buttons.button-approval",
								"label"			=> "coma (,)",
								"attributeEx"	=> "checked value=\",\" name=\"separator\" id=\"separatorComaPersonal\""
							],
							[
								"kind"			=> "components.buttons.button-approval",
								"label" 		=> "punto y coma (;)",
								"attributeEx"	=> "value=\";\" name=\"separator\" id=\"separatorPuntoComaPersonal\""
							]
						],
						"buttonEx"	=> []
					];
					if(isset($request) && !isset($new_requisition))
					{
						if(isset($request) && $request->status == 2)
						{
							array_push($buttons["buttonEx"],
							[
								"kind"			=> "components.buttons.button",
								"label"			=> "CARGAR ARCHIVO",
								"variant"		=> "primary",
								"attributeEx"	=> "type=\"submit\" id=\"upload_file\" formaction=\"".route('requisition.save-follow',$request->folio)."\""
							]);
						}
					}
					else
					{
						array_push($buttons["buttonEx"],
						[
							"kind" 			=> "components.buttons.button",
							"label" 		=> "CARGAR ARCHIVO",
							"variant"		=> "primary",
							"attributeEx"	=> "type=\"submit\" id=\"upload_file\" formaction=\"".route('requisition.store-detail')."\""
						]);
					}
				@endphp
				@component('components.documents.select_file_csv', 
				[
					"attributeExInput"	=> "type=\"file\" name=\"csv_file_personal\" id=\"files_personal\" accept=\".csv\"",
					"buttons"			=> $buttons
				])
				@endcomponent
			</div>
		</div>
		<div class="w-full">
			@php
				$body 	   = [];
				$modelBody = [];
				$modelHead = ["Nombre", "CURP", "Puesto", "Acción"];
				if(isset($request) && $request->requisition->employees()->exists())
				{
					foreach($request->requisition->employees as $key => $emp)
					{
						$valueIncomeDate	= $emp->admissionDate != '' ? $emp->admissionDate->format('d-m-Y') : '';
						$valueImssDate		= $emp->imssDate != '' ? $emp->imssDate->format('d-m-Y') : '';
						$valueDownDate		= $emp->downDate != '' ? $emp->downDate->format('d-m-Y') : '';
						$valueEndingDate	= $emp->endingDate != '' ? $emp->endingDate->format('d-m-Y') : '';
						$valueReentryDate	= $emp->reentryDate != '' ? $emp->reentryDate->format('d-m-Y') : '';
						$descriptionAccount	= $emp->accounts->account." - ".$emp->accounts->description." (".$emp->accounts->content.")";

						$body = 
						[
							[
								"content" =>
								[
									"label" => htmlentities($emp->fullName())
								]
							],
							[
								"content" => 
								[
									"label" => $emp->curp
								]
							],
							[
								"content" => 
								[
									"label" => htmlentities($emp->position)
								]
							],
							[
								"content" =>
								[
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_employee_id[]\" value=\"".$emp->id."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_name[]\" value=\"".htmlentities($emp->name)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_last_name[]\" value=\"".htmlentities($emp->last_name)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_scnd_last_name[]\" value=\"".htmlentities($emp->scnd_last_name)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_curp[]\" value=\"".$emp->curp."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_rfc[]\" value=\"".$emp->rfc."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_tax_regime[]\" value=\"".$emp->tax_regime."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_imss[]\" value=\"".$emp->imss."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_email[]\" value=\"".htmlentities($emp->email)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_phone[]\" value=\"".$emp->phone."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_street[]\" value=\"".htmlentities($emp->street)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_number_employee[]\" value=\"".htmlentities($emp->number)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_colony[]\" value=\"".htmlentities($emp->colony)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_cp[]\" value=\"".$emp->cp."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_city[]\" value=\"".htmlentities($emp->city)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_state[]\" value=\"".$emp->state_id."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"state_description\" value=\"".htmlentities($emp->states->description)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_state[]\" value=\"".$emp->state."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_project[]\" value=\"".$emp->project."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_enterprise[]\" value=\"".$emp->enterprise."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_account[]\" value=\"".$emp->account."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"account_description\" value=\"".$descriptionAccount."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_direction[]\" value=\"".$emp->direction."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_department[]\" value=\"".$emp->department."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_position[]\" value=\"".htmlentities($emp->position)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_immediate_boss[]\" value=\"".htmlentities($emp->immediate_boss)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_income_date[]\" value=\"".$valueIncomeDate."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_status_imss[]\" value=\"".$emp->status_imss."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_imss_date[]\" value=\"".$valueImssDate."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_down_date[]\" value=\"".$valueDownDate."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_ending_date[]\" value=\"".$valueEndingDate."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_reentry_date[]\" value=\"".$valueReentryDate."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_type_employee[]\" value=\"".$emp->workerType."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_regime_employee[]\" value=\"".$emp->regime_id."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_status_employee[]\" value=\"".$emp->workerStatus."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_status_reason[]\" value=\"".htmlentities($emp->status_reason)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_sdi[]\" value=\"".$emp->sdi."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_periodicity[]\" value=\"".$emp->periodicity."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_employer_register[]\" value=\"".$emp->employer_register."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_payment_way[]\" value=\"".$emp->paymentWay."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_net_income[]\" value=\"".$emp->netIncome."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_complement[]\" value=\"".$emp->complement."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_fonacot[]\" value=\"".$emp->fonacot."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_infonavit_credit[]\" value=\"".$emp->infonavitCredit."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_infonavit_discount[]\" value=\"".$emp->infonavitDiscount."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_infonavit_discount_type[]\" value=\"".$emp->infonavitDiscountType."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_alimony_discount_type[]\" value=\"".$emp->alimonyDiscountType."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_alimony_discount[]\" value=\"".$emp->alimonyDiscount."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_replace[]\" value=\"".htmlentities($emp->replace)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_purpose[]\" value=\"".htmlentities($emp->purpose)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_requeriments[]\" value=\"".htmlentities($emp->requeriments)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_observations[]\" value=\"".htmlentities($emp->observations)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_viatics[]\" value=\"".$emp->viatics."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_camping[]\" value=\"".$emp->camping."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_position_immediate_boss[]\" value=\"".htmlentities($emp->position_immediate_boss)."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_work_subdepartment[]\" value=\"".$emp->subdepartment_id."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_birth_certificate[]\" value=\"".$emp->doc_birth_certificate."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_proof_of_address[]\" value=\"".$emp->doc_proof_of_address."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_nss[]\" value=\"".$emp->doc_nss."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_ine[]\" value=\"".$emp->doc_ine."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_curp[]\" value=\"".$emp->doc_curp."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_rfc[]\" value=\"".$emp->doc_rfc."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_cv[]\" value=\"".$emp->doc_cv."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_proof_of_studies[]\" value=\"".$emp->doc_proof_of_studies."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_professional_license[]\" value=\"".$emp->doc_professional_license."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_doc_requisition[]\" value=\"".$emp->doc_requisition."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_computer_required[]\" value=\"".$emp->computer_required."\""
									],
									[
										"kind" => "components.inputs.input-text",
										"attributeEx" => "type=\"hidden\" name=\"rq_qualified_employee[]\" value=\"".$emp->qualified_employee."\""
									],
									[
										"kind"          => "components.buttons.button",
										"label"         => "<span class=\"icon-pencil\"></span>", 
										"classEx"       => "edit-employee",
										"variant"		=> "success",
										"attributeEx"   => "data-toggle=\"modal\" type=\"button\" data-target=\"#addEmployee\""
									],
									[
										"kind"          => "components.buttons.button",
										"label"         => "<span class=\"icon-download\"></span>", 
										"buttonElement" => "a",
										"classEx"       => "edit-employee",
										"variant"		=> "secondary",
										"attributeEx"   => "type=\"button\" title=\"Descargar formato\" href=\"".route('requisition.personal.individual',$emp->id)."\""
									],
									[
										"kind"          => "components.buttons.button",
										"label"         => "<span class=\"icon-x\"></span>", 
										"classEx"       => "delete-employee",
										"attributeEx"	=> "type=\"button\"",
										"variant"		=> "red"
									],
								]
							]
						];
						$accountContent = "";
						foreach($emp->bankData as $acc)
						{
							if($acc->type == '1')
							{
								$accountContent .= '<div class="container-accounts">';
							}
							else
							{
								$accountContent .= '<div class="container-accounts-alimony">';
							}
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"alias_".$key."[]\" value=\"".$acc->alias."\"",
								"classEx"		=> "t_alias"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"beneficiary_".$key."[]\" value=\"".$acc->beneficiary."\"",
								"classEx"		=> "t_beneficiary"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"type_".$key."[]\" value=\"".$acc->type."\"",
								"classEx"		=> "t_type"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"idEmployee_".$key."[]\" value=\"".$acc->idEmployee."\"",
								"classEx"		=> "t_idEmployee"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"idCatBank_".$key."[]\" value=\"".$acc->idCatBank."\"",
								"classEx"		=> "t_idCatBank"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"clabe_".$key."[]\" value=\"".$acc->clabe."\"",
								"classEx"		=> "t_clabe"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"account_".$key."[]\" value=\"".$acc->account."\"",
								"classEx"		=> "t_account"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"cardNumber_".$key."[]\" value=\"".$acc->cardNumber."\"",
								"classEx"		=> "t_cardNumber"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"branch_".$key."[]\" value=\"".$acc->branch."\"",
								"classEx"		=> "t_branch"
							])->render();
							$accountContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"bankName_".$key."[]\" value=\"".$acc->bank->description."\"",
								"classEx"		=> "t_bankName"
							])->render();
							$accountContent .= "</div>";
						}
						$body[0]['content'][1] = $accountContent;
						$docsContent = "";
						foreach($emp->documents as $doc)
						{	
							$docsContent .= '<div class="container-other-documents">';
							$docsContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"name_other_document_".$key."[]\" value=\"".$doc->name."\"",
								"classEx"		=> "t_name_other_document"
							])->render();
							$docsContent .= view('components.inputs.input-text',[
								"attributeEx"	=> "type=\"hidden\" name=\"path_other_document_".$key."[]\" value=\"".$doc->path."\"",
								"classEx"		=> "t_path_other_document"
							])->render();
							$docsContent .= "</div>";
						}
						$body[0]['content'][2] = $docsContent;
						array_push($modelBody, $body);
					}
				}
			@endphp
			@component('components.tables.alwaysVisibleTable',[
				"modelHead" => $modelHead,
				"modelBody" => $modelBody,
				"title"     => "Conceptos"
			])
				@slot('attributeExBody')
					id="list_employees"
				@endslot
			@endcomponent	
		</div>	
		<div class="col-span-2 md:col-span-4 grid justify-items-center md:justify-items-start">
			@component('components.buttons.button', ["variant" => "warning", "label" => "<span class='icon-plus'></span> Agregar Empleado"])
				@slot('attributeEx')
					id="btnAddEmployee"  data-toggle="modal" data-target="#addEmployee" type="button"
				@endslot
			@endcomponent
		</div>
	</div>
</div>
