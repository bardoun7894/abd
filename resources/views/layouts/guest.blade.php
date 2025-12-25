<!DOCTYPE html>
<html lang="ar" style="direction: rtl;">
	<head><base href="../../../">
		<title>شركة عبدالله سعيد ال هنيدي للمقاولات</title>
		<meta name="description" content="شركة عبدالله سعيد ال هنيدي للمقاولات" />
		<meta name="keywords" content="شركة عبدالله سعيد ال هنيدي للمقاولات" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="utf-8" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="شركة عبدالله سعيد ال هنيدي للمقاولات" />
		<meta property="og:url" content="" />
		<meta property="og:site_name" content="شركة عبدالله سعيد ال هنيدي للمقاولات" />
		<link rel="canonical" href="" />
		<link rel="shortcut icon" href="{{asset('assets/media/logos/logo.png')}}" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
        <link href="{{asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/fonts/dinnext/styles.rtl.css')}}" rel="stylesheet" type="text/css" />
	</head>
	<body id="kt_body" class="bg-body">
		<div class="d-flex flex-column flex-root">
			<div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed" >
				<div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
					<a  class="mb-12">
						<img alt="Logo" src="{{asset('assets/media/logos/logo.jpg')}} " class="h-150px"  style="width: 200px ; height: 200px"/>
					</a>
					<div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 ">
                {{ $slot }}
        </div>
    </div>
</div>
</div>
<div class="sidebar-menu">
    <li class="sidebar-item">
        <a href="{{ route('tasks.index') }}" class='sidebar-link'>
            <i class="bi bi-list-task"></i>
            <span>المهام</span>
        </a>
    </li>
</div>
<script>var hostUrl = "assets/";</script>
<script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
<script src="{{asset('assets/js/custom/authentication/sign-in/general.js')}}"></script>
</body>
</html>
