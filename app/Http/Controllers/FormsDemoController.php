<?php

namespace App\Http\Controllers;

use App\Forms\AllFieldsForm;
use App\Forms\CampaignPlanForm;
use App\Forms\ClinicIntakeForm;
use App\Forms\CustomFieldsForm;
use App\Forms\EditorialCalendarForm;
use App\Forms\EventSessionForm;
use App\Forms\HiringPipelineForm;
use App\Forms\LandingPageForm;
use App\Forms\OnboardingWizardForm;
use App\Forms\ProductLaunchForm;
use App\Forms\ProjectKickoffForm;
use App\Forms\PropertyBookingForm;
use App\Forms\SubscriptionBillingForm;
use App\Forms\SupportChatForm;
use App\Forms\SupportTriageForm;
use App\Http\Attributes\ValidateDemoForm;
use App\Models\FormEntry;
use App\Services\FormEntryService;
use Erag\InertiaForms\Form;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormsDemoController extends Controller
{
    /**
     * Demo forms keyed by their URL slug.
     *
     * @var array<string, array{label: string, class: class-string<Form>}>
     */
    public const DEMOS = [
        'all-fields' => ['label' => 'All fields', 'class' => AllFieldsForm::class],
        'custom-fields' => ['label' => 'Custom fields', 'class' => CustomFieldsForm::class],
        'onboarding-wizard' => ['label' => 'Onboarding wizard', 'class' => OnboardingWizardForm::class],
        'landing-page' => ['label' => 'Landing page', 'class' => LandingPageForm::class],
        'support-chat' => ['label' => 'Support chat', 'class' => SupportChatForm::class],
        'product-launch' => ['label' => 'Product launch', 'class' => ProductLaunchForm::class],
        'project-kickoff' => ['label' => 'Project kickoff', 'class' => ProjectKickoffForm::class],
        'support-triage' => ['label' => 'Support triage', 'class' => SupportTriageForm::class],
        'event-session' => ['label' => 'Event session', 'class' => EventSessionForm::class],
        'campaign-plan' => ['label' => 'Campaign plan', 'class' => CampaignPlanForm::class],
        'hiring-pipeline' => ['label' => 'Hiring pipeline', 'class' => HiringPipelineForm::class],
        'subscription-billing' => ['label' => 'Subscription billing', 'class' => SubscriptionBillingForm::class],
        'clinic-intake' => ['label' => 'Clinic intake', 'class' => ClinicIntakeForm::class],
        'property-booking' => ['label' => 'Property booking', 'class' => PropertyBookingForm::class],
        'editorial-calendar' => ['label' => 'Editorial calendar', 'class' => EditorialCalendarForm::class],
    ];

    public function __construct(protected FormEntryService $entries) {}

    /**
     * The form class of a demo.
     *
     * @return class-string<Form>
     */
    public static function formClass(string $demo): string
    {
        return self::DEMOS[$demo]['class'] ?? abort(404);
    }

    /**
     * List the saved entries of one demo form.
     */
    public function index(Request $request, string $demo = 'all-fields'): Response
    {
        $search = trim($request->string('search')->toString());

        return Inertia::render('FormsDemo/Index', [
            ...$this->shared($demo),
            'search' => $search,
            'entries' => $this->entries->paginate($demo, $search)
                ->through(fn (FormEntry $entry): array => $this->summary($entry)),
        ]);
    }

    /**
     * Show the empty demo form.
     */
    public function create(string $demo): Response
    {
        return Inertia::render('FormsDemo/Form', [
            ...$this->shared($demo),
            'form' => $this->form($demo)->route('forms-demo.store', $demo),
            'entry' => null,
        ]);
    }

    /**
     * Validate the demo form and save it as a new entry.
     */
    public function store(#[ValidateDemoForm] Form $form, string $demo): RedirectResponse
    {
        $this->entries->create($demo, $form);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Entry created.')]);

        return to_route('forms-demo.index', $demo);
    }

    /**
     * Show one saved entry.
     */
    public function show(string $demo, FormEntry $entry): Response
    {
        return Inertia::render('FormsDemo/Show', [
            ...$this->shared($demo),
            'entry' => $this->summary($entry),
            'sections' => $this->entries->present(
                $entry,
                $this->form($demo),
                fn (array $file): string => route('forms-demo.file', [$demo, $entry, 'path' => $file['path']]),
            ),
        ]);
    }

    /**
     * Show the demo form filled with a saved entry.
     */
    public function edit(string $demo, FormEntry $entry): Response
    {
        $form = $this->form($demo);

        return Inertia::render('FormsDemo/Form', [
            ...$this->shared($demo),
            'form' => $form
                ->bind($this->entries->formValues($entry, $form))
                ->route('forms-demo.update', [$demo, $entry]),
            'entry' => [...$this->summary($entry), 'data' => $this->entries->storedData($entry, $form)],
        ]);
    }

    /**
     * Validate the demo form and update the saved entry.
     */
    public function update(#[ValidateDemoForm] Form $form, string $demo, FormEntry $entry): RedirectResponse
    {
        $this->entries->update($entry, $form);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Entry updated.')]);

        return to_route('forms-demo.show', [$demo, $entry]);
    }

    /**
     * Delete a saved entry and its files.
     */
    public function destroy(string $demo, FormEntry $entry): RedirectResponse
    {
        $this->entries->delete($entry);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Entry deleted.')]);

        return to_route('forms-demo.index', $demo);
    }

    /**
     * Open a file uploaded with a saved entry.
     */
    public function file(Request $request, string $demo, FormEntry $entry): StreamedResponse
    {
        $file = $this->entries->file($entry, $request->string('path')->toString());

        abort_if($file === null, 404);

        return Storage::disk(FormEntryService::DISK)->response($file['path'], $file['name']);
    }

    /**
     * Props every demo page needs for the form picker.
     *
     * @return array{demo: string, label: string, className: string, demos: Collection<string, string>}
     */
    protected function shared(string $demo): array
    {
        return [
            'demo' => $demo,
            'label' => self::DEMOS[$demo]['label'],
            'className' => class_basename(self::formClass($demo)),
            'demos' => collect(self::DEMOS)->map(fn (array $item): string => $item['label']),
        ];
    }

    /**
     * @return array{id: int, title: string, createdAt: string|null, updatedAt: string|null}
     */
    protected function summary(FormEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'createdAt' => $entry->created_at?->toIso8601String(),
            'updatedAt' => $entry->updated_at?->toIso8601String(),
        ];
    }

    protected function form(string $demo): Form
    {
        return self::formClass($demo)::make();
    }
}
