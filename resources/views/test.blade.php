@can('role_delete')
<h1>Hello I can edit</h1>

@php
     if(Auth::user()){
                    foreach (Auth::user()->roles as $key => $data) {
                        
                        foreach ($data->permissions as $key => $myd) {
                            print_r($myd->title);
                        }
                       
                    
                        
                    }
                    
                }
@endphp
@endcan