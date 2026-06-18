@extends(\App\Support\ErrorPage::layout(419))

@section('title', \App\Support\ErrorPage::meta(419)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 419])
@endsection
