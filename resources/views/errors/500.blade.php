@extends(\App\Support\ErrorPage::layout(500))

@section('title', \App\Support\ErrorPage::meta(500)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 500])
@endsection
