<?php

namespace App\Services;

use App\Models\FormEntry;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\OtpInput;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores, updates and deletes the forms demo entries, including their
 * uploaded files and hashed secrets.
 */
class FormEntryService
{
    /**
     * Disk the uploaded files are stored on.
     */
    public const DISK = 'local';

    /**
     * Shown instead of a stored secret.
     */
    public const MASK = '••••••••';

    /**
     * @return LengthAwarePaginator<int, FormEntry>
     */
    public function paginate(string $demo, ?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return FormEntry::query()
            ->where('demo', $demo)
            ->when($search, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('data', 'like', "%{$search}%"),
            ))
            ->latest('updated_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Save the data validated by the form as a new entry.
     */
    public function create(string $demo, Form $form): FormEntry
    {
        $data = $this->prepare($form, $form->validated(), [], $demo);

        return FormEntry::create([
            'demo' => $demo,
            'title' => $this->title($form, $data),
            'data' => $data,
        ]);
    }

    /**
     * Replace the entry's data with the data validated by the form. Files and
     * secrets that were left empty keep their stored value.
     */
    public function update(FormEntry $entry, Form $form): FormEntry
    {
        $previous = $entry->data;
        $data = $this->prepare($form, $form->validated(), $previous, $entry->demo);

        $entry->update([
            'title' => $this->title($form, $data),
            'data' => $data,
        ]);

        $this->deleteFiles(array_diff($this->filePaths($previous), $this->filePaths($data)));

        return $entry;
    }

    /**
     * Delete the entry and its uploaded files.
     */
    public function delete(FormEntry $entry): void
    {
        $entry->delete();

        $this->deleteFiles($this->filePaths($entry->data));
    }

    /**
     * Values to bind to the edit form. Secrets are left out so their hashes
     * never reach the browser.
     *
     * @return array<string, mixed>
     */
    public function formValues(FormEntry $entry, Form $form): array
    {
        return Arr::except($entry->data, $this->secretFields($form));
    }

    /**
     * The stored data with secrets masked and files reduced to their names.
     *
     * @return array<string, mixed>
     */
    public function storedData(FormEntry $entry, Form $form): array
    {
        $data = $this->mapFiles($entry->data, fn (array $file): string => $file['name']);

        foreach ($this->secretFields($form) as $name) {
            if (filled(Arr::get($data, $name))) {
                Arr::set($data, $name, self::MASK);
            }
        }

        return $data;
    }

    /**
     * The entry grouped by fieldset, ready to be listed on the show page.
     *
     * @param  callable(array{name: string, path: string, size: int}): string  $fileUrl
     * @return array<int, array{title: string|null, rows: array<int, array{label: string, type: string, value: string|null, files: array<int, array{name: string, size: int, url: string}>}>}>
     */
    public function present(FormEntry $entry, Form $form, callable $fileUrl): array
    {
        $secrets = $this->secretFields($form);
        $sections = [];

        foreach ($form->getFieldsets() as $fieldset) {
            $rows = [];

            foreach ($fieldset->getFields() as $field) {
                if (! $field->hasValue() || ! Arr::has($entry->data, $field->getName())) {
                    continue;
                }

                $rows[] = [
                    'label' => $field->getLabel(),
                    ...$this->presentValue(
                        Arr::get($entry->data, $field->getName()),
                        in_array($field->getName(), $secrets, true),
                        $fileUrl,
                    ),
                ];
            }

            if ($rows !== []) {
                $sections[] = ['title' => $fieldset->toArray()['legend'] ?? null, 'rows' => $rows];
            }
        }

        return $sections;
    }

    /**
     * The stored file at the given path, when it belongs to the entry.
     *
     * @return array{name: string, path: string, size: int}|null
     */
    public function file(FormEntry $entry, string $path): ?array
    {
        return collect($this->files($entry->data))->firstWhere('path', $path);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $previous
     * @return array<string, mixed>
     */
    protected function prepare(Form $form, array $data, array $previous, string $demo): array
    {
        $data = $this->storeFiles($data, $previous, "form-entries/{$demo}");

        foreach ($this->secretFields($form) as $name) {
            if (! Arr::has($data, $name)) {
                continue;
            }

            $value = Arr::get($data, $name);

            if (filled($value)) {
                Arr::set($data, $name, Hash::make((string) $value));
            } elseif (filled(Arr::get($previous, $name))) {
                Arr::set($data, $name, Arr::get($previous, $name));
            }
        }

        return $data;
    }

    /**
     * Store every uploaded file and keep the previous files where nothing new
     * was uploaded.
     */
    protected function storeFiles(mixed $value, mixed $previous, string $directory): mixed
    {
        if ($value instanceof UploadedFile) {
            return [
                'name' => $value->getClientOriginalName(),
                'path' => (string) $value->store($directory, self::DISK),
                'size' => (int) $value->getSize(),
            ];
        }

        if (($value === null || $value === []) && $this->holdsFiles($previous)) {
            return $previous;
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->storeFiles($item, is_array($previous) ? ($previous[$key] ?? null) : null, $directory);
        }

        return $value;
    }

    /**
     * Whether the value is a stored file or a list of stored files.
     */
    protected function holdsFiles(mixed $value): bool
    {
        if ($this->isFile($value)) {
            return true;
        }

        return is_array($value) && $value !== [] && array_is_list($value)
            && collect($value)->every(fn (mixed $item): bool => $this->isFile($item));
    }

    protected function isFile(mixed $value): bool
    {
        return is_array($value) && array_keys($value) === ['name', 'path', 'size'];
    }

    /**
     * @return array<int, array{name: string, path: string, size: int}>
     */
    protected function files(mixed $value): array
    {
        if ($this->isFile($value)) {
            return [$value];
        }

        return is_array($value) ? array_merge([], ...array_map(fn (mixed $item): array => $this->files($item), array_values($value))) : [];
    }

    /**
     * @return array<int, string>
     */
    protected function filePaths(mixed $value): array
    {
        return array_column($this->files($value), 'path');
    }

    /**
     * @param  array<int, string>  $paths
     */
    protected function deleteFiles(array $paths): void
    {
        if ($paths !== []) {
            Storage::disk(self::DISK)->delete(array_values($paths));
        }
    }

    /**
     * Replace every stored file in the value with the callback's result.
     *
     * @param  callable(array{name: string, path: string, size: int}): mixed  $callback
     */
    protected function mapFiles(mixed $value, callable $callback): mixed
    {
        if ($this->isFile($value)) {
            return $callback($value);
        }

        return is_array($value) ? array_map(fn (mixed $item): mixed => $this->mapFiles($item, $callback), $value) : $value;
    }

    /**
     * Names of the password fields and masked codes, which are stored hashed.
     *
     * @return array<int, string>
     */
    protected function secretFields(Form $form): array
    {
        $names = [];

        foreach ($form->getFieldsets() as $fieldset) {
            foreach ($fieldset->getFields() as $field) {
                if ($this->isSecret($field)) {
                    $names[] = $field->getName();
                }
            }
        }

        return $names;
    }

    protected function isSecret(Field $field): bool
    {
        $props = $field->toArray();

        return ($field instanceof TextInput && ($props['type'] ?? null) === 'password')
            || ($field instanceof OtpInput && ($props['masked'] ?? false) === true);
    }

    /**
     * A short title for the list: the first text value of the entry.
     *
     * @param  array<string, mixed>  $data
     */
    protected function title(Form $form, array $data): string
    {
        $values = $this->mapFiles(Arr::except($data, $this->secretFields($form)), fn (): null => null);

        $text = collect(Arr::dot($values))->first(
            fn (mixed $value): bool => is_string($value) && trim($value) !== '' && ! str_starts_with($value, '#'),
        );

        return $text === null ? 'Untitled entry' : Str::limit(trim(strip_tags($text)), 80);
    }

    /**
     * @param  callable(array{name: string, path: string, size: int}): string  $fileUrl
     * @return array{type: string, value: string|null, files: array<int, array{name: string, size: int, url: string}>}
     */
    protected function presentValue(mixed $value, bool $secret, callable $fileUrl): array
    {
        $row = fn (string $type, ?string $text = null, array $files = []): array => ['type' => $type, 'value' => $text, 'files' => $files];

        if ($value === null || $value === '' || $value === []) {
            return $row('empty');
        }

        if ($secret) {
            return $row('text', self::MASK);
        }

        if ($this->holdsFiles($value)) {
            return $row('files', null, array_map(fn (array $file): array => [
                'name' => $file['name'],
                'size' => $file['size'],
                'url' => $fileUrl($file),
            ], $this->files($value)));
        }

        if (is_bool($value)) {
            return $row('text', $value ? 'Yes' : 'No');
        }

        if (is_scalar($value)) {
            return $row('text', (string) $value);
        }

        if (array_is_list($value) && collect($value)->every(fn (mixed $item): bool => is_scalar($item))) {
            return $row('text', implode(', ', $value));
        }

        $json = json_encode(
            $this->mapFiles($value, fn (array $file): string => $file['name']),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        return $row('json', (string) $json);
    }
}
