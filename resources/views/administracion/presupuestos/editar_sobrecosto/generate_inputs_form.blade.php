@php
    function generateInputForm($type = 'text',$value,$name,$input_name,$values = [])
    {
      $input = '';
      switch ($type) {
        case 'text':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
              <div class='right'>
                <input type='text' name='$input_name' class='new-input-text remove' placeholder='$name' value='$value'>
              </div>
            </div>
          ";
          break;
        case 'number':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
              <div class='right'>
                <input type='text' name='$input_name' class='new-input-text remove number' placeholder='$name' value='$value'>
              </div>
            </div>
          ";
          break;
        case 'decimal':
        case 'decimal2':
        case 'decimal3':
        case 'decimal5':
        case 'decimal6':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
              <div class='right'>
                <input type='text' name='$input_name' class='new-input-text remove $type' placeholder='$name' value='$value'>
              </div>
            </div>
          ";
          break;
        case 'textarea':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
              <div class='right'>
                <textarea type='text' name='$input_name' class='new-input-text remove' placeholder='$name'>$value</textarea>
              </div>
            </div>
          ";
					break;
				case 'date':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
							<div class='right'>
								<input name='$input_name' data-default='". Carbon\Carbon::parse($value)->format('d-m-Y')."' id='$input_name' value='". Carbon\Carbon::parse($value)->format('d-m-Y')."' type='text' class='new-input-text remove datepicker'>
              </div>
            </div>
          ";
          break;
				case 'select':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
              <p>
              <select class='js-$input_name removeselect form-control select' data-title='$name' name='$input_name' multiple='multiple' data-validation='required'>
                ";
                foreach ($values as $key => $s_value) {
                  $input .= "<option ".(($key+1) ==$value ? 'selected' :'') ." value=".($key+1).">$s_value</option>";
                }
    $input .= "</select>
              </p>
            </div>
          ";
          break;
				case 'checkbox':
          $input = "
            <div class='search-table-center-row'>
              <div class='left'>
                <label class='label-form'>$name</label>
              </div>
              <p>
                <input type='checkbox' ".($value == 1 ? 'checked' : '')." name='$input_name' class='new-input-text remove' placeholder='$name'>
              </p>
            </div>
          ";
          break;
        
        default:
          # code...
          break;
      }

      return $input;

    }
@endphp