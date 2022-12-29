<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App;
use Ilovepdf\CompressTask;
use Illuminate\Support\Str as Str;

class ToolsCompressController extends Controller
{
	private $module_id = 272;

	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			return view('tools.compress.index',
				[
					'id'        => $data['father'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return abort(404);
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
				$files         = App\CompressedFile::where('folder_id',$request->folder)
					->where('real_name',$original_name)
					->where('user_id',Auth::user()->id)
					->count();
				if($files > 0)
				{
					$original_name .= '_'.($files + 1);
				}
				$name_for_file = Auth::user()->id.'-'.Str::uuid();
				if($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg')
				{
					if($extension == 'jpeg')
					{
						$extension = 'jpg';
					}
					try
					{
						$sourceData = file_get_contents($request->file);
						$resultData = \Tinify\fromBuffer($sourceData)->toBuffer();
						\Storage::put('/'.Auth::user()->id.'/file/'.$name_for_file,$resultData);
						$size                           = \Storage::size('/'.Auth::user()->id.'/file/'.$name_for_file);
						$compressedFile                 = new App\CompressedFile;
						$compressedFile->real_name      = $original_name;
						$compressedFile->file_name      = $name_for_file;
						$compressedFile->file_extension = $extension;
						$compressedFile->folder_id      = $request->folder;
						$compressedFile->user_id        = Auth::user()->id;
						$compressedFile->file_size      = $size;
						$compressedFile->save();
						$response['error']     = 'DONE';
						$response['name']      = $original_name;
						$response['extension'] = strtolower($extension);
						$response['message']   = '';
					}
					catch(\Tinify\AccountException $e)
					{
						$response['message'] = $e->getMessage();
					}
					catch(\Tinify\ClientException $e)
					{
						$response['message'] = 'Por favor, verifique su archivo.';
					}
					catch(\Tinify\ServerException $e)
					{
						$response['message'] = 'Ocurrió un error al momento de comprimir su archivo. Por favor, intente después de unos minutos. Si ve este mensaje por un periodo de tiempo más larga, por favor contacte a soporte con el código: SAPIT2.';
					}
					catch(\Tinify\ConnectionException $e)
					{
						$response['message'] = 'Ocurrió un problema de conexión, por favor verifique su red e intente nuevamente.';
					}
					catch(Exception $e)
					{
						
					}
				}
				elseif($extension=='pdf')
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
						\Storage::move('/compressed_pdf/'.$name_for_file.'.pdf','/'.Auth::user()->id.'/file/'.$name_for_file);
						\Storage::delete(['/uncompressed_pdf/'.$name_for_file.'.pdf','/compressed_pdf/'.$name_for_file.'.pdf']);
						$size                           = \Storage::size('/'.Auth::user()->id.'/file/'.$name_for_file);
						$compressedFile                 = new App\CompressedFile;
						$compressedFile->real_name      = $original_name;
						$compressedFile->file_name      = $name_for_file;
						$compressedFile->file_extension = $extension;
						$compressedFile->folder_id      = $request->folder;
						$compressedFile->user_id        = Auth::user()->id;
						$compressedFile->file_size      = $size;
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
			}
			return Response($response);
		}
	}

	public function download_files($node,$ids)
	{
		$ids   = explode(',',$ids);
		$files = App\CompressedFile::where('folder_id',$node)
			->whereIn('id',$ids)
			->where('user_id',Auth::user()->id)
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
						$zip->addFile(storage_path('/app/'.Auth::user()->id.'/file/'.$f->file_name), $f->real_name.'.'.$f->file_extension);
					}
					$zip->close();
					return response()->download($zip_file,'archivos.zip');
				}
			}
			else
			{
				return \Storage::download('/'.Auth::user()->id.'/file/'.$files->first()->file_name,$files->first()->real_name.'.'.$files->first()->file_extension);
			}
		}
		else
		{
			return abort(404);
		}
	}

	public function delete_files(Request $request)
	{
		$files = App\CompressedFile::whereIn('id',$request->ids)
			->where('user_id',Auth::user()->id)
			->get();
		foreach($files as $file)
		{
			\Storage::delete('/'.Auth::user()->id.'/file/'.$file->file_name);
			$file->delete();
		}
	}
	public function rename_file(Request $request)
	{
		$responseArr = [
			'error' => '0'
		];
		$file = App\CompressedFile::where('id',$request->id)
			->where('user_id',Auth::user()->id)
			->firstOrFail();
		$file_name  = Str::slug($request->text,'_');
		$files = App\CompressedFile::where('folder_id',$file->folder_id)
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
		$files = App\CompressedFile::whereIn('id',$request->ids)
			->where('user_id',Auth::user()->id)
			->get();
		foreach($files as $file)
		{
			$files_in_folder = App\CompressedFile::where('folder_id',$request->id[0])
				->where('real_name',$file->real_name)
				->where('user_id',Auth::user()->id)
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
		if(App\Folder::where('user_id',Auth::user()->id)->count() > 0)
		{
			$folders = App\Folder::selectRaw('id, text, IFNULL(parent,"#") as parent')->where('user_id',Auth::user()->id)->get()->toArray();
		}
		else
		{
			$folder          = new App\Folder;
			$folder->text    = Str::slug(Auth::user()->name,'_');
			$folder->user_id = Auth::user()->id;
			$folder->save();
			foreach(App\CompressedFile::where('user_id',Auth::user()->id)->get() as $file)
			{
				$file->folder_id = $folder->id;
				$file->save();
			}
			$folders = App\Folder::selectRaw('id, text, IFNULL(parent,"#") as parent')->where('user_id',Auth::user()->id)->get()->toArray();
		}
		return response($folders);
	}

	public function folder_create(Request $request)
	{
		$folder          = new App\Folder;
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
		$folder       = App\Folder::find($request->id);
		$folder->text = $folder_name;
		$folder->save();
		return ['id' => $folder->id, 'text' => $folder->text];
	}

	public function folder_move(Request $request)
	{
		$folder         = App\Folder::find($request->id);
		$folder->parent = $request->parent;
		$folder->save();
		return ['id' => $folder->id, 'text' => $folder->text];
	}

	public function folder_files(Request $request)
	{
		$files = App\CompressedFile::where('user_id',Auth::user()->id)->where('folder_id',$request->id)->get();
		return view('partials.files',['files' => $files]);
	}

	public function folder_delete(Request $request)
	{
		$folder   = App\Folder::find($request->id);
		$folders  = $folder->allFolders->pluck('id');
		$folders[] = $folder->id;
		foreach(App\CompressedFile::where('user_id',Auth::user()->id)->whereIn('folder_id',$folders)->get() as $file)
		{
			\Storage::delete('/'.Auth::user()->id.'/file/'.$file->file_name);
			$file->delete();
		}
		foreach($folder->allFolders as $f)
		{
			$f->delete();
		}
		$folder->delete();
		return 'deleted';
	}
}
