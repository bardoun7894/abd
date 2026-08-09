{{--
    Per-page attachment preview for the review / fix screens.

    $url is InvoiceController::imageUrl() output. It is NOT always an image: when
    poppler is unavailable on the host (exec() disabled) the pipeline falls back to
    the source document plus a page fragment — "…/file/source.pdf#page=64" — so an
    <img> would render a broken icon. Embed those in the browser's PDF viewer, which
    honours the #page fragment and lands on the right invoice.
--}}
@if (! $url)
    <div class="text-muted text-center border rounded py-10">لا توجد صورة للصفحة</div>
@elseif (preg_match('/\.pdf($|[?#])/i', $url))
    <iframe src="{{ $url }}" class="w-100 rounded border" style="height:520px" title="مرفق الفاتورة" loading="lazy"></iframe>
    <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-light-primary w-100 mt-2">📄 فتح المرفق في تبويب جديد</a>
@else
    <img src="{{ $url }}" loading="lazy" class="inv-thumb w-100 rounded border" data-full="{{ $url }}" style="cursor:zoom-in" title="اضغط للتكبير">
@endif
