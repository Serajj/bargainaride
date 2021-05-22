<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Location;
use App\Models\Order;
use App\Models\User;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Validator;

class BookingController extends Controller
{
    //

    public function book_an_order(Request $request)
    {
        $user = Auth::user();
        $existingOrder= Booking::where(['order_status'=>"pending",'uid'=>$user->id])->first();

        if($existingOrder){
            return response()->json(["sucess"=>1,"message"=>"you alredy have a booking .",'booking_id'=>$existingOrder->id]);
        }
        
        $validator = Validator::make($request->all(),[
            'customer_price'=>'required|min:1',
            'pickup_lattitude'=>'required',
            'pickup_longitude'=>'required',
            'pickup_address'=>'required',
            'destination_address'=>'required',
            'destination_lattitude'=>'required',
            'destination_longitude'=>'required',
            'car_type'=>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $bookRide= new Booking();

        $bookRide->uid=$user->id;
        $bookRide->customer_price=$request->customer_price;
        $bookRide->pickup_lattitude=$request->pickup_lattitude;
        $bookRide->pickup_longitude=$request->pickup_longitude;
        $bookRide->pickup_address=$request->pickup_address;
        $bookRide->destination_address=$request->destination_address;
        $bookRide->destination_lattitude=$request->destination_lattitude;
        $bookRide->destination_longitude=$request->destination_longitude;
        $bookRide->car_type=$request->car_type;

        $bookRide->save();


        $bdata=Booking::where('id',$bookRide->id)->first();

        if($bookRide){
            return response()->json(["sucess"=>1,"message"=>"Ride Requested Successfully !!",'booking_id'=>$bdata->id],200);
        }else{
            return response()->json(["sucess"=>0,"message"=>"Ride request failed","data"=>[]],200);
        }
    }

    public function getBooking(Request $request)
    {
        $user = Auth::user();
        $driver_location= Location::where('uid',$user->id)->first();

        $bookings= Booking::select('id','customer_price','pickup_lattitude','pickup_longitude','destination_lattitude','destination_longitude','pickup_address','destination_address','uid','created_at')->where('order_status','pending')->get();



       if($driver_location){

        if($bookings){
          
            foreach ($bookings as $value) {
               

        $latitudeTo=$driver_location->lattitude;
        $longitudeTo=$driver_location->longitude;

  
       $lat1=$latitudeTo;
       $lon1=$longitudeTo;

       $lat2=$value->pickup_lattitude;
       $lon2=$value->pickup_longitude;
       $rad = M_PI / 180;

       //calculating the distance between users and driver
       $distance=acos(sin($lat2*$rad) * sin($lat1*$rad) + cos($lat2*$rad) * cos($lat1*$rad) * cos($lon2*$rad - $lon1*$rad)) * 6371;// Kilometers
         $value->pickup_distance=$distance;

        
 
   
         $lat1=$value->destination_lattitude;
         $long1=$value->destination_longitude;
 
        $lat2=$value->pickup_lattitude;
        $long2=$value->pickup_longitude;
        
        


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

        $value->booking_distance=$num_dist;
        $userdetail=User::select('name','profile_pic')->where('id',$value->uid)->first();
        
        
        $orderStatus= Order::where(['oid'=>$value->id,'did'=>$user->id])->first();
        
        if($orderStatus){
             $value->status=$orderStatus->status;
        }else{
             $value->status='pending';
        }
        if($userdetail){
        $value->profile_pic=$userdetail->profile_pic;
        $value->name=$userdetail->name;
            
        }
        
         }

         $bookings=$bookings->where('pickup_distance','<',21);
         $bookings = $bookings->filter(function ($item) {
                    return $item->id > 0;
                })->values()->all();

         if($bookings){
            return response()->json(['success'=>1,'message'=>'Booking found.','data'=>$bookings]);
         }else{
            return response()->json(['success'=>0,'message'=>'No Booking found.']);
         }

        }else{
            return response()->json(['success'=>0,'message'=>'No Booking found.']);
        }

       }else{
             return response()->json(['message'=>"Please update your location first!!"]);
       }

    }




    public function driverSelectBooking(Request $request)
    {
        $user= Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'driver_price'=>'required|min:1',
            'distance_from_user'=>'required',
            'time_to_pickup'=>'required',
            'order_id'=>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $order= new Order();
        $order->did=$user;
        $order->driver_price=$request->driver_price;
        $order->time_to_pickup	=$request->time_to_pickup;
        $order->distance_from_user=$request->distance_from_user;
        $order->oid=$request->order_id;

        $order->save();

        return response()->json(['success'=>1,'message'=>'Please wait till user accepts your request !!']);
    }

    public function updatePrice(Request $request)
    {
        $user= Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'driver_price'=>'required|min:1',
            'order_id'=>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $order= Order::where(['oid'=>$request->order_id,'did'=>$user])->first();
        if($order){
            $order->driver_price=$request->driver_price;
            $order->update();

            return response()->json(['success'=>1,'message'=>"Price updated successfully !!"]);
        }else{
            return response()->json(['success'=>0,'message'=>"Order not found"]);
        }
    }





public function cancel_booking(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1',
            'cancel_reason'=>'required',
            'cancelled_by'=>'required'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

$existingOrder= Booking::where(['order_status'=>"pending",'uid'=>$user->id,'id'=>$request->order_id])->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>1,"message"=>"Alredy Cancelled / No Booking exist."]);
        }
        
$existingOrder->order_status="cancelled";
$existingOrder->cancel_reason= 'cancelled by : '.$request->cancelled_by . ". Reason : ".$request->cancel_reason;
$existingOrder->update();

       


        

