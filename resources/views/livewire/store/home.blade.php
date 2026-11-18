<div class="store-wrap">
 <section class="store-hero"><div class="hero-copy"><p class="eyebrow"><span></span>EQUIPO PARA EXPLORAR</p><h1>El camino<br>empieza afuera.</h1><p>Prepara tu próxima salida con equipo para el sendero, el campamento y todo lo que hay en medio.</p><a class="store-button primary" href="{{ route('store.catalog') }}">Explorar catálogo →</a></div></section>
 <section id="categorias" class="store-section"><div class="section-heading"><div><p class="eyebrow">ENCUENTRA TU EQUIPO</p><h2>Explora por categoría</h2></div></div><div class="category-grid">
 @foreach(['Mochilas' => '↟', 'Campamento' => '△', 'Hidratación' => '◉', 'Iluminación' => '☼'] as $category => $symbol)
 <a href="{{ route('store.catalog', ['categoria' => $category]) }}"><span class="category-icon" aria-hidden="true">{{ $symbol }}</span><strong>{{ $category }}</strong><span class="category-arrow" aria-hidden="true">→</span></a>
 @endforeach
 </div></section>
 <x-catalog-preview :compact="true" />
 <section class="store-benefits"><div><strong>Equipo que acompaña</strong><span>Diseñado para disfrutar el camino.</span></div><div><strong>Compra a tu ritmo</strong><span>Explora el catálogo en MXN o USD.</span></div><div><strong>Tu próxima aventura</strong><span>Encuentra inspiración para salir.</span></div></section>
 <section class="blade-panel" id="contacto"><h2 class="text-xl font-bold">Estamos para ayudarte</h2><p>Esta es una tienda de demostración. No se procesan compras ni cobros.</p><details class="mt-4"><summary>Envíos y devoluciones</summary><p>Las políticas de envío y devolución se publicarán cuando la tienda esté operativa.</p></details><details class="mt-3"><summary>Privacidad y términos</summary><p>Las interacciones de catálogo y bolsa son locales al navegador. El acceso a tu cuenta utiliza la sesión de Laravel.</p></details></section>
</div>
