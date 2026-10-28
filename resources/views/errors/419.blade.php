@extends('errors.layout')

@section('title', 'انتهت الجلسة')
@section('tone', 'var(--warning)')
@section('tone-bg', 'var(--warning-bg)')
@section('mark', '419')
@section('heading', 'انتهت صلاحية الجلسة')
@section('description', 'لأسباب أمنية، انتهت جلسة العمل الحالية. يرجى تحديث الصفحة والمحاولة مرة أخرى.')

@section('actions')
    <a href="{{ url()->current() }}">تحديث الصفحة</a>
    <a href="{{ route('dashboard') }}" class="secondary">لوحة التحكم</a>
@endsection
