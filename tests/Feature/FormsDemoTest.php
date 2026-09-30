<?php

use App\Http\Controllers\FormsDemoController;
use App\Models\FormEntry;
use App\Models\User;
use App\Services\FormEntryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('guests are redirected to the login page', function () {
    auth()->logout();

    $this->get(route('forms-demo.index'))->assertRedirect(route('login'));
});

test('the index lists the all fields entries by default', function () {
    FormEntry::factory()->create(['demo' => 'all-fields', 'title' => 'Jane Doe']);
    FormEntry::factory()->create(['demo' => 'project-kickoff']);

    $this->get(route('forms-demo.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('FormsDemo/Index')
            ->where('demo', 'all-fields')
            ->where('label', 'All fields')
            ->where('className', 'AllFieldsForm')
            ->has('demos', count(FormsDemoController::DEMOS))
            ->where('search', '')
            ->has('entries.data', 1)
            ->where('entries.data.0.title', 'Jane Doe'));
});

test('the index searches and paginates the entries', function () {
    FormEntry::factory()->count(12)->create();
    FormEntry::factory()->create(['title' => 'Acme redesign']);

    $this->get(route('forms-demo.index', ['demo' => 'project-kickoff', 'search' => 'acme']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('search', 'acme')
            ->has('entries.data', 1)
            ->where('entries.data.0.title', 'Acme redesign'));

    $this->get(route('forms-demo.index', ['demo' => 'project-kickoff', 'page' => 2]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('entries.data', 3)
            ->where('entries.total', 13)
            ->where('entries.current_page', 2));
});

test('every demo form renders and points at its own store route', function (string $demo) {
    $this->get(route('forms-demo.create', $demo))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('FormsDemo/Form')
            ->where('demo', $demo)
            ->where('entry', null)
            ->where('form.action', route('forms-demo.store', $demo))
            ->where('form.method', 'post'));
})->with(array_keys(FormsDemoController::DEMOS));

test('the all fields form has its defaults', function () {
    $this->get(route('forms-demo.create', 'all-fields'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('form.hasFiles', true)
            ->where('form.data.stay', ['start' => '', 'end' => ''])
            ->has('form.fieldsets', 7));
});

test('unknown demos and entries of another demo return not found', function () {
    $entry = FormEntry::factory()->create(['demo' => 'project-kickoff']);

    $this->get('/forms-demo/unknown')->assertNotFound();
    $this->post('/forms-demo/unknown')->assertNotFound();
    $this->get(route('forms-demo.show', ['hiring-pipeline', $entry]))->assertNotFound();
    $this->get(route('forms-demo.edit', ['hiring-pipeline', $entry]))->assertNotFound();
    $this->put(route('forms-demo.update', ['hiring-pipeline', $entry]), ['role_title' => 'Moved'])->assertNotFound();
    $this->delete(route('forms-demo.destroy', ['hiring-pipeline', $entry]))->assertNotFound();
    $this->get(route('forms-demo.file', ['hiring-pipeline', $entry, 'path' => 'x']))->assertNotFound();

    expect($entry->fresh())->not->toBeNull();
});

test('a valid submission is stored and hidden fields are skipped', function () {
    $this->post(route('forms-demo.store', 'project-kickoff'), [
        'project_name' => 'Acme redesign',
        'contact_email' => 'owner@example.com',
        'client_type' => 'personal',
        'company' => 'Ignored while hidden',
        'priority' => 'high',
        'stack' => ['React', 'Tailwind CSS'],
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('forms-demo.index', 'project-kickoff'))
        ->assertInertiaFlash('toast.message', 'Entry created.');

    $entry = FormEntry::sole();

    expect($entry->demo)->toBe('project-kickoff')
        ->and($entry->title)->toBe('Acme redesign')
        ->and($entry->data)->toMatchArray(['contact_email' => 'owner@example.com', 'stack' => ['React', 'Tailwind CSS']])
        ->and($entry->data)->not->toHaveKey('company');
});

test('required and conditional fields are validated', function () {
    $this->from(route('forms-demo.create', 'project-kickoff'))
        ->post(route('forms-demo.store', 'project-kickoff'), ['client_type' => 'business'])
        ->assertRedirect(route('forms-demo.create', 'project-kickoff'))
        ->assertSessionHasErrors(['project_name', 'contact_email', 'company']);

    $this->post(route('forms-demo.store', 'support-triage'), ['severity' => 'critical'])
        ->assertSessionHasErrors(['requester_email', 'area', 'on_call_phone', 'steps']);

    $this->post(route('forms-demo.store', 'subscription-billing'), ['country' => 'DE'])
        ->assertSessionHasErrors(['billing_email', 'vat_number', 'terms']);

    $this->post(route('forms-demo.store', 'hiring-pipeline'), [
        'role_title' => 'Senior Laravel developer',
        'department' => 'Engineering',
        'workplace' => 'hybrid',
    ])->assertSessionHasErrors(['office']);

    expect(FormEntry::count())->toBe(0);
});

test('the edit form is filled with the entry and updates it', function () {
    $entry = FormEntry::factory()->create();

    $this->get(route('forms-demo.edit', ['project-kickoff', $entry]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('FormsDemo/Form')
            ->where('entry.id', $entry->id)
            ->where('entry.data.project_name', $entry->title)
            ->where('form.action', route('forms-demo.update', ['project-kickoff', $entry]))
            ->where('form.method', 'put')
            ->where('form.data.project_name', $entry->title)
            ->where('form.data.stack', ['Laravel', 'React']));

    $this->put(route('forms-demo.update', ['project-kickoff', $entry]), [
        'project_name' => 'Renamed project',
        'contact_email' => 'new@example.com',
        'client_type' => 'personal',
        'priority' => 'low',
    ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('forms-demo.show', ['project-kickoff', $entry]))
        ->assertInertiaFlash('toast.message', 'Entry updated.');

    expect($entry->fresh())
        ->title->toBe('Renamed project')
        ->data->toMatchArray(['contact_email' => 'new@example.com', 'priority' => 'low']);
});

test('the show page lists the entry by fieldset', function () {
    $entry = FormEntry::factory()->create(['data' => [
        'project_name' => 'Acme redesign',
        'contact_email' => 'owner@example.com',
        'client_type' => 'personal',
        'priority' => 'high',
        'stack' => ['React', 'Tailwind CSS'],
    ]]);

    $this->get(route('forms-demo.show', ['project-kickoff', $entry]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('FormsDemo/Show')
            ->where('entry.title', $entry->title)
            ->where('sections', fn ($sections): bool => collect($sections)->flatMap(fn (array $section): array => $section['rows'])
                ->contains(fn (array $row): bool => $row['type'] === 'text' && $row['value'] === 'React, Tailwind CSS')));
});

test('deleting an entry removes it and its files', function () {
    Storage::fake(FormEntryService::DISK);

    $this->post(route('forms-demo.store', 'support-triage'), [
        'requester_email' => 'jane@example.com',
        'area' => 'billing',
        'severity' => 'low',
        'steps' => 'Open the latest invoice.',
        'screenshots' => [UploadedFile::fake()->image('invoice.png')],
    ])->assertSessionHasNoErrors();

    $entry = FormEntry::sole();
    $path = $entry->data['screenshots'][0]['path'];

    Storage::disk(FormEntryService::DISK)->assertExists($path);

    $this->delete(route('forms-demo.destroy', ['support-triage', $entry]))
        ->assertRedirect(route('forms-demo.index', 'support-triage'))
        ->assertInertiaFlash('toast.message', 'Entry deleted.');

    expect(FormEntry::count())->toBe(0);
    Storage::disk(FormEntryService::DISK)->assertMissing($path);
});

test('uploaded files are stored, kept on update and can be opened', function () {
    Storage::fake(FormEntryService::DISK);

    $full = [
        'full_name' => 'Jane Doe',
        'plan' => 'pro',
        'frameworks' => ['Laravel', 'React'],
        'keywords' => ['forms', 'laravel'],
        'size' => 'L',
        'channels' => ['email', 'push'],
        'stay' => ['start' => now()->addDay()->toDateString(), 'end' => now()->addDays(3)->toDateString()],
        'opens_at' => '09:15',
        'duration' => '01:30:00',
        'brand_color' => '#7c3aed',
        'terms' => true,
    ];

    $this->post(route('forms-demo.store', 'all-fields'), [
        ...$full,
        'attachments' => [UploadedFile::fake()->create('brief.pdf', 10, 'application/pdf')],
    ])->assertSessionHasNoErrors();

    $entry = FormEntry::sole();
    $file = $entry->data['attachments'][0];

    expect($entry->title)->toBe('Jane Doe')
        ->and($file['name'])->toBe('brief.pdf');

    $this->get(route('forms-demo.file', ['all-fields', $entry, 'path' => $file['path']]))->assertOk();
    $this->get(route('forms-demo.file', ['all-fields', $entry, 'path' => 'form-entries/other.pdf']))->assertNotFound();

    $this->put(route('forms-demo.update', ['all-fields', $entry]), [...$full, 'full_name' => 'Jane Smith', 'attachments' => []])
        ->assertSessionHasNoErrors();

    expect($entry->fresh()->data['attachments'])->toBe([$file]);
    Storage::disk(FormEntryService::DISK)->assertExists($file['path']);

    $this->put(route('forms-demo.update', ['all-fields', $entry]), [
        ...$full,
        'attachments' => [UploadedFile::fake()->create('brief-v2.pdf', 10, 'application/pdf')],
    ])->assertSessionHasNoErrors();

    expect($entry->fresh()->data['attachments'][0]['name'])->toBe('brief-v2.pdf');
    Storage::disk(FormEntryService::DISK)->assertMissing($file['path']);
});

test('passwords are stored hashed and never sent back to the browser', function () {
    $this->post(route('forms-demo.store', 'onboarding-wizard'), [
        'name' => 'Jane',
        'email' => 'jane@example.test',
        'password' => 'secret-password',
        'workspace' => 'Acme Studio',
        'workspace_url' => 'acme-studio',
        'team_size' => '2-10',
        'code' => '123456',
    ])->assertSessionHasNoErrors();

    $entry = FormEntry::sole();
    $hash = $entry->data['password'];

    expect(Hash::check('secret-password', $hash))->toBeTrue();

    $this->get(route('forms-demo.edit', ['onboarding-wizard', $entry]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('form.data.password', '')
            ->where('entry.data.password', FormEntryService::MASK));

    $this->get(route('forms-demo.show', ['onboarding-wizard', $entry]))
        ->assertDontSee($hash, false);
});

test('invalid values in the all fields form are rejected', function () {
    $this->post(route('forms-demo.store', 'all-fields'), [
        'full_name' => 'Jane Doe',
        'stay' => ['start' => '2026-10-09', 'end' => '2026-10-05'],
        'brand_color' => 'purple',
        'keywords' => ['same', 'same'],
    ])->assertSessionHasErrors(['stay.end', 'brand_color', 'keywords.0', 'terms']);
});

test('custom fields are serialized with their extra props', function () {
    $this->get(route('forms-demo.create', 'custom-fields'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('className', 'CustomFieldsForm')
            ->where('form.fieldsets.0.fields.1.component', 'QuantityStepper')
            ->where('form.fieldsets.0.fields.1.max', 10)
            ->where('form.fieldsets.0.fields.1.unit', 'tickets')
            ->where('form.fieldsets.1.fields.1.component', 'CodeInput')
            ->where('form.fieldsets.1.fields.1.length', 6)
            ->where('form.fieldsets.2.fields.0.component', 'Rating')
            ->where('form.fieldsets.2.fields.0.stars', 5)
            ->where('form.data.tickets', 2)
            ->where('form.data.parking', 0)
            ->where('form.data.rating', null));
});

test('custom fields are validated by their own rules', function () {
    $this->post(route('forms-demo.store', 'custom-fields'), [
        'email' => 'jane@example.com',
        'tickets' => 12,
        'code' => '12a4',
        'rating' => 6,
    ])->assertSessionHasErrors(['tickets', 'code', 'rating']);

    $this->post(route('forms-demo.store', 'custom-fields'), [
        'ticket' => 'vip',
        'tickets' => 3,
        'parking' => 1,
        'email' => 'jane@example.com',
        'code' => '123456',
        'rating' => 5,
        'comment' => 'Ignored while hidden',
    ])->assertSessionHasNoErrors();

    expect(FormEntry::sole()->data)
        ->toMatchArray(['tickets' => 3, 'rating' => 5])
        ->not->toHaveKey('comment');
});

test('key value rows are validated and stored as a plain array', function () {
    $this->get(route('forms-demo.create', 'subscription-billing'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('form.data.invoice_metadata', [
                ['key' => 'region', 'value' => 'EMEA'],
                ['key' => 'cost_center', 'value' => 'OPS-204'],
            ]));

    $valid = [
        'plan' => 'growth',
        'cycle' => 'yearly',
        'seats' => 5,
        'billing_email' => 'billing@example.com',
        'country' => 'IN',
        'terms' => true,
    ];

    $this->post(route('forms-demo.store', 'subscription-billing'), [...$valid, 'invoice_metadata' => [
        ['key' => 'region', 'value' => 'EMEA'],
        ['key' => '', 'value' => ''],
        ['key' => 'cost_center', 'value' => 'OPS-204'],
    ]])->assertSessionHasNoErrors();

    expect(FormEntry::sole()->data['invoice_metadata'])->toBe(['region' => 'EMEA', 'cost_center' => 'OPS-204']);

    $this->post(route('forms-demo.store', 'subscription-billing'), [...$valid, 'invoice_metadata' => [
        ['key' => 'region', 'value' => 'EMEA'],
        ['key' => 'region', 'value' => 'APAC'],
    ]])->assertSessionHasErrors(['invoice_metadata.1.key']);
});

test('builder blocks are validated per block and stored clean', function () {
    $this->get(route('forms-demo.create', 'editorial-calendar'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('form.hasFiles', true)
            ->where('form.data.body.0', ['type' => 'section', 'data' => ['heading' => 'Why forms belong in PHP', 'summary' => '']])
            ->where('form.data.body.1.type', 'quote'));

    $valid = ['headline' => 'Forms', 'author' => 6, 'section' => 'guides', 'status' => 'draft'];

    $this->post(route('forms-demo.store', 'editorial-calendar'), [...$valid, 'body' => [
        ['type' => 'section', 'data' => ['heading' => '']],
        ['type' => 'quote', 'data' => ['text' => 'Hi', 'author' => '']],
    ]])->assertSessionHasErrors([
        'body.0.data.heading' => 'The Heading (Section 1) field is required.',
        'body.1.data.author' => 'The Author (Quote 2) field is required.',
    ]);

    $this->post(route('forms-demo.store', 'editorial-calendar'), [...$valid, 'body' => [
        ['type' => 'quote', 'data' => ['text' => 'Hi', 'author' => 'Ben', 'source' => '', 'extra' => 'dropped']],
    ]])->assertSessionHasNoErrors();

    expect(FormEntry::sole()->data['body'])->toBe([
        ['type' => 'quote', 'data' => ['text' => 'Hi', 'author' => 'Ben', 'source' => null]],
    ]);
});

test('the author select searches on the server and rejects unknown authors', function () {
    $form = $this->get(route('forms-demo.create', 'editorial-calendar'))->inertiaProps('form');
    $author = collect($form['fieldsets'])->flatMap(fn (array $fieldset): array => $fieldset['fields'])->firstWhere('name', 'author');

    expect($author['search'])->toMatchArray(['field' => 'author', 'url' => route('inertia-forms.search')])
        ->and($author['options'])->toBe([]);

    $this->postJson($author['search']['url'], ['form' => $author['search']['token'], 'field' => 'author', 'search' => 'morg'])
        ->assertOk()
        ->assertJsonPath('options.0.label', 'Morgan Vale')
        ->assertJsonPath('options.0.description', 'morgan@example.test')
        ->assertJsonCount(1, 'options');

    $this->post(route('forms-demo.store', 'editorial-calendar'), ['headline' => 'Forms', 'author' => 99, 'section' => 'guides', 'status' => 'draft'])
        ->assertSessionHasErrors(['author' => 'The selected Author is invalid.']);
});

test('the onboarding wizard checks each step on the server', function () {
    $form = $this->get(route('forms-demo.create', 'onboarding-wizard'))->inertiaProps('form');

    expect($form['wizard'])->toMatchArray(['nextLabel' => 'Continue', 'backLabel' => 'Back'])
        ->and(array_column($form['fieldsets'], 'icon'))->toContain('user', 'briefcase', 'shield');

    $step = fn (int $index, array $data) => $this->postJson($form['wizard']['validateUrl'], [
        'form' => $form['wizard']['token'],
        'step' => $index,
        'data' => $data,
    ]);

    $step(0, ['name' => 'Jane', 'email' => 'not-an-email', 'password' => 'short'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);

    $step(0, ['name' => 'Jane', 'email' => 'jane@example.test', 'password' => 'secret-password'])->assertNoContent();

    $step(1, ['workspace' => 'Acme Studio', 'workspace_url' => 'Acme Studio'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['workspace_url']);

    $step(2, ['code' => '000000'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.code.0', 'That code is not right. Use 123456 in this demo.');
});

test('the landing page saves blocks, a repeater and the clicked action', function () {
    $this->post(route('forms-demo.store', 'landing-page'), [
        'title' => 'Launch faster',
        'slug' => 'launch-faster',
        'cta' => ['url' => 'https://example.test/start', 'label' => 'Get started', 'target' => '_blank'],
        'intro' => 'Build forms in PHP.',
        'sections' => [['type' => 'hero', 'data' => ['headline' => 'Launch', 'summary' => '']]],
        'faq' => [['question' => 'Free?', 'answer' => 'Yes.']],
        'intent' => 'publish',
    ])->assertSessionHasNoErrors();

    expect(FormEntry::sole()->data)->toMatchArray([
        'intent' => 'publish',
        'intro' => 'Build forms in PHP.',
        'faq' => [['question' => 'Free?', 'answer' => 'Yes.']],
    ]);

    $this->post(route('forms-demo.store', 'landing-page'), [
        'title' => 'Launch faster',
        'slug' => 'Launch Faster',
        'cta' => ['url' => 'http://example.test'],
        'intent' => 'delete',
    ])->assertSessionHasErrors(['slug', 'cta.url', 'intent']);
});

test('the support chat needs a message or an attachment', function () {
    Storage::fake(FormEntryService::DISK);

    $this->post(route('forms-demo.store', 'support-chat'), ['reply' => ['message' => '']])
        ->assertSessionHasErrors(['reply' => 'Write a message or attach a file.']);

    $this->post(route('forms-demo.store', 'support-chat'), ['reply' => [
        'message' => 'Thanks for reaching out!',
        'attachments' => [UploadedFile::fake()->image('screenshot.png')],
    ]])->assertSessionHasNoErrors();

    $entry = FormEntry::sole();

    expect($entry->title)->toBe('Thanks for reaching out!')
        ->and($entry->data['reply']['attachments'][0]['name'])->toBe('screenshot.png');
});
