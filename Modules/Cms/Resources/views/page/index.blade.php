@extends('layouts.app')
@section('title', __('cms::lang.cms'))

@section('content')

    @include('cms::layouts.nav')



    <!-- Main content -->
    <section class="content" style="padding: 15px 0">
        <!-- Content Header (Page header) -->
        <section class="content-header f_content-header f_product_content-header">
            <h1>
                @if ($post_type == 'page')
                    @lang('cms::lang.page')
                @elseif($post_type == 'testimonial')
                    @lang('cms::lang.testimonial')
                @elseif($post_type == 'blog')
                    @lang('cms::lang.blog')
                @endif
            </h1>
            <div class="box-tools">
                <a class="btn btn-block f_add-btn"
                    href="{{ action([\Modules\Cms\Http\Controllers\CmsPageController::class, 'create'], ['type' => $post_type]) }}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
            </div>
        </section>
        @component('components.widget', ['class' => 'box-primary'])

            <div class="row">
                @forelse($pages as $page)
                    <div class="col-md-4 page-box" style="">
                        @component('components.widget', ['class' => 'box box-solid', 'title' => $page->title])
                            <div style="display: flex;justify-content: space-between;align-items: flex-end">
                                <div style="display: flex;flex-direction: column;gap: 5px;">
                                    <p style="margin: 0 !important">
                                        <b>@lang('cms::lang.priority'): </b> {{ $page->priority }}
                                    </p>
                                    <p class="text-muted" style="margin: 0 !important">
                                        @lang('lang_v1.added_on'): {{ @format_datetime($page->created_at) }}
                                    </p>
                                    @if (!empty($page->layout))
                                        <p class="text-muted" style="margin: 0 !important">
                                            @lang('cms::lang.layout'): @lang('cms::lang.' . $page->layout)
                                        </p>
                                    @endif
                                    @if ($page->is_enabled == 0)
                                        <span class="label bg-gray" style="margin: 0 !important">@lang('cms::lang.disabled')</span>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 10px">
                                    <a class="btn btn-block f_edit_unit_button btn-xs"
                                        href="{{ action([\Modules\Cms\Http\Controllers\CmsPageController::class, 'edit'], [$page->id, 'type' => $post_type]) }}">
                                        <i class="fa fa-edit"></i>
                                        @lang('messages.edit')
                                    </a>
                                    @if (empty($page->layout))
                                        <button
                                            data-href="{{ action([\Modules\Cms\Http\Controllers\CmsPageController::class, 'destroy'], [$page->id, 'type' => $post_type]) }}"
                                            class="btn btn-xs f_delete_unit_button delete_page">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <g clip-path="url(#clip0_248_3811)">
                                                    <path
                                                        d="M10.625 3.75H13.75V5H12.5V13.125C12.5 13.2908 12.4342 13.4497 12.3169 13.5669C12.1997 13.6842 12.0408 13.75 11.875 13.75H3.125C2.95924 13.75 2.80027 13.6842 2.68306 13.5669C2.56585 13.4497 2.5 13.2908 2.5 13.125V5H1.25V3.75H4.375V1.875C4.375 1.70924 4.44085 1.55027 4.55806 1.43306C4.67527 1.31585 4.83424 1.25 5 1.25H10C10.1658 1.25 10.3247 1.31585 10.4419 1.43306C10.5592 1.55027 10.625 1.70924 10.625 1.875V3.75ZM11.25 5H3.75V12.5H11.25V5ZM5.625 6.875H6.875V10.625H5.625V6.875ZM8.125 6.875H9.375V10.625H8.125V6.875ZM5.625 2.5V3.75H9.375V2.5H5.625Z"
                                                        fill="#EF1463" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_248_3811">
                                                        <rect width="15" height="15" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                            @lang('messages.delete')
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endcomponent
                    </div>
                    @if ($loop->iteration % 3 == 0)
                        <div class="clearfix"></div>
                    @endif
                @empty
                    <div class="col-md-12">
                        <div class="" style="background: #FFFFFF; text-align: center">
                            <h3 style="font-weight: 500;font-size: 24px;  color: #A0A0A0;">
                                @lang('cms::lang.not_found_please_add_one')
                            </h3>
                        </div>
                    </div>
                @endforelse
            </div>
        @endcomponent
    </section>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('click', 'button.delete_page', function() {
                var page_box = $(this).closest('.page-box');
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    page_box.remove();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
        })
    </script>
@endsection
