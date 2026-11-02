@extends('errors.layout')

@section('title', 'الصفحة غير موجودة')
@section('mark', '404')
@section('heading', 'لم نعثر على هذه الصفحة')
@section('description', 'قد يكون الرابط غير صحيح أو أن الصفحة تم نقلها أو حذفها.')

@section('actions')
    <a href="{{ url('/') }}">العودة للرئيسية</a>
@endsection
