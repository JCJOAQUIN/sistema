@extends('layouts.layout')

@section('title', 'Inicio')

@section('content')
	<div class="container-full">
		<div class="w-full grid md:grid-cols-12">
			<div class="bg-home pr-8 text-justify bg-right bg-white bg-no-repeat xl:col-span-9 lg:col-span-8 col-span-12 w-full">
				@if(Auth::user()->adglobal == 1)
					<div>
						<img class="max-w-sm w-5/6 pt-10 mx-auto" src="{{ url('images/logo-inicio.jpg') }}">
					</div>
					<article>
						<p class="py-2">Somos una empresa desarrolladora de negocios encargada de guíar y administrar eficientemente los recursos (humanos, económicos, financieros y operativos) de las empresas o negocios ejecutados.</p>
						<p class="py-2">Debido al crecimiento y demanda de negocios en el mercado, nuestros servicios han crecido y se han diversificado transformandonos en una empresa que desarrolla e impulsa nuevos negocios a través de la operación de la infraestructura administrativa de la empresa, asesoría legal y centrarse en el giro que son especialistas.</p>
						<p class="py-2 mb-4">Dentro de los servicios que ofrecemos encontramos:</p>
						<h2 class="bg-gradient-to-r from-orange-600 via-orange-400 to-amber-500 inline-block py-2 pl-3 pr-9 text-white relative">
							<strong>OPERACIÓN</strong>
							<div class="pl-4 w-14 inline-block absolute inset-y-0 -right-7 overflow-hidden">
								<div class="h-10 bg-white transform rotate-45"></div>
							</div>
						</h2>
						<p class="py-2 mb-2">Operación de la infraestructura Administrativa</p>
						<ul class="mb-4">
							<li class="mb-1"><strong>Administración Eficiente</strong> de Recursos Humanos de la empresa.</li>
							<li class="mb-1"><strong>Administración Financiera Integral</strong> (Cobranza, pagos y asesoría para movimientos estratégicos).</li>
							<li class="mb-1"><strong>Administración</strong> de Recursos de los proyectos.</li>
						</ul>
						<h2 class="bg-gradient-to-r from-green-700 via-lime-600 to-lime-500 inline-block py-2 pl-3 pr-9 text-white relative">
							<strong>ASESORÍA</strong>
							<div class="pl-4 w-14 inline-block absolute inset-y-0 -right-7 overflow-hidden">
								<div class="h-10 bg-white transform rotate-45"></div>
							</div>
						</h2>
						<p class="py-2 mb-2">Asesoría Corporativa y Empresarial Integral</p>
						<ul>
							<li class="mb-1"><strong>Asesoría Legal</strong> (Mercantíl, Laboral, Corporativa y Penal).</li>
							<li class="mb-1"><strong>Asesoría y Análisis</strong> de la investigación de Mercados.</li>
							<li class="mb-1"><strong>Asesoría</strong> en la Administración Fiscal y Contable.</li>
							<li class="mb-1"><strong>Asesoría y Administración</strong> para el cumplimiento ante instituciones gubernamentales (SAT, IMSS, INFONAVIT, STPS, etc.).</li>
						</ul>
					</article>
					@component('components.labels.title-divisor')
						@slot('classEx')
							mt-8
						@endslot
					@endcomponent
					<article>
						<h2 class="bg-gradient-to-r from-red-600 via-red-500 to-red-300 inline-block py-2 pl-3 pr-9 text-white relative">
							<strong>VISIÓN</strong>
							<div class="pl-4 w-14 inline-block absolute inset-y-0 -right-7 overflow-hidden">
								<div class="h-10 bg-white transform rotate-45"></div>
							</div>
						</h2>
						<p class="py-2 mb-8">
							“Hacer de Ad Global, una desarrolladora de negocios integral de gran impacto para aquellos emprendedores mexicanos que buscan un crecimiento sólido y constante para sus negocios.”
						</p>
						<h2 class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-300 inline-block py-2 pl-3 pr-9 text-white relative">
							<strong>MISIÓN</strong>
							<div class="pl-4 w-14 inline-block absolute inset-y-0 -right-7 overflow-hidden">
								<div class="h-10 bg-white transform rotate-45"></div>
							</div>
						</h2>
						<p class="py-2 mb-8">
							“Elevar la calidad de nuestro trabajo diario y servicio brindado a nuestros clientes internos y externos, con el fin de conservar nuestras relaciones actuales y potencializar nuestro alcance hacia las diferentes áreas de consumo”
						</p>
						<h2 class="bg-gradient-to-r from-yellow-500 via-yellow-400 to-amber-200 inline-block py-2 pl-3 pr-9 text-white relative">
							<strong>VALORES</strong>
							<div class="pl-4 w-14 inline-block absolute inset-y-0 -right-7 overflow-hidden">
								<div class="h-10 bg-white transform rotate-45"></div>
							</div>
						</h2>
						<p class="py-2 mb-8">
							Honestidad, Lealtad, Calidad, Confianza y Trabajo en Equipo.
						</p>
						<p class="py-2 mb-2 text-right italic">
							“Lo que verdaderamente deseas, irremediablemente sucede” (Oscar Wilde)
						</p>
					</article>
				@else
					@php
						if (Auth::user()->enterprise()->exists()) 
						{
							$url = url('images/enterprise/'.Auth::user()->enterprise->first()->path);
							$description = Auth::user()->enterprise->first()->details;
							
						}
						else
						{
							$url = url('images/logo-inicio.jpg');
							$description = '';
						}
					@endphp
					<div>
						<img class="max-w-xs w-4/6 pt-10 mx-auto mb-14" src="{{ $url }}">
					</div>
					<article>
						<p class="py-2 mb-10">
							{{ $description != "" ? $description : '' }}
						</p>
					</article>
				@endif
				<article>
					@if(isset($_COOKIE['follow']))
						@php
							$following	= json_decode(base64_decode($_COOKIE['follow']),true);
						@endphp
						<div class="w-full lg:w-9/12">
							<div class="mt-4 border-solid border-b-4 border-amber-500 text-bluedark-700 font-bold pr-10px pl-3px">
								<span class="icon-clock"></span>&nbsp;MÓDULOS VISITADOS ANTERIORMENTE
							</div>
							@foreach($following as $lastModuleItem)
								<div class="border-solid border-b border-true-gray-200 text-gray-500 my-4 pb-2 w-11/12">
									{{ $lastModuleItem['date'] }}: {{ $lastModuleItem['name'] }}
								</div>
							@endforeach
						</div>
					@endif
				</article>
			</div>
			<div class="border-solid lg:border-l border-gray-400 xl:col-span-3 lg:col-span-4 col-span-12 text-sm mb-3">
				<div class="px-3 py-2 flex justify-center md:justify-start font-bold mb-2 bg-red-500">
					<div class="text-transparent w-6 h-2 hidden md:flex items-center px-2">!</div>
					<a class="no-underline text-white px-3" href="{{ url('tutorial') }}">TUTORIALES</a>
				</div>
				<div class="items-center flex px-3">
					<div class="text-transparent w-6 h-2 flex items-center px-2">!</div>
					<div class="p-3">
						<p class="mb-2 break-all">Se han agregado nuevos video tutoriales de ayuda...</p>
						<p class="underline"><a href="{{ url('tutorial') }}">Leer más»</a></p>
					</div>
				</div>
				@php
					$authUserModules = Auth::user()->module->pluck('id')->toArray();
					// comunicados
					$today        = date('Y-m-d');
					$releasesDate = strtotime('-30 day',strtotime($today));
					$releasesDate = date('Y-m-d',$releasesDate);
					$banners      = App\Releases::whereBetween('date',[''.$releasesDate.' '.date('00:00:00').'',''.$today.' '.date('23:59:59').''])->orderBy('idreleases','DESC')->limit(1)->get();
					// noticias
					$fecha		= date('Y-m-d');
					$nuevafecha	= strtotime('-7 day',strtotime($fecha));
					$nuevafecha	= date('Y-m-d',$nuevafecha);
					$news		= App\News::whereBetween('date',[''.$nuevafecha.' '.date('00:00:00').'',''.$fecha.' '.date('23:59:59').''])->orderBy('date','DESC')->limit(3)->get();
				@endphp
				@if(count($banners) > 0)
					<div class="px-3 py-2 flex justify-center md:justify-start font-bold mt-8 mb-2 bg-orange-500">
						<div class="text-transparent w-6 h-2 hidden md:flex items-center px-2">!</div>
						<a class="no-underline text-white px-3" href="{{ route('releases') }}">COMUNICADOS</a>
					</div>
					@foreach($banners as $banner)
						<div class="items-center flex px-3">
							<div class="text-transparent w-6 h-2 flex items-center px-2">!</div>
							<div class="p-3">
								<span class="text-orange-700 block mb-1">{{$banner->title}}</span>
								<p>{!!substr(str_replace('&nbsp','',strip_tags($banner->content)),0,150)!!}...</p>
							</div>
						</div>
					@endforeach
				@endif
				@if(in_array(83, $authUserModules))
					@if(count($news) > 0)
						<div class="px-3 py-2 flex justify-center md:justify-start font-bold mt-8 mb-2 bg-red-500">
							<div class="text-transparent w-6 h-2 hidden md:flex items-center px-2">!</div>
							<a class="no-underline text-white px-3" href="{{ url('news') }}">ÚLTIMAS NOTICIAS</a>
						</div>
						@foreach($news as $new)
							<div class="items-center flex px-3">
								<div class="text-transparent w-6 h-2 flex items-center px-2">!</div>
								<div class="p-3">
									<span class="block mb-1">{{$new->title}}</span>
									<p class="mb-2 break-all">{!!substr(str_replace('&nbsp','',strip_tags($new->details)),0,150)!!}...</p>
									<p><a class="underline" href="{{route('news.show',$new->idnews)}}">Leer más»</a></p>
								</div>
							</div>
							@if(count($news) > 1 && !$loop->last)
								<hr class="bg-gray-400 content-between ml-12 w-3/4 my-2">
							@endif
						@endforeach
					@endif
				@endif
				@php
					$newTickets = DB::table('tickets')
						->selectRaw('COUNT(*) as tickets')
						->join(DB::raw('(SELECT DISTINCT section_tickets_idsectionTickets FROM user_review_ticket WHERE user_id = '.Auth::user()->id.') as sections'),'idSectionTickets','section_tickets_idsectionTickets')
						->whereNull('assigned_id')
						->where('idStatusTickets',1)
						->first()
						->tickets;
					$answeredTickets = DB::table('tickets')
						->selectRaw('COUNT(*) as tickets')
						->where('request_id',Auth::user()->id)
						->where('idStatusTickets',3)
						->first()
						->tickets;
					$awaitTickets = DB::table('tickets')
						->selectRaw('COUNT(*) as tickets')
						->join(DB::raw('(SELECT DISTINCT section_tickets_idsectionTickets FROM user_review_ticket WHERE user_id = '.Auth::user()->id.') as sections'),'idSectionTickets','section_tickets_idsectionTickets')
						->where('assigned_id',Auth::user()->id)
						->whereIn('idStatusTickets',[1,2])
						->first()
						->tickets;
				@endphp
				@if(in_array(106, $authUserModules) || in_array(108, $authUserModules) || in_array(109, $authUserModules))
					@if(($newTickets > 0 && in_array(106, $authUserModules)) || ($answeredTickets > 0 && in_array(108, $authUserModules)) || ($awaitTickets > 0 && in_array(109, $authUserModules)))
						<div class="px-3 py-2 flex justify-center md:justify-start font-bold mt-8 mb-2 bg-red-500">
							<div class="text-transparent w-6 h-2 hidden md:flex items-center px-2">!</div>
							<a class="no-underline text-white px-3" href="{{ url('/tickets') }}">TICKETS</a>
						</div>
					@endif
					@if($newTickets > 0 && in_array(106, $authUserModules))
						<div class="items-center flex px-3">
							<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-red-500 px-2">!</div>
							<div class="items-center p-3">
								<span class="text-red-500 font-bold block">
									Nuevos
								</span>
								{{ $newTickets }} {{ $newTickets == 1 ? 'ticket' : 'tickets' }} por asignar.
							</div>
						</div>
						@if(($answeredTickets > 0 && in_array(108, $authUserModules)) || ($awaitTickets > 0 && in_array(109, $authUserModules)))
							<hr class="bg-gray-400 content-between ml-12 w-3/4 my-2">
						@endif
					@endif
					@if($answeredTickets > 0 && in_array(108, $authUserModules))
						<div class="items-center flex px-3">
							<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-red-500 px-2">!</div>
							<div class="items-center p-3">
								<span class="text-red-500 font-bold block mb-2">
									Respuestas
								</span>
								{{ $answeredTickets }} {{ $answeredTickets == 1 ? 'ticket' : 'tickets' }} con respuesta.
							</div>
						</div>
						@if($awaitTickets > 0 && in_array(109, $authUserModules))
							<hr class="bg-gray-400 content-between ml-12 w-3/4 my-2">
						@endif
					@endif
					@if($awaitTickets > 0 && in_array(109, $authUserModules))
						<div class="items-center flex px-3">
							<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-red-500 px-2">!</div>
							<div class="items-center p-3">
								<span class="text-red-500 font-bold block mb-2">
									En espera
								</span>
								{{ $awaitTickets }} {{ $awaitTickets == 1 ? 'ticket' : 'tickets' }} por contestar.
							</div>
						</div>
					@endif
				@endif
				@php
					$pendingBill = DB::table('request_models')
						->selectRaw('COUNT(*) as bill')
						->leftJoin('purchases','request_models.folio','purchases.idFolio')
						->where('kind',1)
						->whereIn('status',[3,4,5,10,11,12,18])
						->where(function($query)
						{
							$query->where('purchases.billStatus','Pendiente');
						})
						->where(function ($query)
						{
							$query->where('request_models.idElaborate',Auth::user()->id)
								->orWhere('request_models.idRequest',Auth::user()->id);
						})
						->first()
						->bill;
					$pendingExpenses = DB::table('request_models')
						->selectRaw('COUNT(*) as expenses')
						->leftJoin(DB::raw('(SELECT expenses.resourceId as folio, expenses.total as total FROM expenses INNER JOIN request_models ON expenses.idFolio = request_models.folio AND expenses.idKind = request_models.kind WHERE request_models.status IN(4,5,10,11,12) GROUP BY expenses.resourceId,expenses.total) AS checkup'),'request_models.folio','checkup.folio')
						->where('kind',8)
						->whereIn('status',[5,10,11,12])
						->where('idRequest',Auth::user()->id)
						->join(
							DB::raw('(SELECT DISTINCT departament_id FROM permission_deps JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 32) as deps'),
							'request_models.idDepartment',
							'deps.departament_id'
						)
						->join(
							DB::raw('(SELECT DISTINCT enterprise_id FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 32) as ents'),
							'request_models.idEnterprise',
							'ents.enterprise_id'
						)
						->whereNull('checkup.folio')
						->first()
						->expenses;
				@endphp
				@if($pendingBill > 0 || $pendingExpenses > 0)
					<div class="px-3 py-2 flex justify-center md:justify-start font-bold mt-8 mb-2 bg-green-400 text-white">
						<div class="text-transparent w-6 h-2 hidden md:flex items-center px-2">!</div>
						<span class="px-3">TAREAS PENDIENTES</span>
					</div>
					@if($pendingBill > 0)
						<div class="items-center flex px-3">
							<div class="notification-item-text text-white rounded-full w-6 h-6 flex items-center justify-center bg-green-400 px-2">!</div>
							<div class="items-center p-3 notification-item-text">
								<span class="font-bold text-green-400 block mb-2">
									<a class="no-underline" href="{{ url('/administration/purchase/search?status%5B%5D=5&status%5B%5D=11&status%5B%5D=3&status%5B%5D=10&status%5B%5D=12&status%5B%5D=4&documents=Pendiente') }}">
										Subir factura en OC
									</a>
								</span>
								{{ $pendingBill }} {{ $pendingBill == 1 ? 'compra' : 'compras' }} con factura pendiente.
							</div>
						</div>
						@if($pendingExpenses > 0)
							<hr class="bg-gray-400 content-between ml-12 w-3/4 my-2">
						@endif
					@endif
					@if($pendingExpenses > 0)
						<div class="items-center flex px-3">
							<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-green-400 px-2">!</div>
							<div class="items-center p-3">
								<span class="font-bold text-green-400 block mb-2">
									<a class="no-underline" href="{{ url('administration/expenses/create') }}">
										Comprobar asignación de recurso
									</a>
								</span>
								{{ $pendingExpenses }} {{ $pendingExpenses == 1 ? 'gasto' : 'gastos' }} por comprobar.
							</div>
						</div>
					@endif
				@endif
				<div class="px-3 py-2 flex justify-center md:justify-start font-bold mt-8 mb-2 bg-blue-500 text-white">
					<div class="text-transparent w-6 h-2 hidden md:flex items-center px-2">!</div>
					<span class="px-3">NOTIFICACIONES</span>
				</div>
				@php
					$notifications = array(
						0 => array(
							'check'		=> 36,
							'authorize'	=> 37,
							'module'	=> 24,
							'request'	=> 1,
							'url' 		=> url('/administration/purchase')
						),
						2 => array(
							'check'		=> 34,
							'authorize'	=> 35,
							'module'	=> 26,
							'request'	=> 3,
							'url' 		=> url('/administration/expenses')
						),
						3 => array(
							'check'		=> 54,
							'authorize'	=> 55,
							'module'	=> 51,
							'request'	=> 7,
							'url' 		=> url('/administration/stationery')
						),
						4 => array(
							'check'		=> 65,
							'authorize'	=> 66,
							'module'	=> 62,
							'request'	=> 6,
							'url' 		=> url('/administration/computer')
						),
						5 => array(
							'check'		=> 70,
							'authorize'	=> 71,
							'module'	=> 67,
							'request'	=> 5,
							'url' 		=> url('/administration/loan')
						),
						6 => array(
							'check'		=> 75,
							'authorize'	=> 76,
							'module'	=> 72,
							'request'	=> 4,
							'url' 		=> url('/administration/staff')
						),
						7 => array(
							'check'		=> 87,
							'authorize'	=> 88,
							'module'	=> 84,
							'request'	=> 8,
							'url' 		=> url('/administration/resource')
						),
						8 => array(
							'check'		=> 120,
							'authorize'	=> 121,
							'module'	=> 117,
							'request'	=> 9,
							'url' 		=> url('/administration/refund')
						),
						9 => array(
							'check'		=> 141,
							'authorize'	=> 142,
							'module'	=> 138,
							'request'	=> 10,
							'url' 		=> url('/administration/income')
						),
						10 => array(
							'check'		=> 150,
							'authorize'	=> 151,
							'module'	=> 147,
							'request'	=> 11,
							'url' 		=> url('/administration/movements-accounts')
						),
						11 => array(
							'check'		=> 150,
							'authorize'	=> 151,
							'module'	=> 147,
							'request'	=> 12,
							'url' 		=> url('/administration/movements-accounts')
						),
						12 => array(
							'check'		=> 150,
							'authorize'	=> 151,
							'module'	=> 147,
							'request'	=> 13,
							'url' 		=> url('/administration/movements-accounts')
						),
						13 => array(
							'check'		=> 150,
							'authorize'	=> 151,
							'module'	=> 147,
							'request'	=> 14,
							'url' 		=> url('/administration/movements-accounts')
						),
						14 => array(
							'check'		=> 150,
							'authorize'	=> 151,
							'module'	=> 147,
							'request'	=> 15,
							'url' 		=> url('/administration/movements-accounts')
						),
						15 => array(
							'check'		=> 282,
							'authorize'	=> 283,
							'module'	=> 203,
							'request'	=> 17,
							'url' 		=> url('/administration/purchase-record')
						),
						16 => array(
							'check'		=> 200,
							'authorize'	=> 201,
							'module'	=> 197,
							'request'	=> 18,
							'url' 		=> url('/administration/finance')
						)
					);
					$nominaCounts = DB::table('request_models')
						->selectRaw('SUM(IF(status = 3, 1, 0)) as review, SUM(IF(status = 14, 1, 0)) as construction_review, SUM(IF(status = 4 || status = 15, 1, 0)) as auth')
						->where('kind',16)
						->first();
					$countCDFI = DB::table('bills')
						->selectRaw('COUNT(*) as cfdi')
						->join(
							DB::raw('(SELECT DISTINCT rfc FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module JOIN enterprises ON enterprise_id = id WHERE user_id = '.Auth::user()->id.' AND module_id = 157) as ents'),
							'bills.rfc',
							'ents.rfc'
						)
						->where('status',0)
						->whereNull('folioRequest')
						->where('type','!=','N')
						->first()
						->cfdi;
					$countNomina = DB::table('bills')
						->selectRaw('COUNT(*) as cfdi')
						->join(
							DB::raw('(SELECT DISTINCT rfc FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module JOIN enterprises ON enterprise_id = id WHERE user_id = '.Auth::user()->id.' AND module_id = 180) as ents'),
							'bills.rfc',
							'ents.rfc'
						)
						->where('type','N')
						->whereNull('folioRequest')
						->whereIn('status',[0,6,7])
						->first()
						->cfdi;
					$pendingPayments = App\RequestModel::selectRaw('COUNT(*) as payments')
						->leftJoin(
							DB::raw('(SELECT DISTINCT departament_id FROM permission_deps JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 90) as deps'),
							'request_models.idDepartment',
							'deps.departament_id'
						)
						->leftJoin(
							DB::raw('(SELECT DISTINCT enterprise_id FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 90) as ents'),
							'request_models.idEnterprise',
							'ents.enterprise_id'
						)
						->whereIn('kind',[1,2,3,5,8,9,11,12,13,14,15,16])
						->whereIn('status',[5,12,18])
						->where('payment',0)
						->whereDoesntHave('nominasReal',function($q)
						{
							$q->where('type_nomina',3);
						})
						->whereDoesntHave('groups', function($q)
						{
							$q->where('operationType','=','Entrada');
						})
						->whereDoesntHave('expenses', function($q)
						{
							$q->where(function($q)
							{
								$q->where('reintegro','=',0)->orWhereNull('reintegro');
							})
							->where(function($q)
							{
								$q->where('reembolso','=',0)->orWhereNull('reembolso');
							});
						})
						->where(function($q)
						{
							$q->where('remittance',0)
								->orWhere(function($q)
								{
									$q->where('remittance',1)
									->whereHas('budget',function($q)
									{
										$q->where('status',1);
									});
								});
						})
						->first()
						->payments;
					$n_folios = [];
					$route    = '';
					foreach (Auth::user()->notifications()->where('title','Almacén')->where('end','>=',\Carbon\Carbon::now())->orWhereNull('end')->orderBy('id','desc')->get() as $n)
					{
						$route = $n->route;
						if(strpos($n->body,"Ha llegado un nuevo articulo para la solicitud de compra") >= 0)
						{
							$var = $n->body;
							$var = str_replace('Ha llegado un nuevo articulo para la solicitud de compra: ', '', $var);
							$var = str_replace('.', '', $var);
							if(!in_array($var,$n_folios))
							{
								array_push($n_folios,$var);
							}
						}
						else
						{
							array_push($n_folios,$n->body);
						}
					}
					$n_body = "Ha llegado un nuevo articulo para la solicitud de compra: ";
					if($n_folios > 1)
					{
						$n_body = "Han llegado nuevos articulos para las solicitudes de compra: ";
					}
					foreach ($n_folios as $key => $f)
					{
						if($key == 0)
						{
							$n_body .= "$f";
						}
						else
						{
							$n_body .= ",$f";
						}
					}
					$n_body .= '.';
					$requisitionChecks = DB::table('request_models')->selectRaw('COUNT(*) as request_check')
						->join(
							DB::raw('(SELECT DISTINCT project_id FROM permission_projects JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 231) as projs'),
							'request_models.idProject',
							'projs.project_id'
						)
						->where('kind',19)
						->where('status',3)
						->first()
						->request_check;
					$requisitionAuth = DB::table('request_models')->selectRaw('COUNT(*) as request_auth')
						->join(
							DB::raw('(SELECT DISTINCT project_id FROM permission_projects JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 232) as projs'),
							'request_models.idProject',
							'projs.project_id'
						)
						->where('kind',19)
						->where('status',4)
						->first()
						->request_auth;
					$requisitionVote = DB::table('request_models')->selectRaw('COUNT(*) as request_vote')
						->join(
							DB::raw('(SELECT DISTINCT project_id FROM permission_projects JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 276) as projs'),
							'request_models.idProject',
							'projs.project_id'
						)
						->where('kind',19)
						->where('status',27)
						->first()
						->request_vote;
					$requisitionGenerate = DB::table('request_models')->selectRaw('COUNT(*) as request_generate')
						->join(
							DB::raw('(SELECT DISTINCT project_id FROM permission_projects JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 232) as projs'),
							'request_models.idProject',
							'projs.project_id'
						)
						->join('requisitions','folio','idFolio')
						->where('kind',19)
						->where('status',5)
						->where('requisition_type','!=',3)
						->first()
						->request_generate;
					$countWareHousePending = App\RequestModel::selectRaw('COUNT(*) as warehouse')
						->join(
							DB::raw('(SELECT DISTINCT enterprise_id FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 113) as ents'),
							'request_models.idEnterpriseR',
							'ents.enterprise_id'
						)
						->where('kind',1)
						->whereHas('budget',function($q)
						{
							$q->where('status',1);
						})
						->whereIn('status',[5,10,11,12])
						->where('statusWarehouse',0)
						->where('goToWarehouse',1)
						->has('purchases.documents')
						->first()
						->warehouse;
					$countBudgets = App\RequestModel::selectRaw('COUNT(*) as budgets')
						->leftJoin(
							DB::raw('(SELECT DISTINCT departament_id FROM permission_deps JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 234) as deps'),
							'request_models.idDepartment',
							'deps.departament_id'
						)
						->leftJoin(
							DB::raw('(SELECT DISTINCT enterprise_id FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = 234) as ents'),
							'request_models.idEnterprise',
							'ents.enterprise_id'
						)
						->whereIn('kind',[1,9])
						->where('remittance',1)
						->whereDoesntHave('budget')
						->whereIn('status',[2,3,4,5])
						->where('payment',0)
						->first()
						->budgets;
					$htmlNotifications = array();
					foreach ($notifications as $notification)
					{
						$checks = DB::table('request_models')->selectRaw('COUNT(*) as request_check')
							->leftJoin(
								DB::raw('(SELECT DISTINCT departament_id FROM permission_deps JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = '.$notification['check'].') as deps'),
								'request_models.idDepartment',
								'deps.departament_id'
							)
							->leftJoin(
								DB::raw('(SELECT DISTINCT enterprise_id FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = '.$notification['check'].') as ents'),
								'request_models.idEnterprise',
								'ents.enterprise_id'
							)
							->leftJoin(
								DB::raw('(SELECT DISTINCT project_id FROM permission_projects JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = '.$notification['check'].') as projs'),
								'request_models.idProject',
								'projs.project_id'
							)
							->where('kind',$notification['request'])
							->where('status',3)
							->first()
							->request_check;
						$authorization = DB::table('request_models')->selectRaw('COUNT(*) as request_auth')
							->leftJoin(
								DB::raw('(SELECT DISTINCT departament_id FROM permission_deps JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = '.$notification['authorize'].') as deps'),
								'request_models.idDepartment',
								'deps.departament_id'
							)
							->leftJoin(
								DB::raw('(SELECT DISTINCT enterprise_id FROM permission_ents JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = '.$notification['authorize'].') as ents'),
								'request_models.idEnterprise',
								'ents.enterprise_id'
							)
							->leftJoin(
								DB::raw('(SELECT DISTINCT project_id FROM permission_projects JOIN user_has_modules ON iduser_has_module = user_has_module_iduser_has_module WHERE user_id = '.Auth::user()->id.' AND module_id = '.$notification['authorize'].') as projs'),
								'request_models.idProject',
								'projs.project_id'
							)
							->where('kind',$notification['request'])
							->where('status',4)
							->first()
							->request_auth;
						if($checks > 0 || $authorization > 0)
						{
							$htmlNotifications[] = '
								<div class="items-center flex px-3">
									<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
									<div class="items-center p-3">
										<span class="block w-full">
											<a class="no-underline text-blue-500" href="'.$notification['url'].'">'.App\RequestKind::find($notification['request'])->kind.'</a>
										</span>
										'.($checks > 0 ? '<span class="block mb-1">'.$checks.' '.($checks == 1 ? 'solicitud' : 'solicitudes').' por revisar.</span>' : '').'
										'.($authorization > 0 ? '<span class="block mb-1">'.$authorization.' '.($authorization == 1 ? 'solicitud' : 'solicitudes').' por autorizar.</span>' : '').'
									</div>
								</div>';
						}
					}
					if(($nominaCounts->review > 0 && in_array(168, $authUserModules)) || ($nominaCounts->construction_review > 0 && in_array(169, $authUserModules)) || ($nominaCounts->auth > 0 && in_array(170, $authUserModules)))
					{
						$htmlNotifications[] = '
							<div class="items-center flex px-3">
								<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
								<div class="items-center p-3">
									<span class="block">
										<a class="no-underline text-blue-500" href="'.url('/administration/nomina').'">Nómina</a>
									</span>
									'.($nominaCounts->review > 0 && in_array(168, $authUserModules) ? '<span class="block mb-1">'.$nominaCounts->review.' '.($nominaCounts->review == 1 ? 'solicitud' : 'solicitudes').' por revisar.</span>' : '').'
									'.($nominaCounts->construction_review > 0 && in_array(169, $authUserModules) ? '<span class="block mb-1">'.$nominaCounts->construction_review.' '.($nominaCounts->construction_review == 1 ? 'solicitud' : 'solicitudes').' por revisar en obra.</span>' : '').'
									'.($nominaCounts->auth > 0 && in_array(170, $authUserModules) ? '<span class="block mb-1">'.$nominaCounts->auth.' '.($nominaCounts->auth == 1 ? 'solicitud' : 'solicitudes').' por autorizar.</span>' : '').'
								</div>
							</div>';
					}
					if(($countCDFI > 0 && in_array(157, $authUserModules)) || ($countNomina > 0 && in_array(180, $authUserModules)))
					{
						$htmlNotifications[] = '
							<div class="items-center flex px-3">
								<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
								<div class="items-center p-3">
									<span class="block">
										<a class="no-underline text-blue-500" href="'.url('/administration/billing').'">CDFI Pendientes</a>
									</span>
									'.($countCDFI > 0 && in_array(157, $authUserModules) ? '<span class="block mb-1">'.$countCDFI.' CDFI por timbrar.</span>' : '').'
									'.($countNomina > 0 && in_array(180, $authUserModules) ? '<span class="block mb-1">'.$countNomina.' '.($countNomina == 1 ? 'nómina' : 'nóminas').' por timbrar.</span>' : '').'
								</div>
							</div>';
					}
					if($pendingPayments > 0 && in_array(90, $authUserModules))
					{
						$htmlNotifications[] = '
							<div class="items-center flex px-3">
								<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
								<div class="items-center p-3">
									<span class="block">
										<a class="no-underline text-blue-500" href="'.url('/administration/payments').'">Pagos</a>
									</span>
									<span class="block mb-1">'.$pendingPayments.' '.($pendingPayments == 1 ? 'pago pendiente' : 'pagos pendientes').'.</span>
								</div>
							</div>';
					}
					if(count($n_folios))
					{
						$htmlNotifications[] = '
							<div class="items-center flex px-3">
								<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
								<div class="items-center p-3">
									<span class="block">
										<a class="no-underline text-blue-500" href="'.url($route).'">Almacén</a>
									</span>
									<span class="block mb-1">'.$n_body.'</span>
								</div>
							</div>';
					}
					if((in_array(231, $authUserModules) || in_array(232, $authUserModules) || in_array(276, $authUserModules)) && ($requisitionChecks > 0 || $requisitionAuth > 0 || $requisitionVote > 0))
					{
						$htmlNotifications[] = '
							<div class="items-center flex px-3">
								<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
								<div class="items-center p-3">
									<span class="block">
										<a class="no-underline text-blue-500" href="'.url('/administration/requisition').'">Requisiciones</a>
									</span>
									'.(in_array(231, $authUserModules) && $requisitionChecks > 0 ? '<span class="block mb-1">'.$requisitionChecks.' '.($requisitionChecks == 1 ? 'requisición' : 'requisiciones').' por revisar.</span>': '').'
									'.(in_array(232, $authUserModules) && $requisitionAuth > 0 ? '<span class="block mb-1">'.$requisitionAuth.' '.($requisitionAuth == 1 ? 'requisición' : 'requisiciones').' por autorizar.</span>' : '').'
									'.(in_array(276, $authUserModules) && $requisitionVote > 0 ? '<span class="block mb-1">'.$requisitionVote.' '.($requisitionVote == 1 ? 'requisición' : 'requisiciones').' por votar.</span>' : '').'
									'.(in_array(232, $authUserModules) && $requisitionGenerate > 0 ? '<span class="block mb-1">'.$requisitionGenerate.' '.($requisitionGenerate == 1 ? 'requisición' : 'requisiciones').' para generar solicitudes.</span>' : '').'
								</div>
							</div>';
					}
					if(in_array(113, $authUserModules))
					{
						if($countWareHousePending  > 0)
						{
							$htmlNotifications[] = '
								<div class="items-center flex px-3">
									<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
									<div class="items-center p-3">
										<span class="block">
											<a class="no-underline text-blue-500" href="'.url('/warehouse/tool').'">Almacén</a>
										</span>
										<span class="block mb-1">'.$countWareHousePending.' '.($countWareHousePending == 1 ? 'compra pendiente' : 'compras pendientes').' por cargar en almacén.</span>
									</div>
								</div>';
						}
					}
					if(in_array(234, $authUserModules) && $countBudgets > 0)
					{
						$htmlNotifications[] = '
							<div class="items-center flex px-3">
								<div class="text-white rounded-full w-6 h-6 flex items-center justify-center bg-blue-400 px-2">!</div>
								<div class="items-center p-3">
									<span class="block">
										<a class="no-underline text-blue-500" href="'.url('administration/budget/pending').'">Asignación de Presupuesto</a>
									</span>
									<span class="block mb-1">'.$countBudgets.' '.($countBudgets == 1 ? 'presupuesto' : 'presupuestos').' por revisar.</span>
								</div>
							</div>';
					}
				@endphp				
				@foreach ($htmlNotifications as $not)
					{!! $not !!}
					@if(!$loop->last)
						<hr class="bg-gray-400 content-between ml-12 w-3/4 my-2">
					@endif
				@endforeach
			</div>
		</div>
	</div>
@endsection