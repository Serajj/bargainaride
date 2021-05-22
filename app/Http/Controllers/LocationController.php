<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Validator;

use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    //

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(),[

            'longi'=>'required|min:1|max:200',
            'latti'=>'required|min:1|max:200'
            
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $user=Auth::user();

        $locationExist=Location::where('uid',$user->id)->first();
        if($locationExist){
            $locationExist->lattitude=$request->latti;
            $locationExist->longitude=$request->longi;
            $locationExist->update();

            $response = [
                'result' => true,
                'data'=>[$locationExist],
                'ResponseMsg' => "Updated Successfully!"
            ];
        }else{
            $newLocation= new Location();
            $newLocation->uid=$user->id;
            $newLocation->lattitude=$request->latti;
            $newLocation->longitude=$request->longi;
            $newLocation->save();

            $response = [
                'result' => true,
                'data'=>[$newLocation],
                'ResponseMsg' => "Updated Successfully!"
            ];
        }

       

     return response()->Json($response, 201);
    }


    public function getLocation(Request $request)
    {
        $validator = Validator::make($request->all(),[
            
            'uid'=>'required'

        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $locationExist=Location::where('uid',$request->uid)->first();
        
        if($request->order_id){
            
            $order=Booking::where('id',$request->order_id)->first();
            
             $response = [
            'result' => true,
            'order_status'=>$order->order_status,
            'lattitude'=>$locationExist->lattitude,
            'longitude'=>$locationExist->longitude,
            'ResponseMsg' => "Fetched Successfully!"
        ];
            
        }else{
            
             $response = [
            'result' => true,
            'lattitude'=>$locationExist->lattitude,
            'longitude'=>$locationExist->longitude,
            'ResponseMsg' => "Fetched Successfully!"
        ];
            
        }




       

        return response()->Json($response, 201);
    }





   public function GetDrivingDistance(Request $request)
{

    $validator = Validator::make($request->all(),[

        'user_lattitude'=>'required',
        'user_longitude'=>'required',
        'driver_lattitude'=>'required',
        'driver_longitude'=>'required'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors(),202);
    }

    $lat1=$request->user_lattitude;
    $long1=$request->user_longitude;
    $lat2=$request->driver_lattitude;
    $long2=$request->driver_longitude;


    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=in-EN&key=AIzaSyDX4jPMPgs6OegqifuWiKNeocIVLIM6iLs";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response, true);
    $num_dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
    $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
    $time = $response_a['rows'][0]['elements'][0]['duration']['text'];

   return array('distance' => $dist, 'time' => $time, 'distance_num' => $num_dist);

   //return $response_a;
}

public function disByCoodinate(Request $request)
{
    $validator = Validator::make($request->all(),[

        'user_lattitude'=>'required',
        'user_longitude'=>'required'
        // 'driver_lattitude'=>'required',
        // 'driver_longitude'=>'required'
    ]);

    if($validator->fails()){
        return response()->json($validator->errors(),202);
    }

    $latitudeFrom=$request->user_lattitude;
    $longitudeFrom=$request->user_longitude;
   
    


        $users= DB::table('users')->select('users.id','users.name','locations.lattitude','locations.longitude','driver_details.car_model','driver_details.car_type')

        ->join('locations','users.id','=','locations.uid')
        ->join('driver_details','users.id','=','driver_details.uid')

        ->where('users.on_ride','0')

        ->get();




    foreach($users as $user){

       

        $latitudeTo=$user->lattitude;
        $longitudeTo=$user->longitude;

    //     $theta = $longitudeFrom - $longitudeTo;
    //     $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
    //     $dist = acos($dist);
    //     $dist = rad2deg($dist);
    //     $miles = $dist * 60 * 1.1515;
    //    // $distance = ($miles * 1.609344);
       $lat1=$latitudeTo;
       $lon1=$longitudeTo;

       $lat2=$latitudeFrom;
       $lon2=$longitudeFrom;
       $rad = M_PI / 180;

       //calculating the distance between users and driver
       $distance=acos(sin($lat2*$rad) * sin($lat1*$rad) + cos($lat2*$rad) * cos($lat1*$rad) * cos($lon2*$rad - $lon1*$rad)) * 6371;// Kilometers

        $user->distance_from_user=$distance;
   }

  
   

    // filtering the list of drivers within area of 20 km from user location

     $users=$users->where('distance_from_user','<',21);
     $users = $users->filter(function ($item) {
                return $item->id > 5;
            })->values()->all();
if($users>0){
    return response()->json(['data'=>$users,"success"=>1,"message"=>"Drivers found successfully!!"],200);
}
return response()->json(['data'=>[],"success"=>0,"message"=>"No drivers found nearby !"],200);






}





}
