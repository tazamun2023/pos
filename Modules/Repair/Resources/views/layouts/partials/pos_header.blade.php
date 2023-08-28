@if ($__is_repair_enabled)
    @can('repair.create')
        <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) . '?sub_type=repair' }}"
            title="{{ __('repair::lang.add_repair') }}" data-toggle="tooltip" data-placement="bottom"
            class="btn f_content-btn-pos btn-flat m-6 btn-xs m-5 pull-right" 
			style="border-radius: 10px !important;background: #E1E8FF;">
            <i class="fa fa-wrench fa-lg" style="margin-bottom: 5px ;font-size: 15px"></i>
            <strong>@lang('repair::lang.repair')</strong>
        </a>
    @endcan
@endif
