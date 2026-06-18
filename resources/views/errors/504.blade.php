@extends(\App\Support\ErrorPage::layout(504))

@section('title', \App\Support\ErrorPage::meta(504)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 504])
@endsection
