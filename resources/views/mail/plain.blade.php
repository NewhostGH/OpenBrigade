@component('mail::message')
{!! nl2br(e($bodyText)) !!}

@component('mail::subcopy')
{{ __('Ce message vous est adressé par :app.', ['app' => config('app.name')]) }}
@endcomponent
@endcomponent
