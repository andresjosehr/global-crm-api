<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $user_id = $request->user()->id;




        $notifications = Notification::where('user_id', $user_id)

            ->when($request->read, function ($query) use ($request) {
                return $query->where('read', $request->read);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ApiResponseController::response('Exito', 200, $notifications);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($notification)
    {

        $not = new Notification();

        // Get fillable fields
        $fillable = (new Notification())->getFillable();
        $noti = array_filter($notification, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);

        $not->fill($noti);
        $not->save();

        event(new \App\Events\SendNotificationEvent($notification['user_id'], $notification));

        return ApiResponseController::response('Exito', 200, $not);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->update(['read' => true]);

        return ApiResponseController::response('Exito', 200, 'Notificación actualizada');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
