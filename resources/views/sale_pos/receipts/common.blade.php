            
            <div class="row">
                <div class="col-xs-7">
                    <div>
                        @if(!empty($business->logo))
                            <img src="{{asset($business->logo)}}" class="img img-responsive" style="width:160px;height:80px;">
                        
                        @endif
                    </div>
                </div>
                <div class="col-xs-5">                    
                    <h3>INVOICE NO: {{$receipt_details->invoice_no}}</h3>  
                    Date: {{$receipt_details->invoice_date}}
                </div>
            </div>
            <div class="row">
                    <div class="col-xs-7">
                        <div>From,</div>    
                        <div>{{$business->name}}</div>
                        <div>{{$business->bname}}, {{$business->city}},{{$business->zip_code}}</div>
                        <div>Phone No: {{$business->mobile}},</div>
                    </div>
                    <div class="col-xs-5">
                        
                        <div>To</div>
                        <div>{{!empty($receipt_details->customer_name)?$receipt_details->customer_name:''}}</div>
                        <div>{!! !empty($receipt_details->customer_info)?$receipt_details->customer_info:'' !!}</div>
                    </div>
            </div>
            <br>
<div class="row">
	<div class="col-xs-12">
		
		<table class="table table-responsive table-bordered">
			<thead>
				<tr>
                                        <th>Seril No</th>
					<th>{{$receipt_details->table_product_label}}</th>
					<th>{{$receipt_details->table_qty_label}}</th>
					<th>{{$receipt_details->table_unit_price_label}}</th>
					<th>{{$receipt_details->table_subtotal_label}}</th>
				</tr>
			</thead>
			<tbody>
                            <?php $i=0;?>
				@forelse($receipt_details->lines as $line)
					<tr>
                                            <td>{{++$i}}</td>
						<td style="word-break: break-all;">
                            {{$line['name']}} {{$line['variation']}} 
                            @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
                            @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
                            @if(!empty($line['sell_line_note']))({{$line['sell_line_note']}}) @endif 
                            @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif 
                            @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif 
                        </td>
						<td>{{$line['quantity']}} {{$line['units']}} </td>
						<td>{{$line['unit_price_inc_tax']}}</td>
						<td>{{$line['line_total']}}</td>
					</tr>
					@if(!empty($line['modifiers']))
						@foreach($line['modifiers'] as $modifier)
							<tr>
								<td>
		                            {{$modifier['name']}} {{$modifier['variation']}} 
		                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
		                            @if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif 
		                        </td>
								<td>{{$modifier['quantity']}} {{$modifier['units']}} </td>
								<td>{{$modifier['unit_price_inc_tax']}}</td>
								<td>{{$modifier['line_total']}}</td>
							</tr>
						@endforeach
					@endif
				@empty
					<tr>
						<td colspan="4">&nbsp;</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>        
            
            <div class="row">
                <div class="total">
                    <div class="col-xs-7">
                        @if(empty($receipt_details->total_due))
                            <div class="text-size bold">{!! $receipt_details->total_due_label !!} <span> {{$receipt_details->total_due}} </span></div>
                        @endif
                    </div>
                    <div class="col-xs-2">                            
                        <div class="text-size bold">{{$receipt_details->subtotal_label}}</div>
                        @if(!empty($receipt_details->shipping_charges))
                            <div class="text-size bold">{{$receipt_details->shipping_charges_label}}</div>
                        @endif
                        @if( !empty($receipt_details->discount) )
                            <div class="text-size bold">{{$receipt_details->discount_label}}</div>
                        @endif
                        @if( !empty($receipt_details->tax) )
                            <div class="text-size bold">{{$receipt_details->tax_label}}</div>
                        @endif
                        <div class="text-size grand-font bold">{!! $receipt_details->total_label !!}</div>                    
                    </div>
                    <div class="col-xs-2">
                        <div class="text-size bold">{!! $receipt_details->subtotal !!}</div>
                        @if(!empty($receipt_details->shipping_charges))
                            <div class="text-size bold">{{$receipt_details->shipping_charges}}</div>
                        @endif
                        @if( !empty($receipt_details->discount) )
                            <div class="text-size bold">{{$receipt_details->discount}}</div>
                        @endif
                        @if( !empty($receipt_details->tax) )
                            <div class="text-size bold">{{$receipt_details->tax}}</div>
                        @endif
                        <div class="text-size grand-font bold">{{$receipt_details->total}} </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    Amount in word: {{ucwords($inWord)}} Only.
                </div>                
            </div>
            <br>
            <div class="row">
                <div class="invoice-total">
                    <div class="col-xs-12">
                        <div style="font-size:13px;">I agree with Term And Conditions</div>
                    </div>  
                </div>
            </div>
                <div class="row bottom-space">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-5">
                                <div>                                    
                                    @if(!empty($receipt_details->payments))
                                        @foreach($receipt_details->payments as $payment)
                                            Payment Method: {{$payment['method']}}<br>
                                            Pay Amount : {{$payment['amount']}}                                            
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                             <div class="col-xs-6">
                                Customer Sign: ..................................
                            </div>
                            <div class="col-xs-6">
                                Authorized Sign: ................................
                            </div>                            
                        </div>                                               
                    </div>
                </div>
            <style type="text/css">
                table tr td{
                    padding-top:0px;
                    padding-bottom: 0px;
                    margin-top: 0px;
                    margin-bottom: 0px;
                    
                }
                .page-break{
                    page-break-after: always;
                }
                .width{
                    width:25%;
                }
            </style>
            <div class="page-break"></div>
            <div class="row">
                <div class="col-xs-12">
                  
                </div>
            </div>