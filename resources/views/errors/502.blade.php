@extends(\App\Support\ErrorPage::layout(502))

@section('title', \App\Support\ErrorPage::meta(502)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 502])
@endsection
