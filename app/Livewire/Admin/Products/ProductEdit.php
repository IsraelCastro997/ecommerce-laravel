<?php
namespace App\Livewire\Admin\Products;
use App\Models\Product;
use Livewire\Attributes\Locked;
use Livewire\Component;
class ProductEdit extends Component {
 #[Locked]
 public Product $product;
 public function mount(Product $product): void { $this->product=$product; }
 public function render() { return view('livewire.admin.products.product-edit'); }
}
