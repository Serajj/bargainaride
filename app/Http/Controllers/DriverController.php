<?php

namespace App\Http\Controllers;

use App\Models\DriverDetail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;


class DriverController extends Controller
{
    //

    public function addDriver(Request $request){


        $validator = Validator::make($request->all(),[
            'vehicle_num'=>'required',
            'dl_num'=>'required|min:10|max:20',
            'car_type'=>'required',
            'car_model'=>'required',
            'dl_image'=>'required|file',
            'rc_image'=>'required|file'
           
        ]);

        if($validator->fails()){
            return response()->json(['success'=>0 ,'errors'=>$validator->errors()],202);
        }
 
            $user=User::where('id',Auth::user()->id)->first();

            if(DriverDetail::where('uid',Auth::user()->id)->first()){
                return response()->json(['info'=>"Driver Details Already exist !!"],202);
            }
            
            $driver= new DriverDetail();

            $driver->uid=$user->id;
            $driver->vehicle_no=$request->vehicle_num;
            
      if ($request->hasFile('dl_image')) {
        $image = $request->file('dl_image');
        $name = 'dl'.time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/driverdata/'.$user->id);
        $image->move($destinationPath, $name);
       
        $driver->dl_image='https://bargainaride.com/public/driverdata/'.$user->id.'/'.$name;
        
    }
    
    if ($request->hasFile('rc_image')) {
        $image = $request->file('rc_image');
        $name = 'rc'.time().'.'.$image->getClientOriginalExtension();
        $destinationPath = public_path('/driverdata/'.$user->id);
        $image->move($destinationPath, $name);
       
        $driver->rc_image='https://bargainaride.com/public/driverdata/'.$user->id.'/'.$name;
        
    }
            
            
            
            
            
            $driver->dl_no=$request->dl_num;
            $driver->car_type=$request->car_type;
            $driver->car_model=$request->car_model;


            if($driver->save()){
               $user->profile_complete="1";
               $user->update();
            }

              

        $response = [
                    'success'=>1,
                    'result' => true,
                    'data'=>[$driver],
                    'ResponseMsg' => "Saved Successfully!"
                ];
        
             return response()->Json($response, 201);
    }


    public function getAvailableDrivers(Request $request)
    {
        $validator = Validator::make($request->all(),[
            
            'order_id'=>'required'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $drivers=Order::select('oid','did','time_to_pickup','driver_price','distance_from_user')->where('oid',$request->order_id)->get();

        if($drivers){
            
            foreach ($drivers as $value) {
                $data= DB::table('users')->select('users.name','users.profile_pic','driver_details.car_model','driver_details.car_type')

                ->join('driver_details','users.id','=','driver_details.uid')
        
                ->where('users.id',$value->did)
        
                ->first();
                $value->driver_name=$data->name;
                $value->car_type=$data->car_type;
                $value->car_model=$data->car_model;
                $value->profile_pic=$data->profile_pic;
            }

            return response()->json(['success'=>1,'message'=>"Drivers found",'data'=>$drivers]);
        }

        return response()->json(['success'=>0,'message'=>"Please wait..",'data'=>[]]);
        
    }
    
    
    
    
    
    public function update_driver(Request $request){


        $validator = Validator::make($request->all(),[
            'vehicle_num'=>'required',
            'dl_num'=>'required|min:10|max:20',
            'car_type'=>'required',
            'car_model'=>'required'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }
 
            $user=User::where('id',Auth::user()->id)->first();

            
            
            $driver= DriverDetail::where('uid',Auth::user()->id)->first();

            $driver->vehicle_no=$request->vehicle_num;
            $driver->dl_no=$request->dl_num;
            $driver->car_type=$request->car_type;
            $driver->car_model=$request->car_model;


            if($driver->update()){
               $user->profile_complete="1";
               $user->update();
            }

              

        $response = [
                    'result' => true,
                    'data'=>[$driver],
                    'ResponseMsg' => "Updated Successfully!"
                ];
        
             return response()->Json($response, 201);
    }
    
    
    
}
