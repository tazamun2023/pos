@php
  $all_notifications = auth()->user()->notifications;
  $unread_notifications = $all_notifications->where('read_at', null);
  $total_unread = count($unread_notifications);
@endphp
<!-- Notifications: style can be found in dropdown.less -->
{{-- <li class="dropdown notifications-menu">
  <a href="#" class="dropdown-toggle load_notifications" data-toggle="dropdown" id="show_unread_notifications" data-loaded="false">
    <i class="fas fa-bell"></i>
    <span class="label label-warning notifications_count">@if(!empty($total_unread)){{$total_unread}}@endif</span>
  </a>
  <ul class="dropdown-menu">
    <!-- <li class="header">You have 10 unread notifications</li> -->
    <li>
      <!-- inner menu: contains the actual data -->

      <ul class="menu" id="notifications_list">
      </ul>
    </li>
    
    @if(count($all_notifications) > 10)
      <li class="footer load_more_li">
        <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
      </li>
    @endif
  </ul>
</li> --}}
<li class="dropdown notifications-menu">
  <a href="#" class="dropdown-toggle load_notifications f_notifications" data-toggle="dropdown" id="show_unread_notifications" data-loaded="false">
    {{-- <i class="fas fa-bell"></i> --}}
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <g clip-path="url(#clip0_83_2423)">
      <path d="M20 17H22V19H2V17H4V10C4 7.87827 4.84286 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10V17ZM18 17V10C18 8.4087 17.3679 6.88258 16.2426 5.75736C15.1174 4.63214 13.5913 4 12 4C10.4087 4 8.88258 4.63214 7.75736 5.75736C6.63214 6.88258 6 8.4087 6 10V17H18ZM9 21H15V23H9V21Z" fill="#A0A0A0"/>
      </g>
      <defs>
      <clipPath id="clip0_83_2423">
      <rect width="24" height="24" fill="white"/>
      </clipPath>
      </defs>
      </svg>
      
    <span class="label label-warning notifications_count">@if(!empty($total_unread)){{$total_unread}}@endif</span>
  </a>
  <ul class="dropdown-menu">
    <!-- <li class="header">You have 10 unread notifications</li> -->
    <li>
      <!-- inner menu: contains the actual data -->

      <ul class="menu" id="notifications_list">
      </ul>
    </li>
    
    @if(count($all_notifications) > 10)
      <li class="footer load_more_li">
        <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
      </li>
    @endif
  </ul>
</li>
<input type="hidden" id="notification_page" value="1">