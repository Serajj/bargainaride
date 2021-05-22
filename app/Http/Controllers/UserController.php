<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{
    function index(Request $request)
    {
       
        if(!$request->mobile){
            $response = [
                'result' => false,
                'ResponseMsg' => "Please provide correct mobile number!"
            ];
        
             return response($response, 201);
        }
        
        $otp=1234;//rand(1111,9999);
        
        //otp generation
       
        
        
        
        //end otp generation
        
        
        
        
        
        $user= User::where('mobile', $request->mobile)->first();
         //print_r($request->mobile);
            if (!$user) {

                $new=new user();
                $new->mobile=$request->mobile;
                $new->otp=$otp;
                $new->password= Hash::make('serajalam');
                $new->save();
                // return response([
                //     'message' => ['These credentials do not match our records.']
                // ], 404);
            }else{
                $user->otp=$otp;
                $user->update();
            }
        
            
        
            $response = [
                'result' => true,
                'ResponseMsg' => "OTP send successfully!"
            ];
        
             return response($response, 201);
    }


    function otpverify(Request $request)
    {
        if(!$request->mobile || !$request->otp){
            return response([
                'result' => false,
                'data'=>[],
                'token'=>null,
                'message' => 'Please provide Mobile and OTP.'
            ], 404);
        }
        $user= User::where('mobile', $request->mobile)->where('otp', $request->otp)->first();
         //print_r($request->mobile);
            if (!$user) {
                return response([
                    'result' => false,
                    'data'=>[],
                    'token'=>null,
                    'message' => 'Wrong OTP.'
                ], 404);
            }
        
             $token = $user->createToken('bargainaride')->accessToken;
        
            $response = [
                'result' => true,
                'data'=>[$user],
                'token'=>$token,
                'ResponseMsg' => "OTP verified successfully!"
            ];
        
             return response($response, 201);
    }

    function adduserdetail(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'wpnum'=>'required|min:10|max:12',
            'city'=>'required',
            'state'=>'required',
            'type'=>'required',
            'aadhar_no'=>'required'
           
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(),202);
        }
 
            $user=User::where('id',Auth::user()->id)->first();
            $user->name=$request->name;
            $user->wpnum=$request->wpnum;
            $user->city=$request->city;
            $user->state=$request->state;
            $user->type=$request->type;
            $user->aadhar_no=$request->aadhar_no;



            $user->password=Hash::make("Serajisagoodprogrammer");
            $request->type=="user" ? $user->profile_complete="1":($user->profile_complete=="0" ? $user->profile_complete="0":$user->profile_complete="1");
            $request->type=="user" ? $user->account_status="active":$user->account_status="pending";
            $user->update();

        $response = [
                    'result' => true,
                    'data'=>[$user],
                    'ResponseMsg' => "Saved Successfully!"
                ];
        
             return response()->Json($response, 201);

    }
}