         return response()->json(["sucess"=>1,"message"=>"Booking Cancelled Successfully !"],200);
    }


public function confirm_booking(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1',
            'driver_id'=>'required',
            'agreed_amount'=>'required|min:1'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $existingOrder= Booking::where(['uid'=>$user->id,'id'=>$request->order_id])->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>1,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
       if($existingOrder->order_status == 'verification' || $existingOrder->order_status == 'confirmed'){
         $otp=  $existingOrder->otp;
       }else{
        $otp=rand(1111,9999);
        $existingOrder->order_status="verification";
        $existingOrder->otp=$otp;
        $existingOrder->did = $request->driver_id;
        $existingOrder->order_price = $request->agreed_amount;
        $existingOrder->update();
        
       }
       
       $updatedriver=User::where('id',$request->driver_id)->first();
       $updatedriver->on_ride=1;
       $updatedriver->update();
       
       
       $updateuser=User::where('id',$user->id)->first();
       $updateuser->on_ride=1;
       $updateuser->update();
       

     
       $bookingInfo=DB::table('users')->select('users.name','users.mobile','users.profile_pic','driver_details.vehicle_no','driver_details.car_model','driver_details.car_type')

                ->join('driver_details','users.id','=','driver_details.uid')
        
                ->where('users.id',$request->driver_id)
        
                ->first();
                
              $bookingInfo->driver_id= $request->driver_id; 


        

         return response()->json(["sucess"=>1,"message"=>"Driver is coming at pickup point.","verification_otp"=>$otp,"data"=>$bookingInfo],200);
    }



