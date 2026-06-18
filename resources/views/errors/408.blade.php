@extends(\App\Support\ErrorPage::layout(408))

@section('title', \App\Support\ErrorPage::meta(408)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 408])
@endsection
