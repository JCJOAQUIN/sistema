<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App;
use Auth;
use App\Module;
use App\ProcessFolder;
use App\ProcessFile;
use Ilovepdf\CompressTask;
use Illuminate\Support\Str as Str;
use App\DownloadProcessDocument;
use Carbon\Carbon;

class ToolConstructionProcessController extends Controller
{
	private $module_id = 306;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = Module::find($this->module_id);
			return view('tools.procesos.index',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function upload_file(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'   => 'ERROR',
			'message' => 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->file('file'))
			{
				$extension     = strtolower($request->file->getClientOriginalExtension());

				$original_name = Str::slug(pathinfo($request->file->getClientOriginalName(), PATHINFO_FILENAME),'_');

				$files         = ProcessFile::where('folder_id',$request->folder)
					->where('real_name',$original_name)
					->count();

				if($files > 0)
				{
					$original_name .= '_'.($files + 1);
				}
				$name_for_file = $original_name;

				if($extension=='pdf')
				{
					try
					{
						$myTask = new CompressTask('project_public_3366528f2ee24af6a83e7cb142128e1c__nwaXf03e5ca1e49cb9f1d272dda7e327c6df','secret_key_09de0b6ac33ca88293b6dd69b35c8564_CZyihbc2f9c54892e685d558169cc933a4dfd');
						\Storage::put('/uncompressed_pdf/'.$name_for_file.'.pdf',\File::get($request->file));
						\Storage::makeDirectory('compressed_pdf');
						$file = $myTask->addFile(storage_path().'/app/uncompressed_pdf/'.$name_for_file.'.pdf');
						$myTask->setCompressionLevel('recommended');
						$myTask->execute();
						$myTask->setOutputFilename($name_for_file);
						$myTask->download(storage_path().'/app/compressed_pdf');
						\Storage::move('/compressed_pdf/'.$name_for_file.'.pdf','/procedimientos/'.$name_for_file);
						\Storage::delete(['/uncompressed_pdf/'.$name_for_file.'.pdf','/compressed_pdf/'.$name_for_file.'.pdf']);
						$size                           = \Storage::size('/procedimientos/'.$name_for_file);
						$compressedFile					= new ProcessFile;
						$compressedFile->real_name		= $original_name;
						$compressedFile->file_name		= $name_for_file;
						$compressedFile->file_extension	= $extension;
						$compressedFile->folder_id		= $request->folder;
						$compressedFile->user_id		= Auth::user()->id;
						$compressedFile->file_size		= $size;
						$compressedFile->save();
						$response['error']     = 'DONE';
						$response['name']      = $original_name;
						$response['extension'] = strtolower($extension);
						$response['message']   = '';
					}
					catch (\Ilovepdf\Exceptions\StartException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\AuthException $e)
					{
						$response['message'] = 'Ocurrió un problema, por favor intente nuevamente.';
					}
					catch (\Ilovepdf\Exceptions\UploadException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Ilovepdf\Exceptions\ProcessException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos.';
					}
					catch (\Exception $e)
					{
						$response['message_console'] = $e->getMessage();
					}
				}
				else
				{
					\Storage::put('/procedimientos/'.$original_name,\File::get($request->file));
					$size                           = \Storage::size('/procedimientos/'.$name_for_file);
					$compressedFile					= new ProcessFile;
					$compressedFile->real_name		= $original_name;
					$compressedFile->file_name		= $name_for_file;
					$compressedFile->file_extension	= $extension;
					$compressedFile->folder_id		= $request->folder;
					$compressedFile->user_id		= Auth::user()->id;
					$compressedFile->file_size		= $size;
					$compressedFile->save();
					$response['error']     = 'DONE';
					$response['name']      = $original_name;
					$response['extension'] = strtolower($extension);
					$response['message']   = '';
				}
			}
			return Response($response);
		}
	}

	public function download_files($node,$ids)
	{
		$ids   = explode(',',$ids);
		$files = ProcessFile::where('folder_id',$node)
			->whereIn('id',$ids)
			->get();
		if($files->count() > 0)
		{
			if($files->count() > 1)
			{
				$zip_file = '/tmp/'.Str::uuid().'.zip';
				$zip      = new \ZipArchive();
				if($zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true)
				{
					foreach($files as $f)
					{
						$zip->addFile(storage_path('/app/procedimientos/'.$f->file_name), $f->real_name.'.'.$f->file_extension);

						$new_download					= new DownloadProcessDocument();
						$new_download->file_name		= $f->file_name;
						$new_download->real_name		= $f->real_name;
						$new_download->file_extension	= $f->file_extension;
						$new_download->user_id			= Auth::user()->id;
						$new_download->save();

					}
					$zip->close();
					return response()->download($zip_file,'archivos.zip');
				}
			}
			else
			{
				$new_download					= new DownloadProcessDocument();
				$new_download->file_name		= $files->first()->file_name;
				$new_download->real_name		= $files->first()->real_name;
				$new_download->file_extension	= $files->first()->file_extension;
				$new_download->user_id			= Auth::user()->id;
				$new_download->save();

				return \Storage::download('/procedimientos/'.$files->first()->file_name,$files->first()->real_name.'.'.$files->first()->file_extension);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function delete_files(Request $request)
	{
		$files = ProcessFile::whereIn('id',$request->ids)->get();
		foreach($files as $file)
		{
			\Storage::delete('/procedimientos/'.$file->file_name);
			$file->delete();
		}
	}
	public function rename_file(Request $request)
	{
		$responseArr = [
			'error' => '0'
		];
		$file		= ProcessFile::where('id',$request->id)->firstOrFail();

		$file_name	= Str::slug($request->text,'_');

		$files 		= ProcessFile::where('folder_id',$file->folder_id)
					->where('real_name',$file_name)
					->where('id','!=',$file->id)
					->where('user_id',Auth::user()->id)
					->count();
		if($file_name == '')
		{
			$responseArr['error'] = '1';
			return $responseArr;
		}
		if($files > 0)
		{
			$file_name .= '_'.($files + 1);
		}
		$file->real_name = $file_name;
		$file->save();
		$responseArr['file']        = $file->real_name;
		$responseArr['last_modify'] = $file->updated_at->format('d-m-Y h:i:s');
		return $responseArr;
	}

	public function move_file(Request $request)
	{
		$files = ProcessFile::whereIn('id',$request->ids)->get();
		foreach($files as $file)
		{
			$files_in_folder = ProcessFile::where('folder_id',$request->id[0])
							->where('real_name',$file->real_name)
							->count();

			if($files_in_folder > 0)
			{
				$file->real_name .= '_'.($files_in_folder + 1);
			}
			$file->folder_id = $request->id[0];
			$file->save();
		}
		return 'DONE';
	}

	public function folder_get()
	{
		$folders = [];
		if(ProcessFolder::count() > 0)
		{
			$folders = ProcessFolder::selectRaw('id, text, IFNULL(parent,"#") as parent')->get()->toArray();
		}
		else
		{
			$folder          = new ProcessFolder;
			$folder->text    = 'Procesos';
			$folder->user_id = Auth::user()->id;
			$folder->save();
			foreach(ProcessFile::all() as $file)
			{
				$file->folder_id = $folder->id;
				$file->save();
			}
			$folders = ProcessFolder::selectRaw('id, text, IFNULL(parent,"#") as parent')->get()->toArray();
		}
		return response($folders);
	}

	public function folder_create(Request $request)
	{
		$folder          = new ProcessFolder;
		$folder->text    = $request->text;
		$folder->user_id = Auth::user()->id;
		$folder->parent  = $request->id;
		$folder->save();
		return $folder;
	}

	public function folder_rename(Request $request)
	{
		$folder_name  = Str::slug($request->text,'_');
		if($folder_name == '')
		{
			return abort(404);
		}
		$folder       = ProcessFolder::find($request->id);
		$folder->text = $folder_name;
		$folder->save();
		return ['id' => $folder->id, 'text' => $folder->text];
	}

	public function folder_move(Request $request)
	{
		$folder         = ProcessFolder::find($request->id);
		$folder->parent = $request->parent;
		$folder->save();
		return ['id' => $folder->id, 'text' => $folder->text];
	}

	public function folder_files(Request $request)
	{
		$files = ProcessFile::where('folder_id',$request->id)->get();
		return view('partials.files',['files' => $files]);
	}

	public function folder_delete(Request $request)
	{
		$folder		= ProcessFolder::find($request->id);
		$folders	= $folder->allFolders->pluck('id');
		$folders[]	= $folder->id;

		foreach(ProcessFile::whereIn('folder_id',$folders)->get() as $file)
		{
			\Storage::delete('/procedimientos/'.$file->file_name);
			$file->delete();
		}
		foreach($folder->allFolders as $f)
		{
			$f->delete();
		}
		$folder->delete();
		return 'deleted';
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',316)->count()>0)
		{
			$data     		= App\Module::find(316);
			$folio          = $request->folio != '' ? $request->folio: null;
			$status         = $request->status != '' ? $request->status: null;
			$name         	= $request->name != '' ? $request->name: null;
			$mindate        = $request->mindate != '' ? Carbon::createFromFormat('d-m-Y', $request->mindate) : null;
			$maxdate        = $request->maxdate != '' ? Carbon::createFromFormat('d-m-Y', $request->maxdate) : null;
			$enterpriseid   = $request->enterpriseid != '' ? $request->enterpriseid: null;
			$kind   		= $request->kind != '' ? $request->kind: null;
			
			$requests = App\RequestModel::where(function($q)
				{
					$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
						->orWhereHas('adjustment', function ($query)
						{
							$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
								->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
						})
						->orWhereHas('loanEnterprise', function ($query) {
							$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
								->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
						})
						->orWhereHas('purchaseEnterprise', function ($query) {
							$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
								->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
						})
						->orWhereHas('groups', function ($query) {
							$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
								->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
						})
						->orWhereHas('movementsEnterprise', function ($query) {
							$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
								->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
						})
						->orWhereNull('idEnterprise');
				})
				->where(function($q)
				{
					$q->whereIn('idDepartment',Auth::user()->inChargeDep(316)->pluck('departament_id'))
						->orWhereHas('adjustment', function ($query)
						{
							$query->whereIn('idDepartamentOrigin',Auth::user()->inChargeDep(316)->pluck('departament_id'))
								->orWhereIn('idDepartamentDestiny',Auth::user()->inChargeDep(316)->pluck('departament_id'));
						})
						->orWhereHas('purchaseEnterprise', function ($query) {
							$query->whereIn('idDepartamentOrigin',Auth::user()->inChargeDep(316)->pluck('departament_id'));
						})
						->orWhereHas('groups', function ($query) {
							$query->whereIn('idDepartamentOrigin',Auth::user()->inChargeDep(316)->pluck('departament_id'));
						})
						->orWhereHas('loanEnterprise')
						->orWhereHas('movementsEnterprise')
						->orWhereNull('idDepartment');
				})
				->where(function ($query) use ($folio, $status, $name, $mindate, $maxdate, $enterpriseid, $kind)
				{
					if($folio != "")
					{
						$query->where('folio',$folio);
					}
					if($status != "")
					{
						$query->where('status',$status);
					}
					if($name != "")
					{
						$query->whereHas('requestUser', function($q) use($name)
						{
							$q->where(DB::raw("CONCAT_WS(' ',users.name,users.last_name,users.scnd_last_name)"),'LIKE','%'.preg_replace("/\s+/", "%", $name).'%');
						});
					}
					if($mindate != "" && $maxdate != "")
					{
						$query->whereBetween('fDate',[$mindate->format('Y-m-d 00:00:00'), $maxdate->format('Y-m-d 23:59:59')]);
					}
					if ($enterpriseid != "") 
					{
						$query->where(function($queryE) use ($enterpriseid)
						{
							$queryE->where('idEnterprise',$enterpriseid)->orWhere('idEnterpriseR',$enterpriseid);
						});
					}
					if ($kind != "") 
					{
						$query->where('kind',$kind);
					}
				})
				->orderBy('fDate','DESC')
				->paginate(10);
			
			return view('tools.solicitudes_globales.busqueda',
				[
					'requests'     => $requests,
					'id'		   => $data['father'],
					'title'		   => $data['name'],
					'details'	   => $data['details'],
					'child_id' 	   => 271,
					'option_id'    => 316,
					'folio'        => $folio,
					'status'       => $status,
					'name'         => $name,
					'mindate'      => $request->mindate,
					'maxdate'      => $request->maxdate,
					'enterpriseid' => $enterpriseid,
					'kind' 		   => $kind,
				]);
		}
		else
		{
			return abort(404);
		}
	}

	public function show($id) 
	{	
		$flag = App\RequestModel::where('folio',$id)
			->where(function($q)
			{
				$q->whereIn('idEnterprise',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
					->orWhereHas('adjustment', function ($query)
					{
						$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
							->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
					})
					->orWhereHas('loanEnterprise', function ($query) {
						$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
							->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
					})
					->orWhereHas('purchaseEnterprise', function ($query) {
						$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
							->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
					})
					->orWhereHas('groups', function ($query) {
						$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
							->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
					})
					->orWhereHas('movementsEnterprise', function ($query) {
						$query->whereIn('idEnterpriseOrigin',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))
							->orWhereIn('idEnterpriseDestiny',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'));
					})
					->orWhereNull('idEnterprise');
			})
			->where(function($q) use ($id)
			{
				$q->whereIn('idDepartment',Auth::user()->inChargeDep(316)->pluck('departament_id'))
					->orWhereHas('adjustment', function ($query)
					{
						$query->whereIn('idDepartamentOrigin',Auth::user()->inChargeDep(316)->pluck('departament_id'))
							->orWhereIn('idDepartamentDestiny',Auth::user()->inChargeDep(316)->pluck('departament_id'));
					})
					->orWhereHas('purchaseEnterprise', function ($query) {
						$query->whereIn('idDepartamentOrigin',Auth::user()->inChargeDep(316)->pluck('departament_id'));
					})
					->orWhereHas('groups', function ($query) {
						$query->whereIn('idDepartamentOrigin',Auth::user()->inChargeDep(316)->pluck('departament_id'));
					})
					->orWhereHas('loanEnterprise', function ($query) use ($id) {
						$query->where('idFolio',$id);
					})
					->orWhereHas('movementsEnterprise', function ($query) use ($id) {
						$query->where('idFolio',$id);
					})
					->orWhereNull('idDepartment');
			})
		 	->get();

		if(Auth::user()->module->where('id',316)->count()>0 && count($flag)>0)
		{
			$data     			= App\Module::find(316);
			$request			= App\RequestModel::find($id);
			$areas				= App\Area::where('status','ACTIVE')->get();
			$enterprises    	= App\Enterprise::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeEnt(316)->pluck('enterprise_id'))->get();
			$departments    	= App\Department::where('status','ACTIVE')->whereIn('id',Auth::user()->inChargeDep(316)->pluck('departament_id'))->get();
			$responsibilities	= App\Responsibility::all();
			$minSalary			= App\Parameter::where('parameter_name','MIN_SALARY')->get();
			$maxSalary			= App\Parameter::where('parameter_name','MAX_SALARY')->get();

			if ($request != "") 
			{
				return view('tools.solicitudes_globales.seguimiento',
					[
						'id' 		  	   => $data['father'],
						'title'		  	   => $data['name'],
						'details' 	  	   => $data['details'],
						'child_id' 	  	   => 271,
						'option_id'   	   => 316,
						'request' 	  	   => $request,
						'requests' 	  	   => $request,
						'areas'		  	   => $areas,
						'enterprises' 	   => $enterprises,
						'departments' 	   => $departments,
						'responsibilities' => $responsibilities,
						'minSalary'		   => $minSalary,
						'maxSalary'		   => $maxSalary,
					]);
			}
			else
			{
				return abort(404);
			}
			
		}
		else
		{
			return abort(404);
		}
	}
}
