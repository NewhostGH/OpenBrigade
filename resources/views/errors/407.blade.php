@extends(\App\Support\ErrorPage::layout(407))

@section('title', \App\Support\ErrorPage::meta(407)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 407])
@endsection
