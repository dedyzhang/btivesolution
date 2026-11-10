<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Helper kecil untuk tabel yang bisa di-sort (klik header) + berpaginasi,
 * dipakai bareng komponen <x-sortable-th>.
 */
class TableSort
{
    /** Kolom sort dari request, divalidasi terhadap whitelist; balikan [kolom, arah]. */
    public static function resolve(Request $request, array $allowed, string $default, string $defaultDir = 'asc'): array
    {
        $sort = in_array($request->query('sort'), $allowed, true) ? $request->query('sort') : $default;
        $dir = $request->query('dir') ?: $defaultDir;
        $dir = $dir === 'desc' ? 'desc' : 'asc';
        return [$sort, $dir];
    }

    /**
     * Paginasi manual atas Collection yang SUDAH diurutkan di PHP (dipakai untuk
     * kolom hasil hitung seperti "sisa poin" yang tak bisa di-ORDER BY di database).
     */
    public static function paginateCollection(Collection $items, int $perPage = 20): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage('page');
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($slice, $items->count(), $perPage, $page, [
            'path'  => Paginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);
    }
}
