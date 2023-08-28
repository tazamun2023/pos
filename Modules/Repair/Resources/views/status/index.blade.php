<button type="button" class="btn btn-sm f_btn-primary btn-modal pull-right" 
    data-href="{{action([\Modules\Repair\Http\Controllers\RepairStatusController::class, 'create'])}}" 
    data-container=".view_modal">
    <i class="fa fa-plus"></i>
    @lang( 'messages.add' )
</button>
<br><br>
<table class="table table-bordered table-striped" id="status_table" style="width: 100%">
    <thead>
    <tr class="f_tr-th">
        <th>@lang( 'repair::lang.status_name' )</th>
        <th>@lang( 'repair::lang.color' )</th>
        <th>@lang( 'repair::lang.sort_order' )</th>
        <th>@lang( 'messages.action' )</th>
    </tr>
    </thead>
</table>
<div class="modal fade brands_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>