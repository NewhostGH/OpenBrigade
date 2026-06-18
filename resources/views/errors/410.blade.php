@extends(\App\Support\ErrorPage::layout(410))

@section('title', \App\Support\ErrorPage::meta(410)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 410])
@endsection
