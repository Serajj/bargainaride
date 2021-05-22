<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

       

        foreach (Permission::all() as $value) {
           
            $happy=$value->title;
            Gate::define($value->title, function () use($happy) {
               
                if(Auth::user()){
                   //echo '<script>alert("'.$happy.'")</script>';
                    foreach (Auth::user()->roles as $key => $data) {
                       
                        foreach ($data->permissions as $key => $myd) {
                           
                            if($myd->title==$happy){
                                return true;
                            }


                           
                        }    
                    }
                    
                }
                
            });
        }

        
        //


       


      
    }

   
}
