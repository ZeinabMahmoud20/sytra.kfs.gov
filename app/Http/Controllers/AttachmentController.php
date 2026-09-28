<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * عرض / تحميل مرفق واحد.
     *
     * الخدمة تتم عبر راوتر محمي بدل الاعتماد على public/storage symlink،
     * لأن السيرفر غالباً لا ينشئ الـ symlink فيرجع 403 Forbidden.
     */
    public function show(Attachment $attachment): StreamedResponse
    {
        $disk = Storage::disk('public');

        abort_unless(
            $attachment->FilePath && $disk->exists($attachment->FilePath),
            404
        );

        $name = ($attachment->AttachmentName ?: 'attachment')
            . ($attachment->FileExtension ? '.' . $attachment->FileExtension : '');

        return $disk->response(
            $attachment->FilePath,
            $name,
            ['X-Content-Type-Options' => 'nosniff'],
            $this->isInlineable($attachment->FileExtension) ? 'inline' : 'attachment',
        );
    }

    /**
     * الصور و PDF تُعرض داخل المتصفح، أي نوع تاني ينزل كتحميل.
     */
    protected function isInlineable(?string $extension): bool
    {
        return in_array(
            strtolower((string) $extension),
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'pdf'],
            true
        );
    }
}
