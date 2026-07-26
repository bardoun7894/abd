{{--
    Shared upload diagnostics for the AI PDF upload screens (invoices + leases).

    WHY THIS EXISTS (client feedback 2026-07-26: "ظهرت رسالة تقول إنه ليس ملف PDF،
    مع أنه هو ملف PDF"):

    Both upload views used to end their AJAX chain with a single blanket fallback —
    `showErr(x.responseJSON.message_out || 'فشل الرفع، تأكد أن الملف PDF')` — which
    fired on EVERY failed request. An expired CSRF token (419), a LiteSpeed timeout
    (the noor instance kills requests at 121s), a 500 in the queue worker, a dropped
    connection: all of them accused the user's file of not being a PDF. The client's
    file was a perfectly valid PDF 1.5; the message was simply wrong, and it sent
    them looking for a problem in the file instead of the request.

    Two changes:
      1. aiUploadError(x) maps the real HTTP status to an actionable Arabic message,
         and prefers any server-sent message_out / validation error over guessing.
      2. aiAssertPdf(file, cb) sniffs the first five bytes for the "%PDF-" magic
         number BEFORE uploading, so "this is not a PDF" is only ever said when it
         is actually true — and is said instantly, without a round trip.
--}}
<script>
    /**
     * Turn a failed jqXHR into a message that tells the user what to DO.
     * Server-supplied text always wins; the status map is the fallback.
     */
    function aiUploadError(x) {
        var r = (x && x.responseJSON) || {};
        if (r.message_out) { return r.message_out; }
        // Laravel validation bag: {"errors":{"pdf":["..."]}}
        if (r.errors) {
            for (var k in r.errors) {
                if (r.errors[k] && r.errors[k][0]) { return r.errors[k][0]; }
            }
        }
        switch (x && x.status) {
            case 0:
                return 'انقطع الاتصال بالخادم أثناء الرفع. تحقّق من الشبكة ثم أعد المحاولة.';
            case 401:
            case 419:
                return 'انتهت صلاحية الجلسة. حدّث الصفحة (F5)، سجّل الدخول من جديد، ثم أعد الرفع.';
            case 403:
                return 'ليست لديك صلاحية الرفع في هذه الشاشة.';
            case 413:
                return 'حجم الملف أكبر مما يقبله الخادم. قسّم الملف إلى أجزاء أصغر ثم أعد المحاولة.';
            case 422:
                return 'رُفض الملف: تأكد أنه ملف PDF صالح وأن حجمه لا يتجاوز 50 ميجابايت.';
            case 429:
                return 'تم تجاوز الحد المسموح مؤقتاً. انتظر دقيقة ثم أعد المحاولة.';
            case 500:
            case 502:
            case 503:
                return 'خطأ في الخادم أثناء معالجة الملف (رمز ' + x.status + '). أعد المحاولة، وإذا تكرر أبلغ الدعم الفني.';
            case 504:
                return 'استغرقت المعالجة وقتاً أطول من المسموح به على الخادم. جرّب ملفاً بعدد صفحات أقل.';
        }
        return 'فشل الرفع (رمز ' + ((x && x.status) || '؟') + '). أعد المحاولة، وإذا تكرر أبلغ الدعم الفني.';
    }

    /**
     * Verify the file's magic number before uploading, then continue.
     *
     * cb(errorMessageOrNull) — null means "go ahead".
     * allowImages mirrors what the target endpoint accepts: the invoices upload
     * takes a PDF *or* a scanned JPG/PNG/GIF/WEBP, the leases upload takes PDF
     * only. Getting this wrong would reject files the server would have accepted,
     * so it is passed explicitly by each caller rather than assumed.
     *
     * Falls through to cb(null) whenever the bytes cannot be read (old browser,
     * unreadable file), so this can only ever be a fast, friendly pre-check — the
     * server-side check in App\Support\UploadSignature stays authoritative.
     */
    function aiAssertUploadType(file, allowImages, cb) {
        if (!file || typeof FileReader === 'undefined') { cb(null); return; }
        var reader = new FileReader();
        reader.onloadend = function () {
            var bytes = new Uint8Array(reader.result || new ArrayBuffer(0));
            var magic = '';
            for (var i = 0; i < bytes.length; i++) { magic += String.fromCharCode(bytes[i]); }

            // Some generators emit junk before the header, so allow a small offset.
            if (magic.indexOf('%PDF-') >= 0) { cb(null); return; }

            if (allowImages) {
                var isJpeg = magic.charCodeAt(0) === 0xFF && magic.charCodeAt(1) === 0xD8 && magic.charCodeAt(2) === 0xFF;
                var isPng  = magic.indexOf('PNG') === 1;
                var isGif  = magic.indexOf('GIF8') === 0;
                var isWebp = magic.indexOf('RIFF') === 0 && magic.indexOf('WEBP') === 8;
                if (isJpeg || isPng || isGif || isWebp) { cb(null); return; }
                cb('هذا الملف ليس PDF ولا صورة صالحة («' + file.name + '»). قد يكون ملفاً تالفاً أو أُعيدت تسميته — أعد حفظه أو امسحه ضوئياً ثم أعد الرفع.');
                return;
            }

            cb('هذا الملف ليس PDF فعلياً («' + file.name + '»). قد يكون صورة أو ملفاً أُعيدت تسميته بامتداد pdf — احفظه أو امسحه ضوئياً كـ PDF ثم أعد الرفع.');
        };
        reader.onerror = function () { cb(null); }; // unreadable here — let the server decide
        reader.readAsArrayBuffer(file.slice(0, 1024));
    }
</script>
