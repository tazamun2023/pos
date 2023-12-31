@extends('layouts.app')
@section('title', __('lang_v1.warranties'))

@section('content')

    <!-- Content Header (Page header) -->
    {{-- <section class="content-header">
    <h1>@lang('lang_v1.warranties')
    </h1>
</section> --}}

    <!-- Main content -->


    <section class="content" style="padding: 15px 0">
        <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
            <h1>@lang('lang_v1.warranties')
            </h1>
            <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
            <div>
                @can('product.create')
                    <div class="box-tools">
                        <button type="button" class="btn f_add-btn btn-modal"
                            data-href="{{ action([\App\Http\Controllers\WarrantyController::class, 'create']) }}"
                            data-container=".view_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')</button>
                    </div>
                @endcan
            </div>
        </section>
        @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_warranties')])
            {{-- @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\App\Http\Controllers\WarrantyController::class, 'create']) }}"
                        data-container=".view_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')</button>
                </div>
            @endslot --}}
            <table class="table table-bordered table-striped" id="warranty_table">
                <thead>
                    <tr class="f_tr-th">
                        <th>@lang('lang_v1.name')</th>
                        <th>@lang('lang_v1.description')</th>
                        <th>@lang('lang_v1.duration')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        @endcomponent

    </section>
    <!-- /.content -->
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            //Status table
            var warranty_table = $('#warranty_table').DataTable({
                buttons: [],
                processing: true,
                serverSide: true,
                ajax: "{{ action([\App\Http\Controllers\WarrantyController::class, 'index']) }}",
                columnDefs: [{
                    "targets": 3,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ]
            });

            $(document).on('submit', 'form#warranty_form', function(e) {
                e.preventDefault();
                $(this).find('button[type="submit"]').attr('disabled', true);
                var data = $(this).serialize();

                $.ajax({
                    method: $(this).attr('method'),
                    url: $(this).attr("action"),
                    dataType: "json",
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div.view_modal').modal('hide');
                            toastr.success(result.msg);
                            warranty_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });
        });
    </script>
@endsection
