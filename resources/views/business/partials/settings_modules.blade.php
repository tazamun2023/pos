<div class="pos-tab-content" style="border-radius: 10px;padding: 20px !important;">
	<div class="row">
	@if(!empty($modules))
		<h4 style="padding: 0 20px">@lang('lang_v1.enable_disable_modules')</h4>
		@foreach($modules as $k => $v)
            <div class="col-sm-4">
                <div class="form-group addProduct_form">
                    <div class="checkbox">
                    <br>
                      <label>
                        {!! Form::checkbox('enabled_modules[]', $k,  in_array($k, $enabled_modules) , 
                        ['class' => 'input-icheck']); !!} {{$v['name']}}
                      </label>
                      @if(!empty($v['tooltip'])) @show_tooltip($v['tooltip']) @endif
                    </div>
                </div>
            </div>
        @endforeach
	@endif
	</div>
</div>