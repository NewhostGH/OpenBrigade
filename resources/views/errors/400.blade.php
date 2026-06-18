@extends(\App\Support\ErrorPage::layout(400))

@section('title', \App\Support\ErrorPage::meta(400)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 400])
@endsection
