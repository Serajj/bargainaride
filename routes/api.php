<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Routing\RouteGroup;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post("login",[UserController::class,'index'])->name('login');

Route::get("login",function(){
    return response()->Json(['error'=>'Please provide valid auth token']);
})->name('login');
Route::post("validate_otp",[UserController::class,'otpverify']);

Route::middleware(['auth:api'])->group(function () {

    //User Api

    Route::post("new_signup",[UserController::class,'adduserdetail']);
     Route::post("update_profile",[UserController::class,'adduserdetail']);
    Route::post("get_available_drivers",[DriverController::class,'getAvailableDrivers']);



    //order apis
    Route::post("bookRide",[BookingController::class,'book_an_order']);
    Route::post("getBooking",[BookingController::class,'getBooking']);
    Route::post("cancel_booking",[BookingController::class,'cancel_booking']);
     Route::post("confirm_booking",[BookingController::class,'confirm_booking']);
      Route::post("get_booking_detail",[BookingController::class,'get_booking_detail']);
      Route::post("verify_pickup",[BookingController::class,'verify_booking_otp']);
      
      Route::post("booking_complete",[BookingController::class,'booking_complete']);
      
      
      //feedback
      
      Route::post("add_feedback",[BookingController::class,'addFeedback']);
      Route::post("add_payment",[BookingController::class,'makePayment']);
      
       Route::post("payment_status",[BookingController::class,'paymentStatus']);
       
        Route::post("get_ride_history",[BookingController::class,'getRideHistory']);
        Route::post("set_payment_mode",[BookingController::class,'setPaymentMethod']);



    //Driver Api
    Route::post("add_driver_data",[DriverController::class,'addDriver']);
    Route::post("selectBooking",[BookingController::class,'driverSelectBooking']);
    Route::post("update_price",[BookingController::class,'updatePrice']);
    Route::post("update_driver_data",[DriverController::class,'update_driver']);




    //Location APIs
    Route::get("getLocation",[LocationController::class,'getLocation']);
    Route::post("updateLocation",[LocationController::class,'updateLocation']);
    Route::post("getBooking",[BookingController::class,'getBooking']);
    
});
Route::get("getDistance",[LocationController::class,'GetDrivingDistance']);

Route::get("getDriverList",[LocationController::class,'disByCoodinate']);



 Route::post("getFeedbacks",[BookingController::class,'getFeedBack']);