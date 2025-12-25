<div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-image:url('assets/media/misc/pattern-1.jpg')">
    <h3 class="text-white fw-bold px-9 mt-10 mb-6">الاشعارات
    <span class="fs-8 opacity-75 ps-3">{{ $count_list }} اشعار</span></h3>
    <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-bold px-9">
        <li class="nav-item">
            <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab" href="#kt_topbar_notifications_1">اخر اشعارات</a>
        </li>
    </ul>
</div>
<div class="tab-content">
    <div class="tab-pane fade active show" id="kt_topbar_notifications_1" role="tabpanel">
        <div class="scroll-y mh-325px my-5 px-8">


            <?php
              foreach ($listmoraslat as $x) {
                        if ($x->is_read == '0') {
                    $is_read_desc = 'غير مقروء';
                } else if ($x->is_read == '1') {
                    $is_read_desc = 'مقروء';
                } else {
                    $is_read_desc = 'غير مدخل';
                }
                $moraslat_status_name =  $x->moraslat_status_name;


                if ($x->create_user == Auth::user()->id) {
                    $type_desc = 'صادر';
                } else if ($x->user_id == Auth::user()->id) {
                    $type_desc = 'وارد';
                } else {
                    $type_desc = '';
                }

                        ?>



            <div class="d-flex flex-stack py-4">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-35px me-4">
                        <span class="symbol-label bg-light-primary">
                            <span class="svg-icon svg-icon-2  @if ($x->create_user == Auth::user()->id)  svg-icon-success  @else svg-icon-danger  @endif">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3" d="M11 6.5C11 9 9 11 6.5 11C4 11 2 9 2 6.5C2 4 4 2 6.5 2C9 2 11 4 11 6.5ZM17.5 2C15 2 13 4 13 6.5C13 9 15 11 17.5 11C20 11 22 9 22 6.5C22 4 20 2 17.5 2ZM6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13ZM17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13Z" fill="black"></path>
                                    <path d="M17.5 16C17.5 16 17.4 16 17.5 16L16.7 15.3C16.1 14.7 15.7 13.9 15.6 13.1C15.5 12.4 15.5 11.6 15.6 10.8C15.7 9.99999 16.1 9.19998 16.7 8.59998L17.4 7.90002H17.5C18.3 7.90002 19 7.20002 19 6.40002C19 5.60002 18.3 4.90002 17.5 4.90002C16.7 4.90002 16 5.60002 16 6.40002V6.5L15.3 7.20001C14.7 7.80001 13.9 8.19999 13.1 8.29999C12.4 8.39999 11.6 8.39999 10.8 8.29999C9.99999 8.19999 9.20001 7.80001 8.60001 7.20001L7.89999 6.5V6.40002C7.89999 5.60002 7.19999 4.90002 6.39999 4.90002C5.59999 4.90002 4.89999 5.60002 4.89999 6.40002C4.89999 7.20002 5.59999 7.90002 6.39999 7.90002H6.5L7.20001 8.59998C7.80001 9.19998 8.19999 9.99999 8.29999 10.8C8.39999 11.5 8.39999 12.3 8.29999 13.1C8.19999 13.9 7.80001 14.7 7.20001 15.3L6.5 16H6.39999C5.59999 16 4.89999 16.7 4.89999 17.5C4.89999 18.3 5.59999 19 6.39999 19C7.19999 19 7.89999 18.3 7.89999 17.5V17.4L8.60001 16.7C9.20001 16.1 9.99999 15.7 10.8 15.6C11.5 15.5 12.3 15.5 13.1 15.6C13.9 15.7 14.7 16.1 15.3 16.7L16 17.4V17.5C16 18.3 16.7 19 17.5 19C18.3 19 19 18.3 19 17.5C19 16.7 18.3 16 17.5 16Z" fill="black"></path>
                                </svg>
                            </span>
                        </span>
                    </div>


                    <div class="mb-0 me-2">
                        <a href="{{ route('dashboard.moraslat.views') }}" class="fs-6 text-info text-hover-primary fw-bolder">{{ $x->moraslat_type_name }} - {{ $x->moraslat_categoty_name }}</a>
                        <div class="text-success fs-7 fw-bolder">{{ $x->moraslat_id }}-{{ $type_desc }}</div>
                        <div class="text-primary fs-7">{{ $moraslat_status_name }}</div>


                    </div>
                </div>
                <span class="badge badge-light-danger fs-8">{{ $is_read_desc }} </span>
            </div>


            <?php } ?>


        </div>
        <div class="py-3 text-center border-top">
            <a href="{{ route('dashboard.moraslat.views') }}" class="btn btn-color-dark btn-active-color-info">عرض الكل
            <span class="svg-icon svg-icon-5">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="18" y="13" width="13" height="2" rx="1" transform="rotate(-180 18 13)" fill="black"></rect>
                    <path d="M15.4343 12.5657L11.25 16.75C10.8358 17.1642 10.8358 17.8358 11.25 18.25C11.6642 18.6642 12.3358 18.6642 12.75 18.25L18.2929 12.7071C18.6834 12.3166 18.6834 11.6834 18.2929 11.2929L12.75 5.75C12.3358 5.33579 11.6642 5.33579 11.25 5.75C10.8358 6.16421 10.8358 6.83579 11.25 7.25L15.4343 11.4343C15.7467 11.7467 15.7467 12.2533 15.4343 12.5657Z" fill="black"></path>
                </svg>
            </span>
            </a>
        </div>
    </div>





</div>



<script>
$('#count_moraslat').html({{ $count_list }});
</script>
