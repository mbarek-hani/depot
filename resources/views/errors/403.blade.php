@extends('errors.layout')

@section('title', 'غير مصرح')
@section('tone', 'var(--danger)')
@section('tone-bg', 'var(--danger-bg)')
@section('mark', '403')
@section('heading', 'لا تملك صلاحية الوصول')
@section('description', 'ليست لديك صلاحية لعرض هذه الصفحة أو تنفيذ هذا الإجراء.')

@section('actions')
    <a href="{{ route('dashboard') }}">العودة للوحة التحكم</a>
@endsection
