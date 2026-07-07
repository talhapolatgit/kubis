<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'legacy_no',
        'slug',
        'label',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'legacy_no'  => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission')
            ->using(UserPermission::class)
            ->withPivot(['granted_by', 'created_at', 'updated_at']);
    }

    /**
     * config/permissions.php içeriğinden seed verisi üretir.
     *
     * @return list<array{legacy_no: int, slug: string, label: string, sort_order: int}>
     */
    public static function definitionsFromConfig(): array
    {
        $rows = [];
        foreach (config('permissions.permissions', []) as $legacyNo => $def) {
            $rows[] = [
                'legacy_no'  => (int) $legacyNo,
                'slug'       => $def['slug'],
                'label'      => $def['label'],
                'sort_order' => (int) $legacyNo,
            ];
        }

        return $rows;
    }

    /**
     * Arayüz için gruplanmış yetki listesi.
     *
     * @param  \Illuminate\Support\Collection<int, Permission>  $permissionsByLegacyNo
     * @return list<array<string, mixed>>
     */
    public static function groupedForUi($permissionsByLegacyNo): array
    {
        $groups = [];

        foreach (config('permissions.groups', []) as $key => $group) {
            $entry = [
                'key'         => $key,
                'title'       => $group['title'],
                'description' => $group['description'] ?? null,
                'subsections' => [],
                'permissions' => collect(),
            ];

            if (! empty($group['sections'])) {
                foreach ($group['sections'] as $sectionKey => $section) {
                    $items = collect($section['permissions'] ?? [])
                        ->map(fn ($legacyNo) => $permissionsByLegacyNo->get((int) $legacyNo))
                        ->filter()
                        ->values();

                    if ($items->isNotEmpty()) {
                        $entry['subsections'][] = [
                            'key'         => $sectionKey,
                            'title'       => $section['title'] ?? $sectionKey,
                            'description' => $section['description'] ?? null,
                            'permissions' => $items,
                        ];
                    }
                }
            } else {
                $entry['permissions'] = collect($group['permissions'] ?? [])
                    ->map(fn ($legacyNo) => $permissionsByLegacyNo->get((int) $legacyNo))
                    ->filter()
                    ->values();
            }

            $hasContent = $entry['permissions']->isNotEmpty() || ! empty($entry['subsections']);
            if ($hasContent) {
                $groups[] = $entry;
            }
        }

        return $groups;
    }

    public static function groupTitleForLegacyNo(int $legacyNo): ?string
    {
        $groupKey = config("permissions.permissions.{$legacyNo}.group");
        if (! $groupKey) {
            return null;
        }

        return config("permissions.groups.{$groupKey}.title");
    }

    public static function subsectionTitleForLegacyNo(int $legacyNo): ?string
    {
        $def = config("permissions.permissions.{$legacyNo}");
        if (! $def) {
            return null;
        }

        if (! empty($def['section'])) {
            $groupKey = $def['group'] ?? null;
            if ($groupKey) {
                return config("permissions.groups.{$groupKey}.sections.{$def['section']}.title");
            }
        }

        return null;
    }

    public static function breadcrumbForLegacyNo(int $legacyNo): ?string
    {
        $group = static::groupTitleForLegacyNo($legacyNo);
        $subsection = static::subsectionTitleForLegacyNo($legacyNo);

        if ($group && $subsection) {
            return "{$group} · {$subsection}";
        }

        return $group;
    }
}
