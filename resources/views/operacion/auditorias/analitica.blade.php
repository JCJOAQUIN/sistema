@extends('layouts.child_module')

@section('data')
    @component("components.labels.title-divisor")
		Buscar
	@endcomponent

    @component("components.forms.form", ["attributeEx" => "action=\"".route('audits.analitycs')."\" method=\"GET\" id=\"form-search\""])
        @component("components.containers.container-form")
            <div class="col-span-2">
                @component("components.labels.label") Rango de fechas: @endcomponent
                @php                
                    $inputs= [
                        [
                            "input_classEx" => "input-text-date",
                            "input_attributeEx" => "name=\"mindate\" id=\"mindate\" step=\"1\" placeholder=\"Desde\" value=\"".(isset($mindate) ? $mindate : '')."\" data-validation=\"required\"",
                        ],
                        [
                            "input_classEx" => "input-text-date",
                            "input_attributeEx" => "name=\"maxdate\" id=\"maxdate\" step=\"1\" placeholder=\"Hasta\" value=\"".(isset($maxdate) ? $maxdate : '')."\" data-validation=\"required\"",
                        ]
                    ];
                @endphp

                @component("components.inputs.range-input",["inputs" => $inputs])@endcomponent
            </div>

            <div class="col-span-2">
                @component("components.labels.label")
                    Proyecto:
                @endcomponent

                @php
                    $optionsProy =  collect();
                    
                    if(isset($project_id))
                    {
                        $project = App\Project::find($project_id)->first();
                        $optionsProy = $optionsProy->concat([["value" => $project->idproyect, "description" => $project->proyectName, "selected" => "selected"]]);
                    }
                @endphp

                @component('components.inputs.select', ["options" => $optionsProy])
                    @slot('attributeEx')
                        name="project_id" 
                        multiple="multiple" 
                        id="project_id"
                        data-validation="required"
                    @endslot
                    @slot('classEx')
                        js-project
                    @endslot
                @endcomponent
            </div>

            <div class="col-span-2 md:col-span-4 grid md:flex md:items-center justify-center md:justify-start space-x-2">
                @component("components.buttons.button-search", ["variant" => "warning", "attributeEx" => "type=\"submit\"", "label" => "<span class=\"icon-search\"></span> Buscar"]) @endcomponent
                @component("components.buttons.button", ["buttonElement" => "a", "variant" => "reset", "classEx" => "bg-gray-200 px-7 py-2 rounded cursor-pointer hover:bg-gray-200 uppercase font-bold text-sm h-9 text-blue-gray-700", "attributeEx" => "href=\"".strtok($_SERVER['REQUEST_URI'], '?')."\""])Borrar campos @endcomponent
            </div>
        @endcomponent
        @if(isset($audits) && count($audits) > 0)
            @component("components.labels.title-divisor")
                Gráficos
            @endcomponent

            @php
                setlocale(LC_TIME,"spanish");
                $severity_factor    =   $total_persons  =   $total_n1_3 =   $total_n1   =   $total_n3   =   $countFulfilled =   $iai    =  $ias =   0;
                $arrayIAS = [];
                $datesArray = [];
                $datesArrayBK = [];

                foreach($audits as $audit)
                {

                    $n1_3			= $audit->countDangerousnessOneThird();
                    $total_n1_3         = $total_n1_3 + $n1_3;
                    $n1				= $audit->countDangerousnessOne();
                    $total_n1           = $total_n1 + $n1;
                    $n3				= $audit->countDangerousnessThree();
                    $total_n3           = $total_n3 + $n3;

                    $statusAverage  = $audit->statusAverage();
                    
                    ($statusAverage == "100%") ? $countFulfilled++ : "";

                    $total_persons	= $total_persons + $audit->people_involved;

                    $severity_factor = $severity_factor + $audit->severity_factor;
                    
                    $arrayIAS[] = $audit->ias;
                    
                    $ias = $ias + $audit->ias;
                    $iai = $iai + $audit->iai;
                    $datesArray[] = $audit->date;

                }
            
                $ias = $ias/count($audits);
                $iai = $iai/count($audits);
                $compliance = ($countFulfilled*100)/count($audits);
                
            @endphp

            <div class="grid md:grid-cols-3 grid-cols-1 gap-2 mx-4">
                <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white max-w-sm">
                    @component('components.labels.label')
                        Totales
                        @slot('classEx')
                            text-xl leading-tight font-medium mb-2
                        @endslot
                    @endcomponent
                    <div class="w-full mb-20 grid grid-cols-5">
                        @component("components.labels.label")
                            Total de Observaciones:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$severity_factor}}
                            @slot('classEx')
                                pl-3
                            @endslot
                        @endcomponent
                    </div>
                    <div class="w-full mb-20 grid grid-cols-5">
                        @component("components.labels.label")
                            Total de Trabajadores:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$total_persons}}
                            @slot('classEx')
                                pl-3
                            @endslot
                        @endcomponent
                    </div>
                    <div class="w-full mb-20 grid grid-cols-5">
                        @component("components.labels.label")
                            Promedio IAS:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{ number_format($ias, 2) }}
                            @slot('classEx')
                                pl-2
                            @endslot
                        @endcomponent
                    </div>
                </div>
                <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white max-w-sm">
                    @component('components.labels.label')
                        Total de observaciones por factor de severidad
                        @slot('classEx')
                            text-xl leading-tight font-medium mb-2
                        @endslot
                    @endcomponent
                    <div class="w-full mb-10 grid grid-cols-5">
                        <div class="w-10 h-5 bg-green-500"></div>
                        @component("components.labels.label")
                            Seguro 98% al 100%:
                            @slot('classEx')
                                col-span-3 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$total_n1_3}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>
                    <div class="w-full mb-10 grid grid-cols-5">
                        <div class="w-10 h-5 bg-yellow-500"></div>
                        @component("components.labels.label")
                            Preventivo 95% al 98%:
                            @slot('classEx')
                                col-span-3 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$total_n1}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>
                    <div class="w-full mb-10 grid grid-cols-5">
                        <div class="w-10 h-5 bg-red-600"></div>
                        @component("components.labels.label")
                            Peligro menor del 95%:
                            @slot('classEx')
                                col-span-3 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$total_n3}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>
                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Total de observaciones:
                            @slot('classEx')
                                col-span-4 pl-5 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$severity_factor}}
                        @endcomponent
                    </div>
                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Total de trabajadores observados:
                            @slot('classEx')
                                col-span-4 pl-5 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$total_persons}}
                        @endcomponent
                    </div>
                </div>
                <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white max-w-sm">
                    @component('components.labels.label')
                        Auditoría
                        @slot('classEx')
                            text-xl leading-tight font-medium mb-2
                        @endslot
                    @endcomponent
                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Programadas:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{count($audits)}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>

                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Cumplidas:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$countFulfilled}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>

                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            % Cumplimiento:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{round($compliance,2)}}%
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>

                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Promedio IAI:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{round($iai,2)}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>
    
                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Promedio IAS:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{round($ias,2)}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>

                    <div class="w-full mb-10 grid grid-cols-5">
                        @component("components.labels.label")
                            Total de personas trabajando inseguras:
                            @slot('classEx')
                                col-span-4 font-medium
                            @endslot
                        @endcomponent
                        @component("components.labels.label")
                            {{$severity_factor}}
                            @slot('classEx')
                                col-span-1
                            @endslot
                        @endcomponent
                    </div>
                </div>
            </div>
            <div class="grid md:grid-cols-2 grid-cols-1 gap-2 mx-4 pt-4">
                <div class="block p-3 col-span-2 rounded-lg border border-gray-400 bg-white">
                    <div id="showChartBar"></div>
                </div>
            </div>
            <div class="new-chart">
                <div class="grid md:grid-cols-2 grid-cols-1 gap-2 mx-4 pt-4">
                    <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white">
                        @component('components.labels.label')
                            Promedio de IAS por frente
                            @slot('classEx')
                                text-xl leading-tight font-medium mb-2
                            @endslot
                        @endcomponent
                        <div class="w-full mb-10 grid grid-cols-5">
                            @foreach($dataArrayAudits as $dataArrayAudit)
                                @component("components.labels.label")
                                    {{$dataArrayAudit['wbs_description']}}
                                    @slot('classEx')
                                        col-span-4 font-medium
                                    @endslot
                                @endcomponent
                                @component("components.labels.label")
                                    {{round($dataArrayAudit['AuditsWBS'],2)}}
                                    @slot('classEx')
                                        col-span-1
                                    @endslot
                                @endcomponent
                            @endforeach
                        </div>
                    </div>

                    <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white">
                        @component('components.labels.label')
                            Personas trabajando de forma insegura por frente
                            @slot('classEx')
                                text-xl leading-tight font-medium mb-2
                            @endslot
                        @endcomponent
                        <div class="w-full mb-10 grid grid-cols-5">
                            @foreach($dataArrayAudits as $dataArrayAudit)
                                @component("components.labels.label")
                                    {{$dataArrayAudit['wbs_description']}}
                                    @slot('classEx')
                                        col-span-4 font-medium
                                    @endslot
                                @endcomponent
                                @component("components.labels.label")
                                    {{$dataArrayAudit['severity_factor']}}
                                    @slot('classEx')
                                        col-span-1
                                    @endslot
                                @endcomponent
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 grid-cols-1 gap-2 mx-4 pt-4">
                    <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white">
                        @component('components.labels.label')
                            Cantidad de auditorias por frente
                            @slot('classEx')
                                text-xl leading-tight font-medium mb-2
                            @endslot
                        @endcomponent
                        <div id="showChartBarCols"></div>
                    </div>

                    <div class="block p-3 col-span-1 rounded-lg border border-gray-400 bg-white">
                        @component('components.labels.label')
                            Auditorías por contratista
                            @slot('classEx')
                                text-xl leading-tight font-medium mb-2
                            @endslot
                        @endcomponent
                        <div id="showChartBarCols2"></div>
                    </div>
                </div>

                <div class="mx-4 pt-4">
                    <div class="block p-3 rounded-lg border border-gray-400 bg-white">
                        @component('components.labels.label')
                            Desviaciones Observadas
                            @slot('classEx')
                                text-xl leading-tight font-medium mb-2
                            @endslot
                        @endcomponent
                        <div id="showChartPie"></div>
                    </div>
                </div>
            </div> 
        @else
            @isset($audits)
                @component("components.labels.not-found", ["attributeEx" => "not-found", "text" => "NO HAY DATOS"]) @endcomponent
            @endisset  
        @endif
    @endcomponent
