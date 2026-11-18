<nav class="blade-nav" aria-label="Mi cuenta">
 @foreach(['account.index' => 'Resumen', 'account.orders.index' => 'Pedidos', 'account.addresses' => 'Direcciones', 'account.currency' => 'Moneda', 'profile.edit' => 'Perfil', 'password.edit' => 'Contraseña', 'appearance' => 'Apariencia'] as $name => $label)
 <a href="{{ route($name) }}" @if(request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
 @endforeach
</nav>
