@php
	$subtype = '';
@endphp
@if(!empty($transaction_sub_type))
	@php
		$subtype = '?sub_type='.$transaction_sub_type;
	@endphp
@endif

@if(!empty($transactions))
	<table class="table table-slim no-border">
		@foreach ($transactions as $transaction)
			<tr class="cursor-pointer" 
	    		title="Customer: {{$transaction->contact?->name}} 
		    		@if(!empty($transaction->contact->mobile) && $transaction->contact->is_default == 0)
		    			<br/>Mobile: {{$transaction->contact->mobile}}
		    		@endif
	    		" >
				<td>
					{{ $loop->iteration}}.
				</td>
				<td>
					{{ $transaction->invoice_no }} ({{$transaction->contact?->name}})
					@if(!empty($transaction->table))
						- {{$transaction->table->name}}
					@endif
				</td>
				<td class="display_currency">
					{{ $transaction->final_total }}
				</td>
				<td>
					@if(auth()->user()->can('sell.update') || auth()->user()->can('direct_sell.update'))
					<a href="{{action([\App\Http\Controllers\SellPosController::class, 'edit'], [$transaction->id]).$subtype}}">
	    				<i class="fas fa-pen text-muted" aria-hidden="true" title="{{__('lang_v1.click_to_edit')}}"></i>
	    			</a>
	    			@endif
	    			@if(auth()->user()->can('sell.delete') || auth()->user()->can('direct_sell.delete'))
	    			<a href="{{action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$transaction->id])}}" class="delete-sale" style="padding-left: 20px; padding-right: 20px"><i class="fa fa-trash text-danger" title="{{__('lang_v1.click_to_delete')}}"></i></a>
	    			@endif

					@if(!auth()->user()->can('sell.update') && auth()->user()->can('edit_pos_payment'))
						<a href="{{route('edit-pos-payment', ['id' => $transaction->id])}}" 
						title="@lang('lang_v1.add_edit_payment')">
						 <i class="fas fa-money-bill-alt text-muted"></i>
						</a>
					@endif
					<div style='display: inline'>
						<a href='#' url="{{route('send.invoice.to.zatka', \Illuminate\Support\Facades\Crypt::encrypt($transaction->id))}}" class="{{$transaction->zatka_info ? ($transaction->zatka_info->status_code < 300 ? 'hide' : '') : ''  }} send-invoice-link">
							<i class="fa fa-paper-plane text-primary" aria-hidden="true" title="{{__('lang_v1.click_to_send_invoice_to_zatka')}}"></i>
						</a>

						<a href="{{action([\App\Http\Controllers\SellPosController::class, 'printInvoice'], [$transaction->id])}}" class="{{$transaction->zatka_info ? ($transaction->zatka_info->status_code > 300 ? 'hide' : '') : 'hide'  }}  print-invoice-link">
							<i class="fa fa-print text-muted" aria-hidden="true" title="{{__('lang_v1.click_to_print')}}"></i>
						</a>
					</div>
				</td>
			</tr>
		@endforeach
	</table>
@else
	<p>@lang('sale.no_recent_transactions')</p>
@endif

<script>
	$('.send-invoice-link').on('click', function(e){
		let recent_transaction = $('#recent_transactions_modal');
		recent_transaction.modal('hide');
		Swal.fire({
			html: `<div style='padding: 20px 40px'>Sending Invoice To ZATKA...</div>`,
			allowClickOutside: true,
			showConfirmButton: false,
		});
		e.preventDefault();
		let url = $(this).attr('url');
		axios.get(url)
		.then(function(response){
			Swal.close();
			recent_transaction.modal('show');
			switch (response.status){
				case 200:

					break;
				case 202:

					console.log('request accepted. but warning');
					break;
			}
		})
		.catch(function(error){
			Swal.close();
			recent_transaction.modal('show');
			let response = error.response;
			switch (response.status){
				case 303:

					break;
				case 400:

					break;
				case 401:

					break;
				case 500:

					break;
				default:

					break;
			}
			if(response.status === 400){
				console.log()
			}
		})
	})
</script>