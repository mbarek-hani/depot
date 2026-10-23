@if (session('status'))
    <x-ui.alert tone="success">{{ session('status') }}</x-ui.alert>
@endif

@if (session('error'))
    <x-ui.alert tone="danger">{{ session('error') }}</x-ui.alert>
@endif

@if ($errors->any() && ! $errors->hasAny(['email', 'password', 'password_confirmation', 'name']))
    <x-ui.alert tone="danger">يرجى مراجعة الحقول المطلوبة والمحاولة مرة أخرى.</x-ui.alert>
@endif
