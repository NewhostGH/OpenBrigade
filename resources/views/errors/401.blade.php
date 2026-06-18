@extends(\App\Support\ErrorPage::layout(401))

@section('title', \App\Support\ErrorPage::meta(401)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 401])
@endsection
