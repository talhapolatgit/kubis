<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

final class TurkishSearch
{
    private const COLLATE = 'utf8mb4_turkish_ci';

    /**
     * Türkçe büyük/küyük harf kurallarına göre LIKE araması (I↔ı, İ↔i).
     *
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public static function whereLike(Builder $query, string $column, string $value): Builder
    {
        $value = trim($value);
        if ($value === '') {
            return $query;
        }

        return $query->whereRaw(
            $query->qualifyColumn($column) . ' COLLATE ' . self::COLLATE . ' LIKE ?',
            ['%' . $value . '%']
        );
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  list<string>  $columns
     */
    public static function whereLikeAny(Builder $query, array $columns, string $value): Builder
    {
        $value = trim($value);
        if ($value === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($columns, $value): void {
            foreach ($columns as $column) {
                $q->orWhereRaw(
                    $q->qualifyColumn($column) . ' COLLATE ' . self::COLLATE . ' LIKE ?',
                    ['%' . $value . '%']
                );
            }
        });
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    public static function applyTextMatch(
        Builder $query,
        string $column,
        string $value,
        string $mode = 'contains',
        string $boolean = 'and'
    ): void {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        $qualified = $query->qualifyColumn($column);

        if ($mode === 'exact') {
            $sql = $qualified . ' COLLATE ' . self::COLLATE . ' = ?';
            $bindings = [$value];
        } elseif ($mode === 'starts_with') {
            $sql = $qualified . ' COLLATE ' . self::COLLATE . ' LIKE ?';
            $bindings = [$value . '%'];
        } else {
            $sql = $qualified . ' COLLATE ' . self::COLLATE . ' LIKE ?';
            $bindings = ['%' . $value . '%'];
        }

        if ($boolean === 'or') {
            $query->orWhereRaw($sql, $bindings);

            return;
        }

        $query->whereRaw($sql, $bindings);
    }
}
