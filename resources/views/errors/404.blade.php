@extends(\App\Support\ErrorPage::layout(404))

@section('title', \App\Support\ErrorPage::meta(404)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 404])
@endsection
