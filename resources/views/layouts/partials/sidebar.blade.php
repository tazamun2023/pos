<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <div>
            <a href="{{ route('home') }}" class="logo" >
                <span class="logo-lg">{{ Session::get('business.name') }}</span>
                
            </a>
          
        </div>

        <!-- Sidebar Menu -->
        {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
