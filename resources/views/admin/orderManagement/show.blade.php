@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('Booking detail') }}
    </div>

   
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                
                <tr>
                    <th>
                        {{ trans('Pickup Address') }}
                    </th>
                    <td>
                        {{ $user->pickup_address }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('Destination Address') }}
                    </th>
                    <td>
                        {{ $user->destination_address }}
                    </td>
                </tr>
                <tr>
                    
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection