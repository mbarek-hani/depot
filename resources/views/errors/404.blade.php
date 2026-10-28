@extends('errors.layout')

@section('title', 'الصفحة غير موجودة')
@section('mark', '404')
@section('heading', 'لم نعثر على هذه الصفحة')
@section('description', 'قد يكون الرابط غير صحيح أو أن الصفحة تم نقلها أو حذفها.')

@section('actions')
    @auth
        <a href="{{ route('dashboard') }}">لوحة التحكم</a>
    @else
        <a href="{{ route('login') }}">تسجيل الدخول</a>
    @endauth
    <a href="{{ url()->previous() }}" class="secondary">رجوع</a>
@endsection
