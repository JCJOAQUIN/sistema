@extends('layouts.layout')

@section('title', 'Inicio')

@section('content')
	<div class="container-blocks-all">
		@component('components.labels.title-divisor')    TUTORIALES @endcomponent
		<p><br></p>
		<p>
			A continuación presentaremos videotutoriales de apoyo en el manejo de los diferentes módulos del sistema.
		</p>

		<div class="form-container">
			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">ALMACÉN</th>
					</thead>
					<tbody>
						<tr>
							<td>
								Solicitud de Almacén 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/-gm4xa415Z8"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Alta de Almacén</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/DfFv3Ux76VQ"><span class="icon-search"></span> Ver Tutorial</button>								
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">REQUISICIONES</th>
					</thead>
					<tbody>
						<tr>
							<td>Flujo Completo de Requisiciones</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/bihSFwoXTk0"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Asignación de Presupuestos 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/uVVijbl0SX8"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Compras 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/fz28eDELn6w"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Reembolso 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/3fjNfloCjX8"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Requisiciones 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/fKTZs0cGGhM"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">COMPRAS Y GASTOS</th>
					</thead>
					<tbody>
						
						<tr>
							<td>
								Asignación de Recurso 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/zcrBdUhN8hE"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Compras 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/PnNAYd9USgM"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Comprobación de Gasto
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/dTM704JMVDw"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Reembolso 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/m4U1iNUmOeY"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">FINANZAS</th>
					</thead>
					<tbody>
						<tr>
							<td>
								Gastos Financieros
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/zA8d9TehRrU"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Reclasificación de Gasto
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/VvycHftCqWY"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2" >INGRESOS Y MOVIMIENTOS</th>
					</thead>
					<tbody>
						<tr>
							<td>
								Movimientos Entre Cuentas
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/CtziuMoKikA"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Proyección de Ingresos
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/-XcVd025L8E"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">RECURSOS HUMANOS</th>
					</thead>
					<tbody>
						<tr>
							<td>
								Nómina 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/SDKA6DDKZlw"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Préstamo Personal 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/c4pJpwMmQB0"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>
								Personal 
							</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/uvll24neggY"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">TESORERÍA</th>
					</thead>
					<tbody>
						<tr>
							<td>Conciliación de Egresos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/umCQpKEJZlI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Conciliación de Ingresos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/c8YP8JNp5pQ"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Facturación</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/HcS2EACzPOg"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Pagos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/aG-VI9ISKNg"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Registro de Movimientos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/L_wepmNbR4M"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">TICKETS</th>
					</thead>
					<tbody>
						<tr>
							<td>Solicitud y seguimiento de Tickets</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/eeDrdTeczH0"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">REPORTES</th>
					</thead>
					<tbody>
						<tr>
							<td>Almacén</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/5fFmzGYQ8Fo"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Asignación de Recurso</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/HgEkX139Udw"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Balance y Estado de Resultados</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/g5wKCnd41kI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Comisiones de Grupos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/BQ3LKN3aGlQ"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Compras</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/68Wr33ATd30"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Comprobación de Gasto</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/Wz8yaWhipKY"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Concentrado de Cuentas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/EqsTsHhVlqU"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Concentrado de Gastos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/QlFTOSCAijc"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Concentrado de Partidas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/5_1-BmaPOUI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Conciliación</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/b0IYnBawR34"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Cuentas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/ACFfP38_Isg"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Desglose de Cuentas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/LQyWfQ76Q3k"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Gastos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/ym21IqXLg6A"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>ISR Causado</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/JZeYd6eygPQ"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>IVA</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/boeVPHKq0zo"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Maestro</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/VTB_f5-eFg0"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Movimientos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/rsFNvOiv3E0"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Movimientos Entre Cuentas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/e-CXjKhp2ZM"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Nómina</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/A1Z_8pZDlFc"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Nómina por Empleado</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/MKRSZQ_yLnw"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Pagos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/g36dXRQ6uFI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Papelería</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/fxYILiUt-nc"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Proyección de Ingresos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/ElbZmxIH47E"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Reembolso</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/rLbkPeh-Y_c"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Requisiciones</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/8mZpEj7nBYs"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Tickets</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/c9RQJAf8vco"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">CONFIGURACIÓN</th>
					</thead>
					<tbody>
						<tr>
							<td>Bancos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/HPCttAa-0IA"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Comunicados</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/XovZG_aKtQY"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Clientes</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/u6vRoTYhU-8"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Cuentas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/D3Tn2p6mo5Q"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Cuentas Bancarias</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/WR66RgHQQMI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Departamento</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/7TZthXQ_BW4"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Dirección</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/6MMyoYtPBuo"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Empleados</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/PwARnFa9jzc"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Empresa</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/GVqDU7xCwUI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Estados de Solicitud</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/yWgH5rEy3QI"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Etiquetas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/TdxEqnzYzX4"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Lugares de Trabajo</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/qT9vzFWNUEU"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Parámetros</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/lpECrCSmiUc"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Proveedores</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/YGVPPi_tW3Y"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Proyectos</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/ZJwSP23om_c"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Responsabilidades</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/Ccjuv2ZN7fE"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Solicitudes Automáticas</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/Om3ikcdjVNA"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Tarjetas de Crédito</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/gWox6VyL0jo"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Usuario</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/bdjeBJQQVjc"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="group">
				<table class="table">
					<thead class="thead-dark">
						<th colspan="2">OTROS</th>
					</thead>
					<tbody>
						<tr>
							<td>Perfil</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/voPMkNDGsaE"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Noticias</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/48UlOtwiL1A"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
						<tr>
							<td>Sugerencias</td>
							<td>
								<button type="button" class="btn btn-orange" data-toggle="modal" data-target="#dataUrlModal" data-url="https://www.youtube.com/embed/vevTf9BWHgk"><span class="icon-search"></span> Ver Tutorial</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="modal" id="dataUrlModal" tabindex="-1">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header" style="padding: .3rem 1rem;">
					<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" style="margin:0;">
					<div class="embed-container">
						<iframe id="frame" width="560" height="315" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<div class="modal-footer" style="padding: .3rem .8rem;">
				<button type="button" class="btn btn-blue" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		$(document).on('click','[data-toggle="modal"]',function()
		{
			url	= $(this).attr('data-url');

			$('#frame').attr('src',url);
		})
		.on('click','[data-dismiss="modal"]',function()
		{
			$('#frame').removeAttr('src');
		})
	</script>
@endsection	



			