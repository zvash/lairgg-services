@extends('layouts.master')

@section('content')
    <div class="m-b-md">
        <h1 class="title">{{ __('verification.success.title') }}</h1>
        <p>{!! __('verification.success.message', ['fullname' => ucwords($user->full_name)]) !!}</p>
    </div>
@endsection
