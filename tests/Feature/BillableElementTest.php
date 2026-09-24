<?php

use App\Http\Requests\SaveBillableElementRequest;
use Illuminate\Support\Facades\Validator;

// ── Access control ───────────────────────────────────────────────────────────

test('unauthenticated users are redirected from the billable elements page to login', function () {
    $this->get('/billable-elements')->assertRedirect('/login');
    $this->post('/billable-elements')->assertRedirect('/login');
});

// ── Routes & navigation ──────────────────────────────────────────────────────

test('the billable element routes are registered', function () {
    expect(route('billable-element.index'))->toEndWith('/billable-elements')
        ->and(route('billable-element.store'))->toEndWith('/billable-elements')
        ->and(route('billable-element.update', 7))->toEndWith('/billable-elements/7')
        ->and(route('billable-element.destroy', 7))->toEndWith('/billable-elements/7');
});

test('the Clients menu links to the billable elements page', function () {
    $clients = collect(config('navigation.top'))->firstWhere('code', 'companies');

    expect(collect($clients['items'])->pluck('url'))->toContain('/billable-elements');
});

// ── Validation ───────────────────────────────────────────────────────────────

/** Run the request's normalisation + rules on raw input, without the DB-backed exists rule. */
function billableValidate(array $input): Illuminate\Validation\Validator
{
    $request = SaveBillableElementRequest::create('/billable-elements', 'POST', $input);
    (fn () => $this->prepareForValidation())->call($request);

    $rules = $request->rules();
    $rules['TEF_CODE'] = ['required', 'string'];

    return Validator::make($request->all(), $rules);
}

test('a French decimal comma is accepted for the price', function () {
    $v = billableValidate(['TEF_CODE' => 'PRE', 'EF_NAME' => 'Poste de secours', 'EF_PRICE' => '12,50']);

    expect($v->passes())->toBeTrue()
        ->and((float) $v->validated()['EF_PRICE'])->toBe(12.5);
});

test('a negative or non-numeric price is rejected', function (string $price) {
    $v = billableValidate(['TEF_CODE' => 'PRE', 'EF_NAME' => 'Forfait', 'EF_PRICE' => $price]);

    expect($v->fails())->toBeTrue()
        ->and($v->errors()->has('EF_PRICE'))->toBeTrue();
})->with(['-5', 'abc']);

test('the label is required and capped at 60 characters', function () {
    expect(billableValidate(['TEF_CODE' => 'PRE', 'EF_NAME' => '', 'EF_PRICE' => '1'])->fails())->toBeTrue()
        ->and(billableValidate(['TEF_CODE' => 'PRE', 'EF_NAME' => str_repeat('a', 61), 'EF_PRICE' => '1'])->fails())->toBeTrue();
});
