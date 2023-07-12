<?php namespace Dilexus\Octobase\Classes\Api\Lib;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Octobase {

    function enableCrud($class, $listMiddleWhere = [], $viewMiddleWhere = [], $createMiddleWhere = [], $updateMiddleWhere = [], $deleteMiddleWhere = []){
        $model = explode("\\", $class);
        $model = end($model);
        $controller = Str::plural($model);

        Route::prefix(strtolower($controller))->group(function () use ($class, $listMiddleWhere, $viewMiddleWhere, $createMiddleWhere, $updateMiddleWhere, $deleteMiddleWhere) {

            Route::get('', function (Request $request) use ($class) {
                try{

                    $with = $request->input('with');
                    $select = $request->input('select');
                    $where = $request->input('where');
                    $order = $request->input('order');
                    $page = $request->input('page');
                    $perPage = $request->input('perPage') ?? 10;

                    if($page){
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
                        return response()->json(['data' => $records->items(), 'per_page' => $records->perPage(), 'total' => $records->total(), 'page' => $records->currentPage()]);
                    }else{
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
                        return response()->json(['data' => $records]);
                    }
                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($listMiddleWhere);

            Route::get('{id}', function (Request $request, $id) use ($class)  {
                try{
                    $with = $request->input('with');
                    $select = $request->input('select');
                    $records = $class::query();
                    if($select){
                        $records->selectRaw($select);
                    }
                    if($with){
                        $records->with(explode(',', $with));
                    }
                    $record = $records->find($id);
                    if($record){
                        return response()->json($record);
                    }else{
                        return response()->json(['error' => 'Record not found'], 404);
                    }
                }catch(\Exception $e){
                    return response()->json(['error' => $e->getMessage()], 400);
                }
            })->middleware($viewMiddleWhere);

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
            })->middleware($createMiddleWhere);;

            Route::put('{id}', function (Request $request, $id) use ($class)  {
                try{
                    $inputs = $request->all();
                    $record = $class::find($id);
                    if($record){
                    if($id != $request->input('id')){
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
            })->middleware($updateMiddleWhere);;

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
            })->middleware($deleteMiddleWhere);;

        });
    }
}
