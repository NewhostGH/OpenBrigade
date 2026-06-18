@extends(\App\Support\ErrorPage::layout(409))

@section('title', \App\Support\ErrorPage::meta(409)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 409])
@endsection
