@extends(\App\Support\ErrorPage::layout(403))

@section('title', \App\Support\ErrorPage::meta(403)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 403])
@endsection