@endsection

@section('scripts')
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('css/daterangepicker.css') }}">
	<link rel="stylesheet" href="{{ asset('css/jquery.timepicker.min.css') }}">
	<script src="{{ asset('js/jquery-ui.js') }}"></script>
	<script src="{{ asset('js/jquery.numeric.js') }}"></script>
	<script src="{{ asset('js/papaparse.min.js') }}"></script>
	<script src="{{ asset('js/jquery.timepicker.min.js') }}"></script>
	<script src="{{ asset('js/datepair.min.js') }}"></script>
    <script src="{{ asset('js/apexcharts.js') }}"></script>
	<script src="{{ asset('js/jquery.datepair.min.js') }}"></script>
	<script src="{{ asset('js/daterangepicker.js') }}"></script>
	<script src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript"> 
        
        $(document).ready(function()
        {
           generalSelect({'selector': '#project_id', 'model': 14});

            @if(isset($audits) && count($audits) > 0)

                //  TENDENCIA DE RESULTADOS OBTENIDOS
                datesArray =  @json($datesArray);
                total_persons = @json($countFulfilled);
                
                arrayIAS = @json($arrayIAS);
                auditsWBS =  @json($auditsWBS);

                dataArray = [];
                $.each(arrayIAS,function(i,v) 
                {
                    dataArray.push(v);
                });
               
                line(dataArray, datesArray, "#showChartBar");
                
                // CANTIDAD DE AUDITORIAS POR FRENTE
                quantity_auditorias_wbs = [];
                description_auditorias_wbs = [];
                @foreach($dataArrayAudits as $dataArrayAudit)
                    quantity_auditorias_wbs.push(@json($dataArrayAudit['quantity_wbs']));
                    description_auditorias_wbs.push(@json(substr($dataArrayAudit['wbs_description'], 0, 15))+"...");
                @endforeach
                bar(quantity_auditorias_wbs, description_auditorias_wbs, "#showChartBarCols");
                
                // AUDITORIAS POR CONTRATISTA
               @php
                    foreach($auditsContractor as $auditContractor)
                    {
                        foreach($auditContractor as $detailsAuditContractor){}
                        $contractor_name = App\Contractor::selectRaw("name")->find($detailsAuditContractor['contractor_id']);
                        $contractor_array[] = $contractor_name->name;
                        $quantity_contractor_array[] = count($auditContractor);
                    }
                @endphp
                quantity_contractor_auditorias_wbs = @json($quantity_contractor_array);
                contractor_auditorias_wbs = @json($contractor_array);
                bar(quantity_contractor_auditorias_wbs,contractor_auditorias_wbs, "#showChartBarCols2");
                
                // DESVIACIONES OBSERVADAS GRAL
                categories = @json($categoriesReal);
                category = [];
                $.each(categories,function(i,v) 
                {
                    category.push(v["name"]);
                });
               
                array_sum_category = @json($categories);
                pie(array_sum_category, category, "#showChartPie"); //Calculo por categorias
                @php
                    $subcategoryArray = [];
                    $i = 0;
                    
                    foreach($subcategoriesArray as $subcategories)
                    {
                        foreach($subcategories as $subcategory)
                        {
                            
                            $subcategoryArray[$i][] = $subcategory['name'];
                        }
                        $i++;
                    }
                    
                @endphp
                subcategoryArray = @json($subcategoryArray);
                
                dataSubcategoryValues = @json($real_array_subcategory_values);
                
                $.each(categories,function(i,v)
                {
                    if(dataSubcategoryValues[i].every(item => item === 0) == false)
                    {

                        @php
                        $inputTitle = view('components.labels.label',[
										"classEx" => "text-xl leading-tight font-medium mb-2"
									])->render();
                        @endphp
                        inputTitle = '{!!preg_replace("/(\r)*(\n)*/", "", $inputTitle)!!}';
                        inputT = $(inputTitle);
						inputT.text('Desviaciones Observadas en:'+v["name"]);
                        $('.new-chart').append($('<div class="mx-4 pt-4"></div>')
                                .append($('<div class="block p-3 rounded-lg border border-gray-400 bg-white"></div>')
                                    .append(inputT)
                                        .append($('<div id="showChartPie'+i+'"></div>'))));
                        $('#showChartPie').append(pie(dataSubcategoryValues[i], subcategoryArray[i], "#showChartPie"+i));
                    }
                });
            @endif
        });
        function bar(data, categories, divName)
        {
            var options = {
                series: [{
                    data: data
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                    // events: {
                    //     click: function(chart, w, e) {
                    //         console.log(e)
                    //     }
                    // },
                    toolbar: 
                    {
                        show: false
                    },
                },
                plotOptions: {
                    bar: {
                        columnWidth: '65%',
                        distributed: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector(divName), options);
            $(chart.render());
        }
        function line(data, categories, divName)
        {
            
            var options = 
            {
                colors: ['#000000'],
                series: 
                [
                    {
                        name: "IAS",
                        data: data
                    }
                ],
                chart: 
                {
                    height: 350,
                    type: 'line',
                    dropShadow: 
                    {
                        enabled: false,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0
                    },
                    toolbar: 
                    {
                        show: false
                    },
                },
                annotations: 
                {
                    yaxis: 
                    [
                        {
                            y: 0,
                            y2: 95,
                            fillColor: '#F44336',
                            opacity: 0.6
                        },
                        {
                            y: 95,
                            y2: 98,
                            fillColor: '#FFEB3B',
                            opacity: 0.6
                        },
                        {
                            y: 98,
                            y2: 100,
                            fillColor: '#4CAF50',
                            opacity: 0.6
                        }
                    ],
                },
                dataLabels: 
                {
                    enabled: true,
                },
                stroke: 
                {
                    curve: 'straight'
                },
                title: 
                {
                    text: 'Tendencia de resultados obtenidos',
                    align: 'left'
                },
                xaxis: 
                {
                    categories: categories
                },
                yaxis: 
                {
                    min: 0,
                    max: 100
                },
            };
            var chart = new ApexCharts(document.querySelector(divName), options);
            $(chart.render());
        }
        function pie(data, categories, divName)
        {
            var options = 
            {
                series: data,
                chart: 
                {
                    width: 600,
                    type: 'pie',
                },
                labels: categories,
                legend: 
                {
                    position: 'left'
                },
                responsive: 
                [{
                    breakpoint: 600,
                    options: 
                    {
                        chart: 
                        {
                            width: 250,
                            height: 400
                        },
                        legend: 
                        {
                            position: 'bottom'
                        }
                    }
                }]
            };

            if(divName !== false)
            {
                var chart = new ApexCharts(document.querySelector(divName), options);
                chart.render();
            }
        }
    </script> 
@endsection


