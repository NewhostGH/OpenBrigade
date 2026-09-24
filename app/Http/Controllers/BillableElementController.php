<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveBillableElementRequest;
use App\Models\BillableElement;
use App\Models\BillableElementType;
use App\Services\SectionScopeService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Billable elements (legacy element_facturable.php): per-section catalogue of
 * invoice lines with a default price. Permission 29 (Comptabilité) is checked
 * on the route for the active section and again for the row's own section.
 */
class BillableElementController extends Controller
{
    public function __construct(private readonly SectionScopeService $scope) {}

    public function index(Request $request): View
    {
        // First visit: the user's section, like legacy. An explicit empty filter means all sections.
        $sectionId = $request->has('section')
            ? $this->scope->sectionFilter($request)
            : $this->scope->defaultSectionId();
        $type = (string) $request->string('type', 'ALL');

        $query = BillableElement::query()
            ->with(['type', 'section'])
            ->orderBy('TEF_CODE')
            ->orderBy('EF_NAME');
        $this->scope->apply($query, 'S_ID', $sectionId, subsections: false);
        if ($type !== 'ALL') {
            $query->where('TEF_CODE', $type);
        }

        return view('billable-element.index', [
            'items' => $query->get(),
            'types' => BillableElementType::query()->orderBy('TEF_NAME')->get(),
            'sectionId' => $sectionId,
            'type' => $type,
            'defaultSectionId' => $this->scope->defaultSectionId(),
            'currency' => Money::symbol(),
        ]);
    }

    public function store(SaveBillableElementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['S_ID'] = $this->authorizedSection($data['S_ID'] ?? null);

        BillableElement::create($data);

        return $this->backToList($data['S_ID'])->with('success', __('billable_element.created'));
    }

    public function update(SaveBillableElementRequest $request, BillableElement $billableElement): RedirectResponse
    {
        $this->authorizeSection($billableElement->S_ID);
        $data = $request->validated();
        $data['S_ID'] = $this->authorizedSection($data['S_ID'] ?? $billableElement->S_ID);

        $billableElement->update($data);

        return $this->backToList($data['S_ID'])->with('success', __('billable_element.updated'));
    }

    public function destroy(BillableElement $billableElement): RedirectResponse
    {
        $sectionId = $this->authorizeSection($billableElement->S_ID);
        $billableElement->delete();

        return $this->backToList($sectionId)->with('success', __('billable_element.deleted'));
    }

    /** A submitted target section, forced inside the visible set, then authorized. */
    private function authorizedSection(?int $sectionId): int
    {
        return $this->authorizeSection($this->scope->coerce($sectionId) ?? 0);
    }

    /** Abort unless the section is visible to the user and grants F_ID 29 there. */
    private function authorizeSection(int $sectionId): int
    {
        abort_unless(
            $this->scope->allows($sectionId) && auth()->user()->hasPermissionInSection(29, $sectionId),
            403
        );

        return $sectionId;
    }

    private function backToList(int $sectionId): RedirectResponse
    {
        return redirect()->route('billable-element.index', ['section' => $sectionId]);
    }
}
