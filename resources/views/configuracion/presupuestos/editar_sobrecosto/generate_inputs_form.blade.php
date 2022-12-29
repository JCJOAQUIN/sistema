@php
	function generateInputForm($type = 'text',$value,$name,$input_name,$values = [])
	{
	  $input = '';
	  switch ($type) {
		case 'text':
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label",
			[
				'label'		=>	$name,
				"classEx"	=>	"font-bold"
			])));
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text",
			[
				"attributeEx"	=>	"type=\"text\" name=\"$input_name\" placeholder=\"$name\" value=\"$value\"",
				"classEx"		=>	"remove"
			])));
		  /* $input = "
			<div class='search-table-center-row'>
			  <div class='left'>
				<label class='label-form'>$name</label>
			  </div>
			  <div class='right'>
				<input type='text' name='$input_name' class='new-input-text remove' placeholder='$name' value='$value'>
			  </div>
			</div>
		  "; */
			break;
		case 'number':
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
			'label'		=>	$name,
			"classEx"	=>	"font-bold"
			])));
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
			"attributeEx"	=>	"type=\"text\" name=\"$input_name\" placeholder=\"$name\" value=\"$value\"",
			"classEx"		=>	"remove number"
			])));
		 /*  $input = "
			<div class='search-table-center-row'>
			  <div class='left'>
				<label class='label-form'>$name</label>
			  </div>
			  <div class='right'>
				<input type='text' name='$input_name' class='new-input-text remove number' placeholder='$name' value='$value'>
			  </div>
			</div>
		  "; */
			break;
		case 'decimal':
		case 'decimal2':
		case 'decimal3':
		case 'decimal5':
		case 'decimal6':
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
				'label'		=>	$name,
				"classEx"	=>	"font-bold"
			])));
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
				"attributeEx"	=>	"type=\"text\" name=\"$input_name\" placeholder=\"$name\" value=\"$value\"",
				"classEx"		=>	"remove ".$type
			])));
			break;
		case 'textarea':
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label", [
				'label'		=>	$name,
				"classEx"	=>	"font-bold"
			])));
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text", [
				"attributeEx"	=>	"type=\"text\" name=\"$input_name\" placeholder=\"$name\" value=\"$value\"",
				"classEx"		=>	"remove"
			])));



		  /* $input = "
			<div class='search-table-center-row'>
			  <div class='left'>
				<label class='label-form'>$name</label>
			  </div>
			  <div class='right'>
				<textarea type='text' name='$input_name' class='new-input-text remove' placeholder='$name'>$value</textarea>
			  </div>
			</div>
		  "; */
			break;
		case 'date':
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label",
			[
				'label'		=>	$name,
				"classEx"	=>	"font-bold"
			])));
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.input-text",
			[
				"attributeEx"	=>	"type=\"text\" name=\"$input_name\" id=\"$input_name\" data-default=\"".Carbon\Carbon::parse($value)->format('d-m-Y')."\" value=\"".Carbon\Carbon::parse($value)->format('d-m-Y')."\"",
				"classEx"		=>	"remove datepicker"
			])));

/* 

			$input =
			"
				<div class='search-table-center-row'>
				<div class='left'>
					<label class='label-form'>$name</label>
				</div>
				<div class='right'>
					<input name='$input_name' data-default='". Carbon\Carbon::parse($value)->format('d-m-Y')."' id='$input_name' value='". Carbon\Carbon::parse($value)->format('d-m-Y')."' type='text' class='new-input-text remove datepicker'>
				</div>
				</div>
			"; */
		  break;
		case 'select':
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.labels.label",
			[
				'label'		=> $name,
				"classEx"	=>	"font-bold"
			])));
			foreach ($values as $key => $s_value)
			{
				if (($key+1) ==$value)
				{
					$optionSelects[] =
					[
						"value" 		=>	$key+1,
						"description"	=>	$s_value,
						"selected"		=>	"selected"
					];
				}
				else
				{
					$optionSelects[] =
					[
						"value"			=>	$key+1,
						"description"	=>	$s_value,
					];
				}
			}
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.select",
			[
				"attributeEx"	=> "data-title=\"$name\" name=\"$input_name\" multiple=\"multiple\" data-validation=\"required\" placeholder=\"SELECCIONE $name\"",
				"classEx"		=>	"js-$input_name removeselect select",
				"options"		=>	$optionSelects
			])));
		 /*  $input = "
			<div class='search-table-center-row'>
			  <div class='left'>
				<label class='label-form'>$name</label>
			  </div>
			  <p>
			  <select class='js-$input_name removeselect form-control select' data-title='$name' name='$input_name' multiple='multiple' data-validation='required'>
				";
				foreach ($values as $key => $s_value) {
				  $input .= "<option ".(($key+1) ==$value ? 'selected' :'') ." value=".($key+1).">$s_value</option>";
				} */
	/* $input .= "</select>
			  </p>
			</div>
		  "; */
			break;
		case 'checkbox':
			if ($value == 1)
			{
				$check	=	"checked";
			}
			else
			{
				$check	=	"";
			}
			$input .= html_entity_decode( preg_replace("/(\r)*(\n)*/", "",view("components.inputs.checkbox",
			[
				"attributeEx"	=>	"type=\"checkbox\" name=\"$input_name\" placeholder=\"$name\" $check",
				"classEx"		=>	"remove",
			])));
		 /*  $input = "
			<div class='search-table-center-row'>
			  <div class='left'>
				<label class='label-form'>$name</label>
			  </div>
			  <p>
				<input type='checkbox' ".($value == 1 ? 'checked' : '')." name='$input_name' class='new-input-text remove' placeholder='$name'>
			  </p>
			</div>
		  "; */
		  break;
		default:
		  break;
	  }
	  return $input;
	}
@endphp