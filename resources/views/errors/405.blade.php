@extends(\App\Support\ErrorPage::layout(405))

@section('title', \App\Support\ErrorPage::meta(405)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 405])
@endsection
