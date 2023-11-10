<div class="row">
  <div class="col-md-12">
    <hr>
    <button type="button" class="btn btn-primary no-print" id="product_details_button">@lang('lang_v1.visible')</button>
    <button type="button" class="btn btn-primary no-print" style='display: none' id="product_details_button1">@lang('lang_v1.invisible')</button>
    <h3>@lang('lang_v1.product_sold_details_register')</h3>
    <table class="table table-condensed" id='detailsTable'>
      <tr>
        <th>#</th>
        <th>@lang('product.sku')</th>
        <th>@lang('sale.product')</th>
        <th>@lang('sale.qty')</th>
        <th>@lang('sale.total_amount')</th>
      </tr>
      @php
        $total_amount = 0;
        $total_quantity = 0;
      @endphp
      @foreach($details['product_details'] as $detail)
        <tr>
          <td>
            {{$loop->iteration}}.
          </td>
          <td>
            {{$detail->sku}}
          </td>
          <td>
            {{$detail->product_name}}
            @if($detail->type == 'variable')
             {{$detail->product_variation_name}} - {{$detail->variation_name}}
            @endif
          </td>
          <td>
            {{@format_quantity($detail->total_quantity)}}
            @php
              $total_quantity += $detail->total_quantity;
            @endphp
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">
              {{$detail->total_amount}}
            </span>
            @php
              $total_amount += $detail->total_amount;
            @endphp
          </td>
        </tr>
      @endforeach

      
      @php
        $total_amount += ($details['transaction_details']->total_tax - $details['transaction_details']->total_discount);

        $total_amount += $details['transaction_details']->total_shipping_charges;
      @endphp

      <!-- Final details -->
      <tr class="success">
        <th>#</th>
        <th></th>
        <th></th>
        <th>{{$total_quantity}}</th>
        <th>

          @if($details['transaction_details']->total_tax != 0)
            @lang('sale.order_tax'): (+)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_tax}}
            </span>
            <br/>
          @endif

          @if($details['transaction_details']->total_discount != 0)
            @lang('sale.discount'): (-)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_discount}}
            </span>
            <br/>
          @endif
          @if($details['transaction_details']->total_shipping_charges != 0)
            @lang('lang_v1.total_shipping_charges'): (+)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_shipping_charges}}
            </span>
            <br/>
          @endif

          @lang('lang_v1.grand_total'):
          <span class="display_currency" data-currency_symbol="true">
            {{$total_amount}}
          </span>
        </th>
      </tr>

    </table>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <hr>
    <button type="button" class="btn btn-primary no-print" id="by_brand">@lang('lang_v1.visible')</button>
    <button type="button" class="btn btn-primary no-print" style='display: none' id="by_brand1">@lang('lang_v1.invisible')</button>
    <h3>@lang('lang_v1.product_sold_details_register') (@lang('lang_v1.by_brand'))</h3>
    <table class="table table-condensed" id='by_brand_table'>
      <tr>
        <th>#</th>
        <th>@lang('brand.brands')</th>
        <th>@lang('sale.qty')</th>
        <th>@lang('sale.total_amount')</th>
      </tr>
      @php
        $total_amount = 0;
        $total_quantity = 0;
      @endphp
      @foreach($details['product_details_by_brand'] as $detail)
        <tr>
          <td>
            {{$loop->iteration}}.
          </td>
          <td>
            {{$detail->brand_name}}
          </td>
          <td>
            {{@format_quantity($detail->total_quantity)}}
            @php
              $total_quantity += $detail->total_quantity;
            @endphp
          </td>
          <td>
            <span class="display_currency" data-currency_symbol="true">
              {{$detail->total_amount}}
            </span>
            @php
              $total_amount += $detail->total_amount;
            @endphp
          </td>
        </tr>
      @endforeach

      
      @php
        $total_amount += ($details['transaction_details']->total_tax - $details['transaction_details']->total_discount);

        $total_amount += $details['transaction_details']->total_shipping_charges;
      @endphp

      <!-- Final details -->
      <tr class="success">
        <th>#</th>
        <th></th>
        <th>{{$total_quantity}}</th>
        <th>

          @if($details['transaction_details']->total_tax != 0)
            @lang('sale.order_tax'): (+)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_tax}}
            </span>
            <br/>
          @endif

          @if($details['transaction_details']->total_discount != 0)
            @lang('sale.discount'): (-)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_discount}}
            </span>
            <br/>
          @endif
          @if($details['transaction_details']->total_shipping_charges != 0)
            @lang('lang_v1.total_shipping_charges'): (+)
            <span class="display_currency" data-currency_symbol="true">
              {{$details['transaction_details']->total_shipping_charges}}
            </span>
            <br/>
          @endif

          @lang('lang_v1.grand_total'):
          <span class="display_currency" data-currency_symbol="true">
            {{$total_amount}}
          </span>
        </th>
      </tr>

    </table>
  </div>
