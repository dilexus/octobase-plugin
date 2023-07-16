<?php namespace Dilexus\Octobase\Classes\Api\Lib;


use Dilexus\Octobase\Classes\Api\Middleware\OctobaseAuthAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Octobase {

    function enableCrud($class,
        $listM = [OctobaseAuthAdmin::class],
        $viewM = [OctobaseAuthAdmin::class],
        $createM = [OctobaseAuthAdmin::class],
        $updateM = [OctobaseAuthAdmin::class],
        $deleteM = [OctobaseAuthAdmin::class]) {

        $model = explode("\\", $class);
        $model = end($model);
        $controller = Str::plural($model);

        Route::prefix(strtolower($controller))->group(function () use ($class, $listM, $viewM, $createM, $updateM, $deleteM) {

            Route::get('', function (Request $request) use ($class) {
                try{
                    $with = $request->input('with');
                    $select = $request->input('select');
                    $where = $request->input('where');
                    $order = $request->input('order');
                    $page = $request->input('page');
                    $perPage = $request->input('perPage') ?? 10;
                    $locale = $request->input('locale');

                    if($page) {
                        $records = $class::query();
                        if($select){
                            $records->selectRaw($select);
                        }
                        if($with){
                            $records->with(explode(',', $with));
                        }
                        if($where){
                            $records->whereRaw($where);
                        }
                         if($order){
                             $records->orderByRaw($order);
                        }
                        $records = $records->paginate($perPage, ['*'], 'page', $page);

                        if($locale){
                            foreach ($records as $record) {
                                $record->translateContext($locale);
                            }
                        }

                        return response()->json(['data' => $records->items(), 'per_page' => $records->perPage(), 'total' => $records->total(), 'page' => $records->currentPage()]);
                    } else {
                        $records = $class::query();
                        if($select){
                            $records->selectRaw($select);
                        }
                        if($with){
                            $records->with(explode(',', $with));
                        }
                        if($where){
                            $records->whereRaw($where);
                        }
                         if($order){
                             $records->orderByRaw($order);
                        }
                        $records = $records->get();

                        if($locale){
                            foreach ($records as $record) {
                                $record->translateContext($locale);
                            }
                        }
                        return response()->json(['data' => $records]);
                    }
                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($listM);

            Route::get('{id}', function (Request $request, $id) use ($class)  {
                try{
                    $with = $request->input('with');
                    $select = $request->input('select');
                    $locale = $request->input('locale');

                    $records = $class::query();
                    if($select){
                        $records->selectRaw($select);
                    }
                    if($with){
                        $records->with(explode(',', $with));
                    }
                    $record = $records->find($id);

                    if($locale){
                       $record =  $record->lang($locale);
                    }

                    if($record){
                        return response()->json($record);
                    }else{
                        return response()->json(['error' => 'Record not found'], 404);
                    }
                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($viewM);

            Route::post('', function (Request $request) use ($class)  {
                try{
                    $inputs = $request->all();
                    $record = new $class;
                    foreach ($inputs as $key => $value) {
                        $record->fill([$key => $value]);
                    }
                    $record->save();
                    $record->refresh();
                    return response()->json($record, 201);
                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($createM);;

            Route::put('{id}', function (Request $request, $id) use ($class)  {
                try{
                    $inputs = $request->all();

                    $record = $class::find($id);
                    if($record){
                    if($request->input('id') && $id != $request->input('id')){
                        return response()->json(['error' => 'Ids are not matching'], 400);
                    }
                    $update = [];
                    foreach ($inputs as $key => $value) {
                        $update[$key] = $value;
                    }
                    $record->update($update);
                    $record->refresh();
                    return response()->json($record);
                    }else{
                        return response()->json(['error' => 'Record not found'], 404);
                    }

                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($updateM);

            Route::delete('{id}', function ($id) use ($class)  {
                try{
                    $record = $class::find($id);
                    if($record){
                        $record->delete();
                    }else{
                        return response()->json(['error' => 'Record not found'], 404);
                    }
                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($deleteM);

            Route::post('{id}/files', function (Request $request, $id) use ($class) {
                try{
                    $inputs = $request->allFiles();
                    $keepFiles = filter_var( $request->input('keep'), FILTER_VALIDATE_BOOLEAN) ?? true;
                    $record = $class::with(array_key_first($inputs))->find($id);
                    if($record){
                        if($request->input('id') && $id != $request->input('id')){
                            return response()->json(['error' => 'Ids are not matching'], 400);
                        }
                        foreach ($inputs as $key => $value) {
                            if(is_array($value)){
                                if (!$keepFiles && $record->$key) {
                                    foreach ($record->$key as $fileToDelete) {
                                        $fileToDelete->delete();
                                    }
                                }
                                foreach ($value as $fileToUpload) {
                                    $file = new \System\Models\File;
                                    $file->data = $fileToUpload;
                                    $file->is_public = true;
                                    $file->save();
                                    $record->$key()->add($file);
                                }
                            }else{
                                if (!$keepFiles && $record->$key) {
                                    $record->$key->delete();
                                }
                                $file = new \System\Models\File;
                                $file->data = $value;
                                $file->is_public = true;
                                $file->save();
                                $record->$key()->add($file);
                            }

                    }
                    $record->refresh();
                    return response()->json($record);
                    }else{
                        return response()->json(['error' => 'Record not found'], 404);
                    }

                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($updateM);


            Route::delete('{id}/files', function (Request $request, $id) use ($class) {
                try{
                    $all = $request->input('all') ?? false;
                    $file = $request->input('file');
                    if(!$file){
                        return response()->json(['error' => 'File name is required'], 400);
                    }
                    $record = $class::with($file)->find($id);
                    if($record){
                    if($request->input('id') && $id != $request->input('id')){
                        return response()->json(['error' => 'Ids are not matching'], 400);
                    }
                        if($all){
                                if ($record->$file) {
                                    foreach ($record->$file as $fileToDelete) {
                                        $fileToDelete->delete();
                                    }
                                }
                        }else{
                            if ($record->$file) {
                                $record->$file->delete();
                            }
                        }
                    $record->refresh();
                    return response()->json($record);
                    }else{
                        return response()->json(['error' => 'Record not found'], 404);
                    }

                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($deleteM);

        });
    }
}
