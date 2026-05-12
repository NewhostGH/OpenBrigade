<?php

namespace App\Services\Legacy;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LegacyMenuService
{
    public function getTopGroups(?User $user): array
    {
        $rows = DB::table('menu_group as mg')
            ->join('menu_item as mi', 'mi.MG_CODE', '=', 'mg.MG_CODE')
            ->leftJoin('menu_condition as mc', 'mc.MC_CODE', '=', 'mi.MI_CODE')
            ->select(
                'mg.MG_CODE',
                'mg.MG_NAME',
                'mg.MG_ICON',
                'mg.MG_IS_LEFT',
                'mg.MG_ORDER',
                'mi.MI_NAME',
                'mi.MI_URL',
                'mi.MI_ICON',
                'mi.MI_ORDER',
                'mc.MC_TYPE',
                'mc.MC_VALUE'
            )
            ->where('mi.MI_CODE', '<>', 'NOTES')
            ->where(function ($query): void {
                $query->whereNull('mg.MG_IS_LEFT')->orWhere('mg.MG_IS_LEFT', 0);
            })
            ->orderBy('mg.MG_ORDER')
            ->orderBy('mi.MI_ORDER')
            ->get();

        return $this->buildGroups($rows->all(), $user);
    }

    public function getLeftGroups(?User $user): array
    {
        $rows = DB::table('menu_group as mg')
            ->join('menu_item as mi', 'mi.MG_CODE', '=', 'mg.MG_CODE')
            ->leftJoin('menu_condition as mc', 'mc.MC_CODE', '=', 'mi.MI_CODE')
            ->select(
                'mg.MG_CODE',
                'mg.MG_NAME',
                'mg.MG_ICON',
                'mg.MG_ORDER',
                'mi.MI_NAME',
                'mi.MI_URL',
                'mi.MI_ICON',
                'mi.MI_ORDER',
                'mc.MC_TYPE',
                'mc.MC_VALUE'
            )
            ->where('mg.MG_IS_LEFT', 1)
            ->orderBy('mg.MG_ORDER')
            ->orderBy('mi.MI_ORDER')
            ->get();

        return $this->buildGroups($rows->all(), $user);
    }

    private function buildGroups(array $rows, ?User $user): array
    {
        $groups = [];

        foreach ($rows as $row) {
            if (! $this->isAllowed($row->MC_TYPE ?? '', $row->MC_VALUE ?? null, $user)) {
                continue;
            }

            $code = (string) $row->MG_CODE;
            if (! isset($groups[$code])) {
                $groups[$code] = [
                    'name' => (string) ($row->MG_NAME ?? ''),
                    'icon' => (string) ($row->MG_ICON ?? ''),
                    'items' => [],
                ];
            }

            $name = (string) ($row->MI_NAME ?? '');
            $rawUrl = (string) ($row->MI_URL ?? '');
            if ($name === '' || strtolower($name) === 'divider' || $rawUrl === '') {
                continue;
            }

            $isExternal = str_starts_with($rawUrl, 'http');
            if ($isExternal) {
                $normalizedUrl = $rawUrl;
            } elseif (str_starts_with($rawUrl, '/index.php/')) {
                $normalizedUrl = $rawUrl;
            } elseif (str_starts_with($rawUrl, 'index.php/')) {
                $normalizedUrl = '/' . $rawUrl;
            } else {
                $normalizedUrl = '/index.php/' . ltrim($rawUrl, '/');
            }

            $groups[$code]['items'][] = [
                'name' => $name,
                'icon' => (string) ($row->MI_ICON ?? ''),
                'url' => $normalizedUrl,
                'external' => $isExternal,
            ];
        }

        return $groups;
    }

    private function isAllowed(string $type, mixed $value, ?User $user): bool
    {
        if ($type === 'permission' && $user && method_exists($user, 'hasPermission')) {
            return $user->hasPermission((int) $value);
        }

        if ($type === 'not_permission' && $user && method_exists($user, 'hasPermission')) {
            return ! $user->hasPermission((int) $value);
        }

        return true;
    }
}
