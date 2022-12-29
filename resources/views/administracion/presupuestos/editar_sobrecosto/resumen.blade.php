
@php

function generateTable($budget_id)
{

  $COSummaryConcept = App\COSummaryConcept::where('idUpload',$budget_id)->get();
  $table_body =
  "
    <table class='table table-striped'>
      <thead class='thead-dark'>
        <tr>
          <th>CLAVE</th>
          <th>C O N C E P T O</th>
          <th>IMPORTE</th>
          <th>%</th>
        </tr> 
      </thead>
      <tbody>
        <tr>";
      foreach ($COSummaryConcept as $bg) {
        $table_body.="
        <td>
          ".
          generateInputForm(
          'text',//input type
          $bg->clave,//input value
          '',//input title
          "clave[$bg->id]")

          ."
          </td>
        <td>
          ".
          generateInputForm(
          'textarea',//input type
          $bg->concepto,//input value
          '',//input title
          "concepto[$bg->id]")

          ."
          </td>
        <td>
          ".
          generateInputForm(
          'decimal6',//input type
          $bg->importe,//input value
          '',//input title
          "importe[$bg->id]")

          ."
          </td>
        <td>
          ".
          generateInputForm(
          'decimal6',//input type
          $bg->porcentaje,//input value
          '',//input title
          "porcentaje[$bg->id]")

          ."
          </td>
      </tr>";
      }
      $table_body .= "</tbody>
    </table>";


    return $table_body;
}




$names = [
  'custom' => true,
  'data' => generateTable($budget_id),
];

$campos = [

  'COSummaryConcept' => [
    'title' => 'RESUMEN ',
    'names' =>	$names,
  ],

];

@endphp

{!! Form::open(['route' => ['Sobrecosto.save.resumen',$budget_id], 'method' => 'POST', 'id' => 'container-alta','files'=>true]) !!}
<input name="save" value="true" hidden>
@foreach ($campos as $campo)
<div class="margin_top">
  @component('components.labels.title-divisor')    {{ $campo['title'] }}</strong>
  </center>
  <div class='divisor'>
    <div class='gray-divisor'></div>
    <div class='orange-divisor'></div>
    <div class='gray-divisor'></div>
  </div>

  @if (array_key_exists('custom',$campo['names']))
    {!! $campo['names']['data'] !!}
  @else
    @foreach ($campo['names'] as $key => $value)
      <div class='container-blocks'>
        <div class='search-table-center'>
          {!! generateInputForm(
            $value['type'],//input type
            $campo['db'][$key],//input value
            $value['name'],//input title
            $key,//input name and db name
            array_key_exists('values',$value) ? $value['values'] : []//select values
            )
          !!}
        </div>
      </div>
    @endforeach
  @endif
</div>
@endforeach
<center>
<button type="submit" class="btn btn-red">Siguiente</button>
</center>
{!! Form::close() !!}