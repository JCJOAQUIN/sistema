<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcurementData extends Model
{
	protected $fillable = [
		'wbs',
		'nombre_wbs',
		'no_rq',
		'fecha_recepcion_rq',
		'comprador',
		'estatus',
		'pendiente_documental',
		'fecha_oc',
		'pedido_oc',
		'descripcion_pedido',
		'proveedor',
		'partida',
		'tag',
		'pulgadas_mat',
		'unidad',
		'cantidad',
		'concepto',
		'moneda',
		'precio_unitario_mxn',
		'importe_mxn',
		'iva_16',
		'total_partida_mxn',
		'total_oc_mxn',
		'precio_unitario_usd',
		'importe_usd',
		'iva_16_2',
		'total_partida_usd',
		'total_oc_usd',
		'incoterm',
		'hitos',
		'hito_descrtipcion',
		'factura',
		'fecha',
		'monto_con_iva',
		'total_facturado',
		'fecha_pago',
		'est',
		'fianza',
		'estatus_fianza',
		'entrega_contractual',
		'recepcion_en_sitio',
		'observaciones',
		'user_id'
	];
}
