@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('Ride') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>Order ID</th>
                        <th>User Name</th>
                        <th>Driver Name</th>
                        <th>Order Status</th>
                        <th>Order Price</th>
                        <th>Rider Price</th>
                        <th>Pickup Address</th>
                        <th>Destination Address</th>
                        <th>Payment</th>
                        <th>Time</th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $key => $userManagement)
                        <tr data-entry-id="{{ $userManagement->id }}">
                            <td>

                            </td>
                            
                            <td> {{ $userManagement->id }}</td>
                            <td> {{ $userManagement->riderName->name}}</td>
                            <td> {{ $userManagement->did ? $userManagement->driverName->name :''}}</td>
                            <td> {{ $userManagement->order_status }}</td>
                            <td> {{ $userManagement->order_price }}</td>
                            <td> {{ $userManagement->customer_price }}</td>
                            <td> {{ $userManagement->pickup_address }}</td>
                            <td> {{ $userManagement->destination_address }}</td>
                            <td> 
                                <ul style="list-style: none">
                                  <li>{{ $userManagement->payment_status ?$userManagement->payment_status." : ".$userManagement->paid_amount :'Booking Pending' }}</li> 
                                  <li>{{ $userManagement->payment_mode ?$userManagement->payment_mode :'' }}</li> 
                                  <li>{{ $userManagement->offer_code ?"Code : ".$userManagement->offer_code :'' }}</li> 
                                  <li>{{ $userManagement->offer_price ?"Offer Price : ".$userManagement->offer_price :'' }}</li> 
                                 
                                 
                                </ul>
                                
                            </td>
                            <td> {{ $userManagement->created_at }}</td>


                               
                            


                           


                            <td>
                                @can('order_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.order.show', $userManagement->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                            
                                    @endcan
                                    
                                
                               @can('order_delete')
                                   
                               
                                    <form action="{{ route('admin.order.destroy', $userManagement->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="id" value="{{$userManagement->id}}">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
                                    </form>

                                    @endcan
                               
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@section('scripts')
@parent
<script>
    $(function () {
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.order.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)

  //dtButtons.push(deleteButton)


  $('.datatable:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})

</script>
@endsection
@endsection