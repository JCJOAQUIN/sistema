<?php

namespace App\Http\Controllers;
use App\RealEmployee;
use App\User;
use App\MobileSession;
use App\Project;
use App\EmployeeAttendance;
use App\EmployeeFaceEnrollment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


/* 
	Retornar error = 1 cuando exista algún error de acualquier tipo, de acuerdo con la petición de la función.
	Retornar error = 2 cuando el error sea de sesión.
*/

class MobileRequestController extends Controller
{
	public function __construct()
	{
		
	}

	public function functionRequest(Request $request)
	{
		$data = [
			"error" => 1,
			"message" => "Error"
		];
		switch ($request->function)
		{
			case 'SET_LOGIN':
				$data = self::setLogin($request, $data);
				break;
			case 'VALID_SESSION':
				$data = self::validSession($request, $data);
				break;
			case 'DESTROY_SESSION':
				$data = self::destroySession($request, $data);
				break;
			case 'CHECK_ATTENDANCE':
				$data = self::checkAttendance($request, $data);
				break;
			case 'SET_ENROLLMENT';
				$data = self::storeEnrollment($request, $data);
				break;
			case 'SET_ATTENDANCE':
				$data = self::storeAttendance($request, $data);
				break;
			case 'CHECK_LOCATION':
				$data = self::checkLocation($request, $data);
				break;
		}
		return response()->json($data);
	}

	private function setLogin($request, $data)
	{
		$id   = null;
		$name = null;
		$kind = null;
		$user = User::select(['users.id','users.name','users.password'])
			->join('real_employees','real_employees.id','users.real_employee_id')
			->join('worker_datas',function ($q)
			{
				$q->on('real_employees.id','=','worker_datas.idEmployee')
					->where('worker_datas.workerStatus',1)
					->where('worker_datas.visible',1);
			})
			->where("users.email",$request->name)
			->where('users.sys_user',1)
			->where('users.active',1)
			->first();
		if($user != "")
		{
			if(Hash::check($request->pass, $user->password))
			{
				$id   = $user->id;
				$name = $user->name;
				$kind = 'u';
			}
		}
		else
		{
			$employee = RealEmployee::select(['real_employees.id','real_employees.name'])
				->join('worker_datas',function ($q)
				{
					$q->on('real_employees.id','=','worker_datas.idEmployee')
						->where('worker_datas.workerStatus',1)
						->where('worker_datas.visible',1);
				})
				->leftJoin('users','real_employees.id','users.real_employee_id')
				->where("real_employees.email",$request->name)
				->where("real_employees.curp",$request->pass)
				->whereNull('users.id')
				->first();
			if($employee != "")
			{
				$id = $employee->id;
				$name = $employee->name;
				$kind = 'e';
			}
		}
		if($id != null && $name != null && $kind != null)
		{
			$header             = json_encode(['typ' => 'ADG', 'alg' => 'HS256']);
			$payload            = json_encode(['user_id' => $id, 'kind' => $kind]);
			$base64UrlHeader    = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
			$base64UrlPayload   = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
			$signature          = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, '@adg#2022@', true);
			$base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
			MobileSession::create(
				['user_id' => $id,
				'user_kind' => $kind,
				'token' => $base64UrlSignature]
			);
			$idEmployee = $id;
			if($kind == 'u')
			{
				$user = User::find($id);
				$idEmployee = $user->real_employee_id;
			}
			$enrollment = EmployeeFaceEnrollment::where('employee_id',$idEmployee);
			$externalDatabaseRefID = "";
			if($enrollment->count() > 0)
			{
				$externalDatabaseRefID = $enrollment->first()->external_database_ref_id;
			}
			$data["error"]   = 0;
			$data["message"] = "";
			$data["data"]    = [
				"user_token"            => $base64UrlSignature,
				"user_id"               => $id,
				"name"                  => $name,
				"kind"                  => $kind,
				"externalDatabaseRefID" => $externalDatabaseRefID
			];
			$attendance = EmployeeAttendance::where('employee_id',$idEmployee)->whereRaw('DATE(`created_at`) = DATE(NOW())')->count();
			if($attendance > 0)
			{
				$data['data']['attendanceRecord'] = 1;
			}
			else
			{
				$data['data']['attendanceRecord'] = 0;
			}
		}
		else
		{
			$data["message"] = "La combinación de Usuario/Contraseña es inválida.";
		}
		return $data;
	}

	private function validSession($request, $data)
	{
		$data["error"] = 2;
		$id    = null;
		$name  = null;
		$kind  = null;
		$token = null;
		$mobileSession = MobileSession::where('user_id',$request->user_id)
			->where('token',$request->user_token)
			->first();
		if($mobileSession != "")
		{
			$token = $mobileSession->token;
			$kind  = $mobileSession->user_kind;
			if($mobileSession->user_kind == 'u')
			{
				$user = User::select(['users.id','users.name'])
					->join('real_employees','real_employees.id','users.real_employee_id')
					->join('worker_datas',function ($q)
					{
						$q->on('real_employees.id','=','worker_datas.idEmployee')
							->where('worker_datas.workerStatus',1)
							->where('worker_datas.visible',1);
					})
					->where('users.id',$mobileSession->user_id)
					->where('users.sys_user',1)
					->where('users.active',1)
					->first();
				if($user != "")
				{
					$id   = $user->id;
					$name = $user->name;
				}
				else
				{
					$mobileSession->delete();
				}
			}
			else if($mobileSession->user_kind == 'e')
			{
				$employee = RealEmployee::select(['real_employees.id','real_employees.name'])
					->join('worker_datas',function ($q)
					{
						$q->on('real_employees.id','=','worker_datas.idEmployee')
							->where('worker_datas.workerStatus',1)
							->where('worker_datas.visible',1);
					})
					->leftJoin('users','real_employees.id','users.real_employee_id')
					->where('real_employees.id',$mobileSession->user_id)
					->whereNull('users.id')
					->first();
				if($employee != "")
				{
					$id   = $employee->id;
					$name = $employee->name;
				}
				else
				{
					$mobileSession->delete();
				}
			}
		}
		else
		{
			$data["message"] = "La sesión ha expirado.";
		}
		if($id != null && $name != null && $kind != null)
		{
			$idEmployee = $id;
			if($kind == 'u')
			{
				$user = User::find($id);
				$idEmployee = $user->real_employee_id;
			}
			$enrollment = EmployeeFaceEnrollment::where('employee_id',$idEmployee);
			$externalDatabaseRefID = "";
			if($enrollment->count() > 0)
			{
				$externalDatabaseRefID = $enrollment->first()->external_database_ref_id;
			}
			$mobileSession->touch();
			$data["error"]   = 0;
			$data["message"] = "";
			$data["data"]    = [
				"user_token"            => $token,
				"user_id"               => $id,
				"name"                  => $name,
				"kind"                  => $kind,
				"externalDatabaseRefID" => $externalDatabaseRefID
			];
			$attendance = EmployeeAttendance::where('employee_id',$idEmployee)->whereRaw('DATE(`created_at`) = DATE(NOW())')->count();
			if($attendance > 0)
			{
				$data['data']['attendanceRecord'] = 1;
			}
			else
			{
				$data['data']['attendanceRecord'] = 0;
			}
		}
		else
		{
			$data["message"] = "La sesión ha expirado.";
		}
		return $data;
	}

	private function destroySession($request, $data)
	{
		$mobileSession = MobileSession::where('user_id',$request->user_id)
			->where('token',$request->user_token)
			->first();
		if ($mobileSession != null)
		{
			$mobileSession->delete();
		}
		$data = [
			"error" => 0,
			"message" => ""
		];
		return $data;
	}

	private function storeAttendance($request, $data)
	{
		$data = self::validSession($request, $data);
		if($data['error'] == 0)
		{
			$idEmployee = $data['data']['user_id'];
			if($data['data']['kind'] == 'u')
			{
				$user = User::find($data['data']['user_id']);
				$idEmployee = $user->real_employee_id;
			}
			$employeeModel = RealEmployee::find($idEmployee);
			$now = \Carbon\Carbon::now();
			$audit_trail_image = "/face-tec/".$idEmployee."/".$now->format("Y-m-d")."/audit_trail_image_".$request->externalDatabaseRefID.".jpg";
			$lq_audit_trail_image = "/face-tec/".$idEmployee."/".$now->format("Y-m-d")."/lq_audit_trail_image_".$request->externalDatabaseRefID.".jpg";
			$face_scan = "/face-tec/".$idEmployee."/".$now->format("Y-m-d")."/face_scan_".$request->externalDatabaseRefID;
			\Storage::disk('reserved')->put($audit_trail_image,base64_decode($request->auditTrailImage));
			\Storage::disk('reserved')->put($lq_audit_trail_image,base64_decode($request->lowQualityAuditTrailImage));
			\Storage::disk('reserved')->put($face_scan,base64_decode($request->faceScan));
			$enrollment = EmployeeAttendance::create(
			[
				'employee_id' => $idEmployee,
				'latitude' => $request->latitude,
				'longitude' => $request->longitude,
				'audit_trail_image_path' => $audit_trail_image,
				'low_quality_audit_trail_image_path' => $lq_audit_trail_image,
				'face_scan_path' => $face_scan
			]);
		}
		return $data;
	}

	private function checkAttendance($request, $data)
	{
		$data = self::validSession($request, $data);
		if($data['error'] == 0)
		{
			$idEmployee = $data['data']['user_id'];
			if($data['data']['kind'] == 'u')
			{
				$user = User::find($data['data']['user_id']);
				$idEmployee = $user->real_employee_id;
			}
			$attendance = EmployeeAttendance::where('employee_id',$idEmployee)->whereRaw('DATE(`created_at`) = DATE(NOW())')->count();
			if($attendance > 0)
			{
				$data['data']['attendanceRecord'] = 1;
			}
			else
			{
				$data['data']['attendanceRecord'] = 0;
			}
		}
		return $data;
	}

	private function storeEnrollment($request, $data)
	{
		$data = self::validSession($request, $data);
		if($data['error'] == 0)
		{
			if($data['data']['externalDatabaseRefID'] == "")
			{
				$idEmployee = $data['data']['user_id'];
				if($data['data']['kind'] == 'u')
				{
					$user = User::find($data['data']['user_id']);
					$idEmployee = $user->real_employee_id;
				}
				$audit_trail_image = "/face-tec/".$idEmployee."/enrollment/audit_trail_image_".$request->externalDatabaseRefID.".jpg";
				$lq_audit_trail_image = "/face-tec/".$idEmployee."/enrollment/lq_audit_trail_image_".$request->externalDatabaseRefID.".jpg";
				$face_scan = "/face-tec/".$idEmployee."/enrollment/face_scan_".$request->externalDatabaseRefID;
				\Storage::disk('reserved')->put($audit_trail_image,base64_decode($request->auditTrailImage));
				\Storage::disk('reserved')->put($lq_audit_trail_image,base64_decode($request->lowQualityAuditTrailImage));
				\Storage::disk('reserved')->put($face_scan,base64_decode($request->faceScan));
				$enrollment = EmployeeFaceEnrollment::create(
				[
					'employee_id' => $idEmployee,
					'audit_trail_image_path' => $audit_trail_image,
					'low_quality_audit_trail_image_path' => $lq_audit_trail_image,
					'face_scan_path' => $face_scan,
					'external_database_ref_id' => $request->externalDatabaseRefID
				]);
			}
		}
		return $data;
	}

	private function checkLocation($request, $data)
	{
		$data = self::validSession($request, $data);
		if($data['error'] == 0)
		{
			$idEmployee = $data['data']['user_id'];
			if($data['data']['kind'] == 'u')
			{
				$user = User::find($data['data']['user_id']);
				$idEmployee = $user->real_employee_id;
			}
			$employeeModel = RealEmployee::find($idEmployee);
			$project       = $employeeModel->workerDataVisible->first()->projects;
			if($project == null)
			{
				$data["error"] = 1;
				$data["message"] = "Hay un error en la configuración, por favor contacte a soporte.";
			}
			else
			{
				$distance      = self::getDistance($request->latitude, $request->longitude, $project);
				if($distance == null)
				{
					$data["error"] = 1;
					$data["message"] = "Hay un error en la configuración, por favor contacte a soporte.";
				}
				else
				{
					if($distance <= $project->distance)
					{
						$data["error"]   = 0;
						$data["message"] = "";
					}
					else
					{
						$data["error"]   = 1;
						$data["message"] = "No se encuentra en la ubicación requerida, por favor registre su asistencia en el lugar de trabajo.";
					}
				}
			}
		}
		return $data;
	}

	private function getDistance($lat, $lng, $project)
	{
		if($project->latitude != '' && $project->longitude != '')
		{
			$theta  = $project->longitude - $lng;
			$dist   = sin(deg2rad($project->latitude)) * sin(deg2rad($lat)) +  cos(deg2rad($project->latitude)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
			$dist   = acos($dist);
			$dist   = rad2deg($dist);
			$meters = $dist * 60 * 1853.159616;
			return $meters;
		}
		else
		{
			return null;
		}
	}
}
