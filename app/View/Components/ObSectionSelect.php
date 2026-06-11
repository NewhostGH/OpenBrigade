<?php

namespace App\View\Components;

use App\Services\FeatureService;
use App\Services\SectionScopeService;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Section dropdown, scoped by SectionScopeService — the user only ever sees
 * sections inside their visible set (memberships + descendants, narrowed by
 * the navbar switcher). Renders nothing when the multi_site feature is off.
 *
 * Filter usage (with an "all" option, submits on change):
 *   <x-ob-section-select :selected="$sectionId" all-label="Toutes sections" :auto-submit="true" />
 *
 * Form usage (required field, no "all" option):
 *   <x-ob-section-select name="P_SECTION" :selected="$current" required
 *                        class="@error('P_SECTION') is-invalid @enderror" />
 *
 * Extra HTML attributes (id, class, data-*) pass through to the <select>.
 */
class ObSectionSelect extends Component
{
    /** Depth → option background, mirroring the section tree indent colours. */
    private const DEPTH_BG = ['#FFCC33', '#FFFF99', '#B7D8FB', '#D4F1C0', '#F0E6FF'];

    public bool $multiSite;

    public ?int $selected;

    /** @var array<int,array{id:int,label:string,style:string}> */
    public array $options = [];

    public function __construct(
        SectionScopeService $scope,
        FeatureService $features,
        public string $name = 'section',
        int|string|null $selected = null,
        public ?string $allLabel = null,
        public bool $autoSubmit = false,
        public bool $required = false,
    ) {
        $this->multiSite = $features->isEnabled('multi_site');
        $this->selected = ($selected !== null && $selected !== '') ? (int) $selected : null;

        if (! $this->multiSite) {
            return;
        }

        foreach ($scope->options() as $opt) {
            $label = (string) $opt['S_CODE'];
            if ($opt['S_DESCRIPTION']) {
                $label .= ($label !== '' ? ' — ' : '').Str::limit($opt['S_DESCRIPTION'], 22);
            }

            $this->options[] = [
                'id' => $opt['S_ID'],
                'label' => $label,
                'style' => sprintf(
                    'padding-left:%srem; background:%s;',
                    round(1.2 + $opt['depth'] * 0.5, 1),
                    self::DEPTH_BG[min($opt['depth'], count(self::DEPTH_BG) - 1)]
                ),
            ];
        }
    }

    public function render(): View
    {
        return view('components.ob-section-select');
    }
}
