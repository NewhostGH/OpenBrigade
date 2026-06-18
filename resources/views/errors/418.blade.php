@extends(\App\Support\ErrorPage::layout(418))

@section('title', \App\Support\ErrorPage::meta(418)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 418])
@endsection
