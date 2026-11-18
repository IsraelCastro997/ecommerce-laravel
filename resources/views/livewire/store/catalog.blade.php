<div class="store-wrap catalog-page">
    <nav class="catalog-breadcrumb" aria-label="Ruta de navegación"><a href="{{ route('home') }}">Inicio</a><x-store-icon name="chevron" size="12" /><span aria-current="page">Catálogo</span></nav>
    <section class="catalog-intro" aria-labelledby="catalog-cover-title">
        <img class="catalog-intro-photo" src="{{ asset('store/camp.png') }}" alt="Tienda de campaña frente a las montañas y un bosque al amanecer" width="2172" height="724" fetchpriority="high" decoding="async">
        <div class="catalog-intro-copy">
            <span class="catalog-intro-label"><x-store-icon name="mountain" size="21" /> Refugio Outdoor</span>
            <h1 id="catalog-cover-title">Lo esencial para<br>salir de lo cotidiano.</h1>
            <p>Del primer sendero a la última noche bajo las estrellas. Encuentra el equipo para tu próxima salida.</p>
            <a href="#catalogo" class="catalog-intro-link">Explorar productos <x-store-icon name="arrow" size="18" /></a>
        </div>
        <span class="catalog-intro-signature"><x-store-icon name="mountain" size="18" /> Nos vemos afuera.</span>
    </section>
    <x-catalog-preview />
    <section class="catalog-bottom-note"><x-store-icon name="mountain" size="32" /><div><h2>Elige menos. Explora más.</h2><p>Un buen equipo empieza por lo que de verdad necesitas.</p></div><a href="{{ route('home') }}#contacto" class="catalog-text-link">¿Te ayudamos a elegir? <x-store-icon name="arrow" size="17" /></a></section>
</div>
