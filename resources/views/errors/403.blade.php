@extends('errors.layout')

@section('title', 'غير مصرح')
@section('tone', 'var(--danger)')
@section('tone-bg', 'var(--danger-bg)')
@section('mark', '403')
@section('heading', 'لا تملك صلاحية الوصول')
@section('description', 'ليست لديك صلاحية لعرض هذه الصفحة أو تنفيذ هذا الإجراء.')

@section('actions')
    @auth
        <a href="{{ route('dashboard') }}">العودة للوحة التحكم</a>
    @else
        <a href="{{ route('login') }}">تسجيل الدخول</a>
    @endauth
    <a href="{{ url()->previous() }}" class="secondary">رجوع</a>
@endsection
