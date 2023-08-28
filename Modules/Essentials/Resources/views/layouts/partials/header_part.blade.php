@if ($__is_essentials_enabled && $is_employee_allowed)

    <button type="button" class="
		clock_in_btn
		f_clock_in_btn
		@if (!empty($clock_in)) hide @endif
		"
        data-type="clock_in" data-toggle="tooltip" data-placement="bottom" title="@lang('essentials::lang.clock_in')">
        <svg height="30" viewBox="0 0 512 512" width="30" xmlns="http://www.w3.org/2000/svg">
            <path
                d="m256 0c-141.164062 0-256 114.835938-256 256s114.835938 256 256 256 256-114.835938 256-256-114.835938-256-256-256zm0 0"
                fill="#2196f3" />
            <path
                d="m385.75 201.75-138.667969 138.664062c-4.160156 4.160157-9.621093 6.253907-15.082031 6.253907s-10.921875-2.09375-15.082031-6.253907l-69.332031-69.332031c-8.34375-8.339843-8.34375-21.824219 0-30.164062 8.339843-8.34375 21.820312-8.34375 30.164062 0l54.25 54.25 123.585938-123.582031c8.339843-8.34375 21.820312-8.34375 30.164062 0 8.339844 8.339843 8.339844 21.820312 0 30.164062zm0 0"
                fill="#fafafa" />
        </svg>
    </button>

    <button type="button"
        class="f_clock_in_btn clock_out_btn
		@if (empty($clock_in)) hide @endif
		"
        data-type="clock_out" data-toggle="tooltip" data-placement="bottom" data-html="true"
        title="@lang('essentials::lang.clock_out') @if (!empty($clock_in)) <br>
                    <small>
                    	<b>@lang('essentials::lang.clocked_in_at'):</b> {{ @format_datetime($clock_in->clock_in_time) }}
                    </small>
                    <br>
                    <small><b>@lang('essentials::lang.shift'):</b> {{ ucfirst($clock_in->shift_name) }}</small>
                    @if (!empty($clock_in->start_time) && !empty($clock_in->end_time))
                    	<br>
                    	<small>
                    		<b>@lang('restaurant.start_time'):</b> {{ @format_time($clock_in->start_time) }}<br>
                    		<b>@lang('restaurant.end_time'):</b> {{ @format_time($clock_in->end_time) }}
                    	</small> @endif
                @endif">
			

				<svg height="30" viewBox="0 0 512 512" width="30" xmlns="http://www.w3.org/2000/svg"><path d="m416 512h-320c-53.023438 0-96-42.976562-96-96v-320c0-53.023438 42.976562-96 96-96h320c53.023438 0 96 42.976562 96 96v320c0 53.023438-42.976562 96-96 96zm0 0" fill="#fff9dd"/><path d="m256 128c-70.574219 0-128 57.425781-128 128s57.425781 128 128 128 128-57.425781 128-128-57.425781-128-128-128zm64.878906 100.878906-69.328125 69.328125c-2.078125 2.082031-4.816406 3.121094-7.535156 3.121094-2.734375 0-5.457031-1.039063-7.535156-3.121094l-34.671875-34.671875c-4.160156-4.160156-4.160156-10.910156 0-15.085937s10.910156-4.160157 15.085937 0l27.121094 27.117187 61.792969-61.789062c4.160156-4.160156 10.910156-4.160156 15.085937 0s4.144531 10.925781-.015625 15.101562zm0 0" fill="#ffd200"/></svg>
    </button>
@endif