public function get_booking_detail(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1',
            'user_id'=>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $existingOrder= Booking::where('id',$request->order_id)->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>1,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
        $bookingInfo=DB::table('users')->select('users.name','users.mobile','users.profile_pic','bookings.pickup_lattitude','bookings.pickup_longitude')

                ->join('bookings','users.id','=','bookings.uid')
        
                ->where('bookings.id',$request->order_id)
        
                ->first();
                
              $bookingInfo->user_id= $request->user_id; 
              
        return response()->json(["sucess"=>1,"message"=>"Meet user at pickup point.","data"=>$bookingInfo],200);
        
        
    }
    
    
    public function verify_booking_otp(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1',
            'otp'=>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $existingOrder= Booking::where('id',$request->order_id)->first();
        
         if(!$existingOrder){
            return response()->json(["sucess"=>1,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
        if($existingOrder->otp==$request->otp){
            $existingOrder->order_status="confirmed";
            $existingOrder->update();
            return response()->json(["sucess"=>1,"message"=>"OTP Verified","destination_lattitude"=>$existingOrder->destination_lattitude,"destination_longitude"=>$existingOrder->destination_longitude,"destination_address"=>$existingOrder->destination_address],200);
        }else{
            return response()->json(["sucess"=>0,"message"=>"Invalid OTP"],200);
        }
        
    }
    
    
    public function booking_complete(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $existingOrder= Booking::where('id',$request->order_id)->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>0,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
        $existingOrder->order_status="completed";
        $existingOrder->update();
        
        $didu=User::where('id',$user->id)->first();
        $didu->on_ride=0;
        $didu->update();
        
        $userupdt=User::where('id',$existingOrder->uid)->first();
        $userupdt->on_ride=0;
        $userupdt->update();
        
        
         $existingOrder= Booking::where('id',$request->order_id)->first();
              
        return response()->json(["sucess"=>1,"message"=>"Ride completed.","data"=>$existingOrder],200);
        
        
    }
    
     public function addFeedback(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(),[
            'reviewee_id'=>'required|min:1',
            'reviewee_name'=>'required|min:1',
            'review_message'=>'required|min:1',
            'order_id'=>'required|min:1',
            'rating'=>'required|min:1'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }
        
        $feedback= new Feedback();
        $feedback->reviewer_id =$request->reviewee_id;
        $feedback->order_id = $request->order_id;
        $feedback->reviewer_name = $request->reviewee_name;
         $feedback->uid = $user->id;
        $feedback->rating = $request->rating;
        $feedback->feedback = $request->review_message;
        
        $feedback->save();
        
         return response()->json(["sucess"=>1,"message"=>"Feedback added successfully, Thank you !!."],200);
    }
    
    
    
     public function makePayment(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1',
              'payment_mode'=>'required|min:1',
                'paid_amount'=>'required|min:1'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $existingOrder= Booking::where('id',$request->order_id)->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>0,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
        $existingOrder->payment_mode=$request->payment_mode;
        $existingOrder->paid_amount=$request->paid_amount;
        $existingOrder->offer_code=$request->offer_code ? $request->offer_code: "0";
        $existingOrder->offer_price=$request->offer_price ?$request->offer_price  :"0";
        $existingOrder->payment_status="Paid";
        $existingOrder->update();
        
         $existingOrder= Booking::where('id',$request->order_id)->first();
              
        return response()->json(["sucess"=>1,"message"=>"Ride completed.","data"=>$existingOrder],200);
        
        
    }
    
    
     public function paymentStatus(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1'
             
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }
        
        $existingOrder= Booking::select('payment_mode','payment_status')->where('id',$request->order_id)->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>0,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
         return response()->json(["sucess"=>1,"message"=>"Payment Status Fetched Successfully.","data"=>$existingOrder],200);
        
    }
    
     public function getRideHistory(Request $request)
    {
         $userdata = Auth::user();
         $user=$userdata->id;
         if($userdata->type=='driver'){
              $myride= Booking::select('id','uid','did','order_status','pickup_address','destination_address','order_price','payment_mode','created_at')->where(['did'=>$user,'order_status'=>'completed'])->get();
         }else{
              $myride= Booking::select('id','uid','did','order_status','pickup_address','destination_address','order_price','payment_mode','created_at')->where(['uid'=>$user,'order_status'=>'completed'])->get();
         }
         
       
        
       
     
       
        if(!$myride){
            
             if(!$myride){
            return response()->json(["sucess"=>0,"message"=>"No Ride History Available Yet"]);
             }
        }
        foreach($myride as $item){
            $driver = User::select('name','profile_pic')->where('id',$item->did)->first();
            $passenger = User::select('name','profile_pic')->where('id',$item->uid)->first();
            
           
           if($driver && $passenger){
                $item->driver_name=$driver->name;
             $item->driver_image=$driver->profile_pic;
              $item->passenger_name=$passenger->name;
              $item->passenger_image=$passenger->profile_pic;
           }
        }
        
    
         return response()->json(["sucess"=>1,"message"=>"Ride history found","data"=>$myride],200);
    }
    
    
     public function setPaymentMethod(Request $request)
    {
        $user = Auth::user();
        
        
        $validator = Validator::make($request->all(),[
            'order_id'=>'required|min:1',
              'payment_mode'=>'required|min:1'
                
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }

        $existingOrder= Booking::where('id',$request->order_id)->first();

        if(!$existingOrder){
            return response()->json(["sucess"=>0,"message"=>"Wrong booking id / No Booking exist."]);
        }
        
        $existingOrder->payment_mode=$request->payment_mode;
      
        $existingOrder->update();
        
        $existingOrder= Booking::where('id',$request->order_id)->first();
              
        return response()->json(["sucess"=>1,"message"=>"Payment mode set"],200);
        
        
    }
    
    
    public function getFeedBack(Request $request)
    {
       
        
        
        $validator = Validator::make($request->all(),[
            'user_id'=>'required|min:1'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }
        
        $feedback=Feedback::where('uid',$request->user_id)->get();
        
        if($feedback){
            return response()->json(["sucess"=>1,"message"=>"Feedback Found !","data"=>$feedback],200);
        }else{
            return response()->json(["sucess"=>0,"message"=>"No Feedbacks Found"],200);
        }
        
        
    }
    
    

}
