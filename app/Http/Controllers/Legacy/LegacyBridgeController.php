<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LegacyBridgeController extends Controller
{
    public function asset(Request $request, string $assetType, string $assetPath): Response
    {
        $allowedTypes = ['css', 'js', 'images', 'webfonts'];
        if (! in_array($assetType, $allowedTypes, true)) {
            abort(404);
        }

        $normalizedPath = str_replace(['\\', '..'], ['/', ''], $assetPath);
        $normalizedPath = ltrim($normalizedPath, '/');
        if ($normalizedPath === '') {
            abort(404);
        }

        $legacyRoot = $this->resolveLegacyRoot((string) config('legacy_bridge.legacy_root', 'archive/legacy_app'));
        $assetFullPath = $legacyRoot . DIRECTORY_SEPARATOR . $assetType . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);

        if (! is_file($assetFullPath)) {
            abort(404);
        }

        $extension = strtolower((string) pathinfo($assetFullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'map' => 'application/json; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'eot' => 'application/vnd.ms-fontobject',
        ];

        $headers = [
            'Cache-Control' => 'public, max-age=3600',
        ];
        if (isset($mimeTypes[$extension])) {
            $headers['Content-Type'] = $mimeTypes[$extension];
        }

        return response()->file($assetFullPath, $headers);
    }

    public function show(Request $request): Response
    {
        $legacyFile = basename((string) $request->path());
        $routeLegacyFile = (string) $request->route('legacyFile', '');
        if ($routeLegacyFile !== '') {
            $legacyFile = basename($routeLegacyFile);
        }

        if ($legacyFile === 'configuration_db.php' || $legacyFile === '_configuration_db.php') {
            return $request->user()
                ? redirect()->route('dashboard')
                : redirect()->route('login');
        }

        $manifest = config('legacy_bridge.pages', []);

        if (! isset($manifest[$legacyFile]) || empty($manifest[$legacyFile]['bridgeable'])) {
            abort(404);
        }

        $requiredPermission = $manifest[$legacyFile]['permission'] ?? null;

        if ($requiredPermission !== null) {
            $user = $request->user();
            if (! $user || ! method_exists($user, 'hasPermission') || ! $user->hasPermission((int) $requiredPermission)) {
                abort(403);
            }
        }

        $legacyRoot = $this->resolveLegacyRoot((string) config('legacy_bridge.legacy_root', 'archive/legacy_app'));
        $legacyPath = $legacyRoot . DIRECTORY_SEPARATOR . $legacyFile;

        if (! is_file($legacyPath)) {
            abort(404);
        }

        $this->bootstrapLegacyRequest($request, $legacyFile);

        $originalCwd = getcwd() ?: null;
        $headersBefore = headers_list();

        try {
            chdir($legacyRoot);
            ob_start();
            include $legacyPath;
            $content = ob_get_clean() ?: '';
        } finally {
            if ($originalCwd !== null) {
                chdir($originalCwd);
            }
        }

        $headers = $this->collectLegacyHeaders($headersBefore);

        // Rewrite any ISO-8859-1 charset declarations in the HTML body
        // (e.g. <meta http-equiv='Content-Type' content='text/html; charset=ISO-8859-1'>)
        $content = (string) preg_replace(
            '/charset=iso-8859-[0-9]+/i',
            'charset=UTF-8',
            $content
        );

        return response($content, 200, $headers);
    }

    private function bootstrapLegacyRequest(Request $request, string $legacyFile): void
    {
        $_GET = $request->query();
        $_POST = $request->post();
        $_REQUEST = array_merge($_GET, $_POST);
        $_COOKIE = $request->cookies->all();

        $this->hydrateLegacySession($request->user());

        $_SERVER['PHP_SELF'] = '/' . $legacyFile;
        $_SERVER['SCRIPT_NAME'] = '/' . $legacyFile;
        $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        $_SERVER['QUERY_STRING'] = (string) $request->server('QUERY_STRING', '');
    }

    private function hydrateLegacySession(?User $user): void
    {
        if ($user === null) {
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (isset($_COOKIE['PHPSESSID']) && is_string($_COOKIE['PHPSESSID'])) {
                session_id($_COOKIE['PHPSESSID']);
            }
            @session_start();
        }

        $sectionId = (int) ($user->P_SECTION ?? 0);
        $sectionParent = (int) DB::table('section')
            ->where('S_ID', $sectionId)
            ->value('S_PARENT');

        $_SESSION['id'] = (int) $user->P_ID;
        $_SESSION['groupe'] = (int) ($user->GP_ID ?? 0);
        $_SESSION['groupe2'] = (int) ($user->GP_ID2 ?? $user->GP_ID ?? 0);
        $_SESSION['SES_NOM'] = (string) ($user->P_NOM ?? '');
        $_SESSION['SES_PRENOM'] = (string) ($user->P_PRENOM ?? '');
        $_SESSION['SES_EMAIL'] = (string) ($user->P_EMAIL ?? '');
        $_SESSION['SES_GRADE'] = (string) ($user->P_GRADE ?? '');
        $_SESSION['SES_STATUT'] = (string) ($user->P_STATUT ?? '');
        $_SESSION['SES_SECTION'] = $sectionId;
        $_SESSION['SES_PARENT'] = $sectionParent > 0 ? $sectionParent : $sectionId;
        $_SESSION['SES_FAVORITE'] = (int) ($user->P_FAVORITE_SECTION ?? $sectionId);
        $_SESSION['SES_COMPANY'] = (int) ($user->C_ID ?? 0);
        $_SESSION['SES_DEBUT'] = (string) ($_SESSION['SES_DEBUT'] ?? date('Y-m-d H:i:s'));
        $_SESSION['SES_BROWSER'] = (string) ($_SESSION['SES_BROWSER'] ?? substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'bridge'), 0, 250));
        $_SESSION['SES_NBS'] = (int) ($_SESSION['SES_NBS'] ?? 0);
        $_SESSION['LAST_ACTIVITY'] = time();

        // Legacy helpers frequently read these via `global $id`, etc.
        $GLOBALS['id'] = $_SESSION['id'];
        $GLOBALS['groupe'] = $_SESSION['groupe'];
        $GLOBALS['SES_NOM'] = $_SESSION['SES_NOM'];
        $GLOBALS['SES_PRENOM'] = $_SESSION['SES_PRENOM'];
        $GLOBALS['SES_GRADE'] = $_SESSION['SES_GRADE'];
        $GLOBALS['SES_STATUT'] = $_SESSION['SES_STATUT'];
        $GLOBALS['SES_SECTION'] = $_SESSION['SES_SECTION'];
        $GLOBALS['SES_PARENT'] = $_SESSION['SES_PARENT'];
        $GLOBALS['SES_COMPANY'] = $_SESSION['SES_COMPANY'];
    }

    private function collectLegacyHeaders(array $headersBefore): array
    {
        $headersAfter = headers_list();
        $legacyHeaders = array_values(array_diff($headersAfter, $headersBefore));
        $parsed = [];

        foreach ($legacyHeaders as $headerLine) {
            $parts = explode(':', $headerLine, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if ($name !== '') {
                $parsed[$name] = $value;
            }
        }

        // Always serve HTML as UTF-8, regardless of what the legacy code declared
        if (! isset($parsed['Content-Type']) || stripos($parsed['Content-Type'], 'text/html') !== false) {
            $parsed['Content-Type'] = 'text/html; charset=UTF-8';
        } elseif (stripos($parsed['Content-Type'], 'charset=') !== false) {
            $parsed['Content-Type'] = (string) preg_replace('/charset=iso-8859-[0-9]+/i', 'charset=UTF-8', $parsed['Content-Type']);
        }

        return $parsed;
    }

    private function resolveLegacyRoot(string $configuredPath): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($configuredPath));

        if (! $this->isAbsolutePath($normalized)) {
            $normalized = base_path($normalized);
        }

        return rtrim($normalized, DIRECTORY_SEPARATOR);
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        // Unix absolute path.
        if ($path[0] === '/' || $path[0] === '\\') {
            return true;
        }

        // Windows drive letter path (e.g. C:\folder).
        return (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $path);
    }
}
