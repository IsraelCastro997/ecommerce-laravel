<?php
namespace App\Livewire\Store;
use App\Models\Order;
use Livewire\Attributes\Locked;
use Livewire\Component;
class OrderConfirmation extends Component {
 #[Locked]
 public int $orderId;
 public function mount(Order $order): void {
  abort_unless((int)$order->user_id === (int)auth()->id(),403);
  $this->orderId=$order->id;
 }
 public function refreshStatus(): void {}
 public function render() {
  $order=Order::where('user_id',auth()->id())->with('items')->findOrFail($this->orderId);
  return view('livewire.store.order-confirmation',compact('order'));
 }
}