</div>

@if($details['types_of_service_details'])
  <div class="row">
    <div class="col-md-12">
      <hr>
      <h3>@lang('lang_v1.types_of_service_details')</h3>
      <table class="table">
        <tr>
          <th>#</th>
          <th>@lang('lang_v1.types_of_service')</th>
          <th>@lang('sale.total_amount')</th>
        </tr>
        @php
          $total_sales = 0;
        @endphp
        @foreach($details['types_of_service_details'] as $detail)
          <tr>
            <td>
              {{$loop->iteration}}
            </td>
            <td>
              {{$detail->types_of_service_name ?? "--"}}
            </td>
            <td>
              <span class="display_currency" data-currency_symbol="true">
                {{$detail->total_sales}}
              </span>
              @php
                $total_sales += $detail->total_sales;
              @endphp
            </td>
          </tr>
          @php
            $total_sales += $detail->total_sales;
          @endphp
        @endforeach
        <!-- Final details -->
        <tr class="success">
          <th>#</th>
          <th></th>
          <th>
            @lang('lang_v1.grand_total'):
            <span class="display_currency" data-currency_symbol="true">
              {{$total_amount}}
            </span>
          </th>
        </tr>

      </table>
    </div>
  </div>
@endif

<script>
  document.getElementById('product_details_button').addEventListener('click', function() {
    var table = document.getElementById('detailsTable');
    var btnStyle = document.getElementById('product_details_button');
    var btnStyle1 = document.getElementById('product_details_button1');

    if (table.style.display === 'none' || table.style.display === '') {
      // If the table is hidden or not set, show it
      table.style.display = 'table';

      // Hide product_details_button and show product_details_button1
      btnStyle.style.display = 'none';
      btnStyle1.style.display = 'block';
    } else {
      // If the table is visible, hide it
      table.style.display = 'none';
    }
  });
  document.getElementById('product_details_button1').addEventListener('click', function() {
    var table = document.getElementById('detailsTable');
    var btnStyle = document.getElementById('product_details_button');
    var btnStyle1 = document.getElementById('product_details_button1');

    if (table.style.display === 'none' || table.style.display === '') {
      // If the table is hidden or not set, show it
      table.style.display = 'table';

      // Hide product_details_button and show product_details_button1
      btnStyle1.style.display = 'none';
      btnStyle.style.display = 'block';
    } else {
      // If the table is visible, hide it
      table.style.display = 'none';
    }
  });
  document.getElementById('by_brand').addEventListener('click', function() {
    var table = document.getElementById('by_brand_table');
    var btnStyle = document.getElementById('by_brand');
    var btnStyle1 = document.getElementById('by_brand1');

    if (table.style.display === 'none' || table.style.display === '') {
      // If the table is hidden or not set, show it
      table.style.display = 'table';

      // Hide product_details_button and show product_details_button1
      btnStyle.style.display = 'none';
      btnStyle1.style.display = 'block';
    } else {
      // If the table is visible, hide it
      table.style.display = 'none';
    }
  });
  document.getElementById('by_brand1').addEventListener('click', function() {
    var table = document.getElementById('by_brand_table');
    var btnStyle = document.getElementById('by_brand');
    var btnStyle1 = document.getElementById('by_brand1');

    if (table.style.display === 'none' || table.style.display === '') {
      // If the table is hidden or not set, show it
      table.style.display = 'table';

      // Hide product_details_button and show product_details_button1
      btnStyle1.style.display = 'none';
      btnStyle.style.display = 'block';
    } else {
      // If the table is visible, hide it
      table.style.display = 'none';
    }
  });
</script>
