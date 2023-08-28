@extends('layouts.app')
@section('title', __('expense.expense_categories'))

@section('content')

<!-- Content Header (Page header) -->
{{-- <section class="content-header">
    <h1>@lang( 'expense.expense_categories' )
        <small>@lang( 'expense.manage_your_expense_categories' )</small>
    </h1>
</section> --}}

<!-- Main content -->
<section class="content no-print" style="padding: 15px 0;">
    <!-- Content Header (Page header) -->
    <section class="content-header f_content-header f_product_content-header" style="margin-bottom: 25px">
        <h1>@lang( 'expense.expense_categories' )
            <small>@lang( 'expense.manage_your_expense_categories' )</small>
        </h1>


        <div class="box-tools">
            <button type="button" class="btn f_add-btn btn-modal" 
            data-href="{{action([\App\Http\Controllers\ExpenseCategoryController::class, 'create'])}}" 
            data-container=".expense_category_modal">
            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
        </div>
    </section>
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'expense.all_your_expense_categories' )])
        {{-- @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                data-href="{{action([\App\Http\Controllers\ExpenseCategoryController::class, 'create'])}}" 
                data-container=".expense_category_modal">
                <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        @endslot --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="expense_category_table">
                <thead>
                    <tr class="f_tr-th">
                        <th>@lang( 'expense.category_name' )</th>
                        <th>@lang( 'expense.category_code' )</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade expense_category_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
