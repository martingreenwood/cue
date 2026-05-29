<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domains\Events\Data\PublicEventFiltersData;
use App\Domains\Events\Enums\FilterGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class PublicEventIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'when' => ['nullable', Rule::in(['all', 'next-30-days', 'next-90-days'])],
            'what' => ['nullable', 'array', 'max:20'],
            'what.*' => ['string', 'distinct', Rule::exists('filter_terms', 'slug')->where('filter_group', FilterGroup::What->value)],
            'offers' => ['nullable', 'array', 'max:20'],
            'offers.*' => ['string', 'distinct', Rule::exists('filter_terms', 'slug')->where('filter_group', FilterGroup::Offers->value)],
            'access' => ['nullable', 'array', 'max:20'],
            'access.*' => ['string', 'distinct', Rule::exists('filter_terms', 'slug')->where('filter_group', FilterGroup::Access->value)],
        ];
    }

    public function filters(): PublicEventFiltersData
    {
        $validated = $this->validated();
        $query = Str::of((string) ($validated['q'] ?? ''))->squish()->toString();

        return new PublicEventFiltersData(
            query: $query !== '' ? $query : null,
            dateWindow: (string) ($validated['when'] ?? 'all'),
            what: $this->slugs($validated['what'] ?? []),
            offers: $this->slugs($validated['offers'] ?? []),
            access: $this->slugs($validated['access'] ?? []),
        );
    }

    /**
     * @return list<string>
     */
    private function slugs(mixed $slugs): array
    {
        return collect(Arr::wrap($slugs))
            ->filter(fn (mixed $slug): bool => is_string($slug))
            ->values()
            ->all();
    }
}
