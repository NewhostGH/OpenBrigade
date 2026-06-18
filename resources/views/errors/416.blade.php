@extends(\App\Support\ErrorPage::layout(416))

@section('title', \App\Support\ErrorPage::meta(416)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 416])
@endsection
