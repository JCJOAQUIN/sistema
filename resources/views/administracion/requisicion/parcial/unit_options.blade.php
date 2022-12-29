@foreach ($units as $u)
    <option value="{{ $u->name }}">{{ $u->name }}</option>
@endforeach