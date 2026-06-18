@extends(\App\Support\ErrorPage::layout(412))

@section('title', \App\Support\ErrorPage::meta(412)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 412])
@endsection
