<?php

use App\Exceptions\UploadRejectedException;
use App\Services\SecuritySettingService;
use App\Services\UploadSecurityService;
use Illuminate\Http\UploadedFile;

/**
 * Pure-logic tests for the upload safety gate. A fake SecuritySettingService
 * lets us exercise MIME hardening without a database; ClamAV scanning is left
 * off so no daemon is required.
 */
function fakeUploadSettings(array $overrides = []): SecuritySettingService
{
    return new class($overrides) extends SecuritySettingService
    {
        public function __construct(private array $overrides) {}

        public function get(string $name): string
        {
            return (string) ($this->overrides[$name] ?? parent::default($name));
        }
    };
}

function uploadService(array $overrides = []): UploadSecurityService
{
    return new UploadSecurityService(fakeUploadSettings($overrides));
}

/** Write a temp file with given bytes and wrap it as a fake upload. */
function fakeUpload(string $name, string $bytes): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'obtest');
    file_put_contents($path, $bytes);

    return new UploadedFile($path, $name, null, null, true);
}

it('accepts a genuine PNG with a png extension', function () {
    // 8-byte PNG signature is enough for the finfo check.
    $png = "\x89PNG\r\n\x1a\n".str_repeat("\0", 64);
    $file = fakeUpload('photo.png', $png);

    uploadService()->validate($file, ['png', 'jpg'], 1024);
})->throwsNoExceptions();

it('rejects an executable renamed to .png by magic bytes', function () {
    $pe = 'MZ'.str_repeat("\0", 64); // DOS/PE header
    $file = fakeUpload('payload.png', $pe);

    uploadService()->validate($file, ['png', 'jpg'], 1024);
})->throws(UploadRejectedException::class);

it('rejects a forbidden extension even when whitelisted nowhere', function () {
    $file = fakeUpload('shell.php', '<?php echo 1;');

    uploadService()->validate($file, ['php'], 1024);
})->throws(UploadRejectedException::class);

it('rejects a double extension hiding php', function () {
    $png = "\x89PNG\r\n\x1a\n".str_repeat("\0", 16);
    $file = fakeUpload('avatar.php.png', $png);

    uploadService()->validate($file, ['png'], 1024);
})->throws(UploadRejectedException::class);

it('rejects a file over the size limit', function () {
    $file = fakeUpload('big.png', str_repeat('x', 4096));

    uploadService()->validate($file, ['png'], 2); // 2 KB limit, file is 4 KB
})->throws(UploadRejectedException::class);

it('skips mime hardening when the toggle is off', function () {
    $pe = 'MZ'.str_repeat("\0", 64);
    $file = fakeUpload('payload.png', $pe);

    // mime hardening off → magic bytes are not inspected
    uploadService(['sec_upload_mime_hardening' => '0'])->validate($file, ['png'], 1024);
})->throwsNoExceptions();
