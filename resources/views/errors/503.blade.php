@extends(\App\Support\ErrorPage::layout(503))

@section('title', \App\Support\ErrorPage::meta(503)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 503])
@endsection
