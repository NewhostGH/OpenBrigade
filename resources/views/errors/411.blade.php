@extends(\App\Support\ErrorPage::layout(411))

@section('title', \App\Support\ErrorPage::meta(411)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 411])
@endsection
