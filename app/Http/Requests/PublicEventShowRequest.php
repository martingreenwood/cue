<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domains\Events\Data\PublicPerformanceFiltersData;
use App\Domains\Events\Enums\FilterGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class PublicEventShowRequest extends FormRequest
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
            'performance' => ['nullable', 'integer', 'min:1'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'when' => ['nullable', Rule::in(['all', 'today', 'this-week', 'this-month'])],
            'access' => ['nullable', 'array', 'max:20'],
            'access.*' => ['string', 'distinct', Rule::exists('filter_terms', 'slug')->where('filter_group', FilterGroup::Access->value)],
        ];
    }

    public function performanceId(): ?int
    {
        $performanceId = (int) ($this->validated()['performance'] ?? 0);

        return $performanceId > 0 ? $performanceId : null;
    }

    public function filters(): PublicPerformanceFiltersData
    {
        $validated = $this->validated();

        return new PublicPerformanceFiltersData(
            date: isset($validated['date']) ? (string) $validated['date'] : null,
            dateWindow: (string) ($validated['when'] ?? 'all'),
            access: $this->slugs($validated['access'] ?? []),
        );
    }

    /**
     * @return list<string>
     */
    private function slugs(mixed $values): array
    {
        return array_values(array_map(
            static fn (mixed $value): string => (string) $value,
            Arr::wrap($values),
        ));
    }
}
