<?php

namespace App\Traits;

//use App\Models\AdvancedSetting;
//use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Libraries\DriveHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadFile
{
    /**
     * Upload file to storage
     *
     * @param string $file
     * @param string $path
     * @return string
     */

//    public function uploadFile($path, $file, $disk = null): string
    public function uploadFile($file)
    {
      
        $driveHelper = new DriveHelper();
        //one file
        $uploadRes = $driveHelper->sendFile(getIp(), session('user_id'), $file, request()->userAgent());
        $fileCode = '';
        if ($uploadRes['status']['code'] == 200) {
            $fileCode = $uploadRes['data'][0]['file_code'];
             
        }
        return $fileCode;

    }

    /**
     * Delete file from storage
     *
     * @param string $file
     * @param string $path
     * @return void
     */
    public function deleteFile(string $path, string $file): void
    {
        if (File::exists($path . $file)) {
            File::delete($path . $file);
        }
    }
}
