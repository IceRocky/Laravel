<div>
    @json($errors->toArray())

    {{ session()->has('errors') && session()->get('errors')->has('bar') ? 'sessionError:'.session()->get('errors')->first('bar') : '' }}

    @error('test') @enderror

    @component('dump-errors-nested-component')@endcomponent
</div>
