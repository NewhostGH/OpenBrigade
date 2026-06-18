@extends(\App\Support\ErrorPage::layout(413))

@section('title', \App\Support\ErrorPage::meta(413)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 413])
@endsection
