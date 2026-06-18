@extends(\App\Support\ErrorPage::layout(429))

@section('title', \App\Support\ErrorPage::meta(429)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 429])
@endsection
