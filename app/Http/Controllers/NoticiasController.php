<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\http\Requests\GeneralRequest;
use App;
use Lang;
use Alert;
use PostTooLargeException;
use Auth;
use Throwable;
use Carbon\Carbon;
use App\Functions\Files;

class NoticiasController extends Controller
{
	private $module_id = 80;
	public function index()
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$fecha		= date('Y-m-d');
			$nuevafecha	= date('Y-m-d',strtotime('-7 day',strtotime($fecha)));
			$data		= App\Module::find($this->module_id);
			$news		= App\News::whereBetween('date',[''.$nuevafecha.' '.date('00:00:00').'',''.$fecha.' '.date('23:59:59').''])->orderBy('date','desc')->orderBy('idnews','desc')->paginate(10);

			return view('noticias.index',
				[
					'id'        => $data['id'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'news'      => $news,
					'child_id' => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function history(Request $request)
	{
		if(Auth::user()->module->where('id',104)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			$mindate    = $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
		    $maxdate   	= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$name  		= $request->name;
			$description = $request->description;
	  
			if ($request->mindate != "") 
      		{
      			$mindate 	= date('Y-m-d',strtotime($request->mindate));
		    	$maxdate 	= date('Y-m-d',strtotime($request->maxdate));
      		}

			$news   = App\News::where(function($query) use ($name,$mindate,$maxdate,$description)
						{
							if ($name != "") 
							{
								$query->where('title','LIKE','%'.$name.'%');
							}
							if ($description != "") 
							{
								$query->where('details','LIKE','%'.$description.'%');
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
					->orderBy('date','desc')
					->orderBy('idnews','desc')
					->paginate(10);

			return view('noticias.historico',
				[
					'id'          => $data['id'],
					'title'       => $data['name'],
					'details'     => $data['details'],
					'option_id'   => 104,
					'news'        => $news,
					'name'        => $name,
					'description' => $description,
					'mindate'     => $mindate,
					'maxdate'     => $maxdate,
					'child_id'    => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function create()
	{
		if(Auth::user()->module->where('id',81)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			return view('noticias.alta',
				[
					'id'        => $data['id'],
					'title'     => $data['name'],
					'details'   => $data['details'],
					'option_id' => 81,
					'child_id'  => $this->module_id
				]);
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function store(Request $request)
	{
		if(Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data			= App\Module::find($this->module_id);
			$new			= new App\News();
			$new->date		= Carbon::now();
			$new->title		= $request->title;
			$new->details	= $request->details;

			if ($request->realPath != "") 
			{	
				
				$new->path		= $this->setPathAttr($request->realPath);
				$new->save();
				
				$new_file_name	= Files::rename($new->path,$new->idnews);
				$new->path		= $new_file_name;
			}
			else
			{
				$new->path 	= null;
			}

			$new->save();

			$alert = "swal('','".Lang::get("messages.record_created")."', 'success')";
			return redirect('news')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function search(Request $request)
	{
		if(Auth::user()->module->where('id',82)->count()>0)
		{
			$data   	= App\Module::find($this->module_id);
			$mindate    = $request->mindate!='' ? date('Y-m-d',strtotime($request->mindate)) : null;
		    $maxdate   	= $request->maxdate!='' ? date('Y-m-d',strtotime($request->maxdate)) : null;
			$name  		= $request->name;
			$description = $request->description;
	  
			if ($request->mindate != "") 
      		{
      			$mindate 	= date('Y-m-d',strtotime($request->mindate));
		    	$maxdate 	= date('Y-m-d',strtotime($request->maxdate));
      		}

			$news   = App\News::where(function($query) use ($name,$mindate,$maxdate,$description)
						{
							if ($name != "") 
							{
								$query->where('title','LIKE','%'.$name.'%');
							}
							if ($description != "") 
							{
								$query->where('details','LIKE','%'.$description.'%');
							}
							if($mindate != "" && $maxdate != "")
							{
								$query->whereBetween('date',[''.$mindate.' '.date('00:00:00').'',''.$maxdate.' '.date('23:59:59').'']);
							}
						})
					->orderBy('date','desc')
					->orderBy('idnews','desc')
					->paginate(10);
			 return view('noticias.busqueda',
			 	[
					'id'          => $data['id'],
					'title'       => $data['name'],
					'details'     => $data['details'],
					'option_id'   => 82,
					'news'        => $news,
					'name'        => $name,
					'description' => $description,
					'mindate'     => $mindate,
					'maxdate'     => $maxdate,
					'child_id'    => $this->module_id
			 	]);
		}
		else
		{
			return redirect('/');
		}
	}

	public function getNews(Request $request)
	{
		if($request->ajax())
		{
			$output         = "";
			$header         = "";
			$footer         = "";
			$news      = App\News::where('title','LIKE','%'.$request->search.'%')
								->get();
			$countUsers     = count($news);
				if ($countUsers >= 1) 
				{
					$header = "<table id='table' class='table table-hover'><thead><tr><th>ID</th><th>Nombre</th><th>Acci&oacute;n</th></tr></thead><tbody>";
					$footer = "</tbody></table>";
					foreach ($news as $new) {
						$output.="<tr>".
								 "<td>".$new->idnews."</td>".
								 "<td>".$new->title."</td>".
								 "<td><a title='Editar Noticia' href="."'".url::route('news.edit',$new->idnews)."'"."class='btn btn-green'><span class='icon-pencil'></span></a></td>".
								 "</tr>";
					} 
					return Response($header.$output.$footer);
				}
				else
				{
					$notfound = '<div id="not-found" style="display:block;">RESULTADO NO ENCONTRADO</div>';
					return Response($notfound);
				}
		}
	}
	
	public function show($id)
	{
		if(Auth::user()->module->where('id',83)->count()>0)
		{
			$data   = App\Module::find($this->module_id);
			$new   = App\News::find($id);
			if ($new != "") 
			{
				return view('noticias.ver',
					[
						'id'       => $data['id'],
						'title'    => $data['name'],
						'details'  => $data['details'],
						'new'      => $new,
						'child_id' => $this->module_id
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}
	
	public function edit($id)
	{
		if(Auth::user()->module->where('id',82)->count()>0)
		{
			$data = App\Module::find($this->module_id);
			$new = App\News::find($id);
			if ($new != "") 
			{
				return view('noticias.cambio',
					[
						'id'        => $data['id'],
						'title'     => $data['name'],
						'details'   => $data['details'],
						'option_id' => 82,
						'new'       => $new,
						'child_id'  => $this->module_id
					]);
			}
			else
			{
				return redirect('/error');
			}
		}
		else
		{
			return redirect('/');
		}
	}

	public function uploader(Request $request)
	{
		\Tinify\setKey("DDPii23RhemZFX8YXES5OVhEP7UmdXMt");
		$response = array(
			'error'		=> 'ERROR',
			'message'	=> 'Error, por favor intente nuevamente'
		);
		if ($request->ajax()) 
		{
			if($request->realPath!='')
			{
				\Storage::disk('public')->delete('/images/news/'.$request->realPath);				
			}
			if($request->file('path'))
			{
				$extention				= strtolower($request->path->getClientOriginalExtension());
				$nameWithoutExtention	= 'AdG'.round(microtime(true) * 1000).'_newsDoc.';
				$name					= $nameWithoutExtention.$extention;
				$destinity				= '/images/news/'.$name;
				\Storage::disk('public')->put($destinity,file_get_contents($request->path));
				$response['error']		= 'DONE';
				$response['path']		= $name;
				$response['message']	= '';
				$response['extention']	= strtolower($extention);
			}
			return Response($response);
		}
	}

	public function update(Request $request, $id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$data           = App\Module::find($this->module_id);
			$news           = App\News::orderBy('date','desc')->orderBy('idnews','desc')->paginate(10);
			$new            = App\News::find($id);
			$new->title     = $request->title;
			$new->details   = $request->details;
			if ($request->removeImage == 1) 
			{
				if ($request->realPath != "") 
				{
					$new->path		= $this->setPathAttr($request->realPath);
					$new_file_name	= Files::rename($new->path,$new->idnews);
					$new->path		= $new_file_name;
				}
				else
				{
					$new->path 	= null;
				}
			}
			else
			{
				if ($request->realPath != "") 
				{
					$new->path		= $this->setPathAttr($request->realPath);
					$new_file_name	= Files::rename($new->path,$new->idnews);
					$new->path		= $new_file_name;
				}
			}
			$new->save();
			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			return redirect('news')->with('alert',$alert);
		}
		else
		{
			return redirect('/');
		}
	}

	public function delete($id)
	{
		if (Auth::user()->module->where('id',$this->module_id)->count()>0)
		{
			$new = App\News::find($id);
			if ($new->path != "") 
			{
				\Storage::disk('public')->delete('images/news/'.$new->path);
			}
			$new->delete();
			$alert = "swal('','".Lang::get("messages.record_updated")."', 'success')";
			return redirect('news/search')->with('alert',$alert);
		}
	}

	public function setPathAttr($path)
	{
		$new_path = '';
		if(is_string($path) || is_null($path))
		{
			$new_path = $path;
		}
		else
		{
			$new_path	= 'AdG'.round(microtime(true) * 1000).'_news.'.$path->getClientOriginalExtension();
			$name		= '/images/news/AdG'.round(microtime(true) * 1000).'_news.'.$path->getClientOriginalExtension();
			\Storage::disk('public')->put($name,\File::get($path));
		}
		return $new_path;
	}

}
