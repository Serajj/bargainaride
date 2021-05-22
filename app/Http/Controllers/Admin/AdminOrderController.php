<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyOrder;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;

class AdminOrderController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('order_access'), 403);

        $orders = Booking::all();

        foreach ($orders as $value) {
            $nameRider=User::select('name')->where('id',$value->uid)->first();
            if($nameRider){
                $value->riderName=$nameRider;
            }
            $nameRider=User::select('name')->where('id',$value->did)->first();
            if($nameRider){
                $value->driverName=$nameRider;
            }
        }

        return view('admin.orderManagement.index', compact('orders'));
    }



   

   

    public function show(Booking $user)
    {
        abort_unless(\Gate::allows('order_show'), 403);

        //$user->load('roles');

        return view('admin.orderManagement.show', compact('user'));
    }

    public function destroy(Request $req)
    {
        abort_unless(\Gate::allows('user_delete'), 403);
         $booking=Booking::where('id',$req->id)->first();
        $booking->delete();
         // print_r($booking);
        return back();
    }

    public function massDestroy(MassDestroyOrder $request)
    {
        Order::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }


    public function testFunction()
    {
        return view('test');
    }
}
