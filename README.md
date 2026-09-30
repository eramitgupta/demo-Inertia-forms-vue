# Laravel Inertia Forms — Vue demo

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![Inertia](https://img.shields.io/badge/Inertia-3-9553E9)
![Vue](https://img.shields.io/badge/Vue-3.5-4FC08D?logo=vuedotjs&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)

A complete Laravel + Inertia + Vue app built with [**erag/inertia-forms**](https://github.com/eramitgupta/laravel-Inertia-forms): 15 real-world forms, each defined once in a PHP class and rendered by a single `<Form>` component — with full create, read, update and delete.

**[Documentation](https://erag.in/laravel-inertia-forms/)** · **[Package](https://github.com/eramitgupta/laravel-Inertia-forms)** · Other demos: [React](https://github.com/eramitgupta/demo-Inertia-forms-react) · [Svelte](https://github.com/eramitgupta/demo-Inertia-forms-savlte)

## Features

- **15 demo forms** — from a simple contact form to wizards, builders and chat boxes.
- **Full CRUD** for every form — list, create, view, edit and delete entries.
- **Search and pagination** on every entry list.
- **Server-side validation** — rules come from the form class; hidden and unauthorized fields are skipped.
- **File uploads** stored on disk, kept on edit when nothing new is picked, and removed with the entry.
- **Secrets stay secret** — password and masked OTP fields are hashed and never sent back to the browser.
- **Custom fields** — `Rating`, `CodeInput` and `QuantityStepper`, each a PHP class plus a Vue component.
- **Accent colours** — pick one of six colours (or your own) and every form follows it.
- **Dark mode**, typed routes with [Wayfinder](https://github.com/laravel/wayfinder), and a Pest test suite.

## The demo forms

| Demo                 | Form class                              | What it shows                                                                            |
| -------------------- | --------------------------------------- | ---------------------------------------------------------------------------------------- |
| All fields           | `app/Forms/AllFieldsForm.php`           | Every field type and its main options on one page.                                       |
| Custom fields        | `app/Forms/CustomFieldsForm.php`        | Three custom fields (`Rating`, `CodeInput`, `QuantityStepper`) mixed with built-in ones. |
| Onboarding wizard    | `app/Forms/OnboardingWizardForm.php`    | A three-step wizard; each step is checked on the server before moving on.                |
| Landing page         | `app/Forms/LandingPageForm.php`         | Blocks, a repeater, links, display helpers and two submit actions (draft / publish).     |
| Support chat         | `app/Forms/SupportChatForm.php`         | A chat-style reply box: Enter sends, Shift+Enter adds a line, files can be attached.     |
| Product launch       | `app/Forms/ProductLaunchForm.php`       | Pricing, channels and a press-kit upload that appears when press is picked.              |
| Project kickoff      | `app/Forms/ProjectKickoffForm.php`      | Business details only show up for business clients.                                      |
| Support triage       | `app/Forms/SupportTriageForm.php`       | Critical issues ask for an on-call phone number; screenshots upload.                     |
| Event session        | `app/Forms/EventSessionForm.php`        | Room or meeting link depending on the session format.                                    |
| Campaign plan        | `app/Forms/CampaignPlanForm.php`        | Audiences, flight dates, channels and UTM tags.                                          |
| Hiring pipeline      | `app/Forms/HiringPipelineForm.php`      | Office location only matters when the role is not remote.                                |
| Subscription billing | `app/Forms/SubscriptionBillingForm.php` | EU countries ask for a VAT number; key-value invoice metadata.                           |
| Clinic intake        | `app/Forms/ClinicIntakeForm.php`        | Insurance details appear only when the patient is insured.                               |
| Property booking     | `app/Forms/PropertyBookingForm.php`     | Pet details show up when pets are coming along.                                          |
| Editorial calendar   | `app/Forms/EditorialCalendarForm.php`   | Content blocks, server-side author search and scheduled publishing.                      |

## How it works

**1. The form class** — fields, layout, conditions and validation live in one PHP class:

```php
class ProjectKickoffForm extends Form
{
    public function fields(): array
    {
        return [
            Fieldset::make('Project')->columns(2)->fields([
                TextInput::make('project_name')->required()->maxLength(80),
                TextInput::make('contact_email')->email()->required(),
                Combobox::make('client_type')->options(['personal' => 'Personal', 'business' => 'Business'])->default('personal'),
                TextInput::make('company')->required()->visibleWhen('client_type', 'business')->clearWhenHidden(),
            ]),
            Submit::make('Start project')->processingLabel('Starting…'),
        ];
    }
}
```

**2. The controller** — [`FormsDemoController`](app/Http/Controllers/FormsDemoController.php) passes the form to the page and receives it validated. The `#[ValidateDemoForm]` attribute works like the package's `#[Validate]`, picking the form class from the `{demo}` route parameter:

```php
public function edit(string $demo, FormEntry $entry): Response
{
    $form = $this->form($demo);

    return Inertia::render('…', [
        'form' => $form
            ->bind($this->entries->formValues($entry, $form))
            ->route('forms-demo.update', [$demo, $entry]),
    ]);
}

public function update(#[ValidateDemoForm] Form $form, string $demo, FormEntry $entry): RedirectResponse
{
    $this->entries->update($entry, $form); // saves $form->validated() only

    return to_route('forms-demo.show', [$demo, $entry]);
}
```

**3. The service** — [`FormEntryService`](app/Services/FormEntryService.php) stores the validated data in the `form_entries` table, saves uploaded files, hashes secrets, and builds the list, search and detail views.

**4. The page** — one component renders any form:

```vue
<script setup lang="ts">
import { Form, type FormSchema } from '@erag/inertia-forms-vue';
import CodeInput from '@/components/form-fields/CodeInput.vue';
import QuantityStepper from '@/components/form-fields/QuantityStepper.vue';
import Rating from '@/components/form-fields/Rating.vue';

defineProps<{ form: FormSchema }>();
</script>

<template>
    <Form :form="form" :components="{ Rating, CodeInput, QuantityStepper }" />
</template>
```

## Requirements

- PHP 8.3+ and Composer
- Node.js 22+ and npm
- SQLite (default) or any database Laravel supports

## Getting started

```bash
git clone https://github.com/eramitgupta/demo-Inertia-forms-vue.git
cd demo-Inertia-forms-vue
composer setup
php artisan db:seed
composer run dev
```

`composer setup` installs the PHP and npm packages, creates `.env`, generates the app key, runs the migrations and builds the assets. Then open the app, log in with **test@example.com** / **password**, and choose **Forms demo** in the sidebar.

Using [Laravel Herd](https://herd.laravel.com)? The site is served for you — run `npm run dev` instead of `composer run dev`.

## Project structure

```text
app/
├── Forms/                      # the 15 demo form classes
│   └── Fields/                 # custom fields: Rating, CodeInput, QuantityStepper
├── Http/
│   ├── Attributes/ValidateDemoForm.php
│   └── Controllers/FormsDemoController.php
├── Models/FormEntry.php
└── Services/FormEntryService.php
resources/js/
├── components/
│   ├── form-fields/            # Rating.vue, CodeInput.vue, QuantityStepper.vue
│   ├── FormsDemoPicker.vue
│   ├── FormsDemoPanel.vue
│   └── FormsDemoDeleteDialog.vue
└── pages/FormsDemo/
    ├── Index.vue              # entry list with search and pagination
    ├── Form.vue               # create and edit
    └── Show.vue               # entry details
routes/web.php                  # forms-demo.* routes
tests/Feature/FormsDemoTest.php
```

## Testing

```bash
php artisan test --compact
```

The full CI check (formatting, linting, type checks, PHPStan and tests) runs in GitHub Actions on pushes to `main` and on pull requests:

```bash
composer ci:check
```

## AI assistants

The app ships [Laravel Boost](https://github.com/laravel/boost) guidelines (`AGENTS.md`) and skills for Claude Code (`.claude/skills`) and Junie (`.junie/skills`), including **inertia-forms-development** from the package. After updating packages, refresh them with:

```bash
php artisan boost:update
```

## Support

- Documentation: [erag.in/laravel-inertia-forms](https://erag.in/laravel-inertia-forms/)
- Package issues: [github.com/eramitgupta/laravel-Inertia-forms/issues](https://github.com/eramitgupta/laravel-Inertia-forms/issues)

## Sponsorship

If this project helps you, consider [sponsoring the work on GitHub](https://github.com/sponsors/eramitgupta).

## License

Open-sourced under the [MIT license](https://opensource.org/licenses/MIT). Built by [Er Amit Gupta](https://github.com/eramitgupta).
