@props(['product' => null])
<section class="ro-panel" x-data="productEditor">
<p class="ro-notice" role="status" x-show="message" x-text="message" x-cloak></p>
<p class="ro-error" role="alert" x-show="failure" x-text="failure" x-cloak></p>
@if(session('status'))<p role="status" class="ro-notice">{{ session('status') }}</p>@endif
@if($errors->any())<div role="alert" class="ro-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="blade-form" x-ref="form" novalidate @submit="
    if (saving) { $event.preventDefault(); return; }
    message = ''; failure = '';
    for (const field of $refs.form.elements) validate(field);
    if (!$refs.form.checkValidity()) {
        $event.preventDefault();
        $refs.form.querySelector(':invalid')?.focus();
        return;
    }
    saving = true;
" @focusout="validate($event.target)" @input="changed($event.target)" @change="changed($event.target)" method="POST" action="{{ $product ? route('admin.products.update',$product) : route('admin.products.store') }}">
@csrf
@if($product) @method('PUT') @endif
<div class="blade-columns">
@foreach(['sku'=>'SKU','name'=>'Nombre','slug'=>'Slug (ejemplo: mochila-sierra)','origin_country'=>'País de origen (MX, US…)'] as $field=>$label)
<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$product?->{$field}) }}" maxlength="{{ ['sku'=>64,'name'=>255,'slug'=>220,'origin_country'=>2][$field] }}" @required($field !== 'origin_country') @if($field === 'name') pattern="[A-Za-z0-9]+( [A-Za-z0-9]+)*" title="Solo letras sin acentos, números y espacios" @endif @if($field === 'slug') pattern="[a-z0-9]+(-[a-z0-9]+)*" title="Usa minúsculas, números y guiones" @endif @if($field === 'origin_country') pattern="[A-Z]{2}" title="Usa dos letras mayúsculas, por ejemplo MX" @endif><span class="ro-error" role="alert" x-show="errors['{{ $field }}']?.length" x-text="errors['{{ $field }}']?.join(' ')" x-cloak></span></label>
@endforeach
</div>
@foreach(['short_description'=>'Descripción corta','long_description'=>'Descripción larga'] as $field=>$label)
<label>{{ $label }}<textarea name="{{ $field }}" rows="{{ $field === 'short_description' ? 2 : 6 }}" required maxlength="{{ $field === 'short_description' ? 500 : 20000 }}">{{ old($field,$product?->{$field} ?? ($field === 'long_description' ? $product?->description : '')) }}</textarea><span class="ro-error" role="alert" x-show="errors['{{ $field }}']?.length" x-text="errors['{{ $field }}']?.join(' ')" x-cloak></span></label>
@endforeach
<div class="blade-columns">
<label>Marca<select name="brand_id"><option value="">Sin marca</option>@foreach(\App\Models\Brand::orderBy('name')->get(['id','name']) as $brand)<option value="{{ $brand->id }}" @selected(old('brand_id',$product?->brand_id)==$brand->id)>{{ $brand->name }}</option>@endforeach</select><span class="ro-error" role="alert" x-show="errors.brand_id?.length" x-text="errors.brand_id?.join(' ')" x-cloak></span></label>
<label>Categoría principal<select name="primary_category_id"><option value="">Sin categoría</option>@foreach(\App\Models\Category::orderBy('name')->get(['id','name']) as $category)<option value="{{ $category->id }}" @selected(old('primary_category_id',$product?->primary_category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select><span class="ro-error" role="alert" x-show="errors.primary_category_id?.length" x-text="errors.primary_category_id?.join(' ')" x-cloak></span></label>
<label>Estado<select name="status">@foreach(['draft'=>'Borrador','active'=>'Activo','inactive'=>'Inactivo'] as $value=>$label)<option value="{{ $value }}" @selected(old('status',$product?->status ?? 'draft')===$value)>{{ $label }}</option>@endforeach</select><span class="ro-error" role="alert" x-show="errors.status?.length" x-text="errors.status?.join(' ')" x-cloak></span></label>
@foreach(['available_from'=>'Disponible desde','available_until'=>'Disponible hasta'] as $field=>$label)<label>{{ $label }}<input type="datetime-local" @required($field === 'available_until') name="{{ $field }}" value="{{ old($field,$product?->{$field}?->format('Y-m-d\TH:i')) }}"><span class="ro-error" role="alert" x-show="errors['{{ $field }}']?.length" x-text="errors['{{ $field }}']?.join(' ')" x-cloak></span></label>@endforeach
</div>
@php
 $currentPrice = $product?->priceSnapshots()->whereHas('pricePublication',fn($q)=>$q->where('is_active',true)->where('status','published'))->latest('price_publication_id')->first();
 $currentStock = $product ? $product->inventoryBalances()->sum('qty_on_hand') : 0;
@endphp
<section class="product-editor-section"><h2>Precios y existencias</h2><div class="blade-columns">
@foreach(['price_usd'=>'Precio USD','price_mxn'=>'Precio MXN','stock'=>'Stock total'] as $field=>$label)
<label>{{ $label }}<input required type="number" name="{{ $field }}" min="{{ $field === 'stock' ? 0 : '0.01' }}" max="{{ $field === 'stock' ? 4294967295 : '999999.99' }}" step="{{ $field === 'stock' ? 1 : '0.01' }}" value="{{ old($field,$field === 'stock' ? $currentStock : $currentPrice?->{$field}) }}"><span class="ro-error" role="alert" x-show="errors['{{ $field }}']?.length" x-text="errors['{{ $field }}']?.join(' ')" x-cloak></span></label>
@endforeach
</div><label>Costo unitario histórico MXN<input type="number" name="historical_cost_mxn" required min="0" max="999999.9999" step="0.0001" value="{{ old('historical_cost_mxn',$currentPrice?->historical_cost_mxn) }}"><span class="ro-error" role="alert" x-show="errors.historical_cost_mxn?.length" x-text="errors.historical_cost_mxn?.join(' ')" x-cloak></span></label>
<p class="ro-footnote">El costo se conserva en el detalle de cada pedido para calcular la ganancia sin depender de cambios futuros del catálogo.</p>
<p class="ro-footnote">El stock total incluye las unidades reservadas. No puede reducirse por debajo de ellas.</p></section>
<label>URL de imagen<input required type="url" pattern="https?://.*" name="image_url" maxlength="2048" value="{{ old('image_url',$product ? ($product->image_url ?: sprintf('https://picsum.photos/seed/product-%d/640/640',$product->id)) : '') }}" placeholder="https://ejemplo.com/producto.jpg"><span class="ro-error" role="alert" x-show="errors.image_url?.length" x-text="errors.image_url?.join(' ')" x-cloak></span></label>
<div class="blade-actions"><button class="store-button primary" type="submit" :disabled="saving" x-text="saving ? 'Guardando…' : 'Guardar producto'">Guardar producto</button><a class="store-button" href="{{ route('admin.products.index') }}">Volver al catálogo</a></div>
</form></section>
