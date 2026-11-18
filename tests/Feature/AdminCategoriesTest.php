<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{Category,User};
use App\Livewire\Admin\Categories\CategoryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class AdminCategoriesTest extends TestCase
{
 use RefreshDatabase;
 public function test_categories_use_database_filters_and_server_pagination(): void {
  app(PermissionRegistrar::class)->forgetCachedPermissions();
  $user=User::factory()->create();$user->givePermissionTo(Permission::findOrCreate('access-admin','web'));$this->actingAs($user);
  $root=Category::forceCreate(['name'=>'Campamento','slug'=>'campamento','active'=>true]);
  Category::forceCreate(['name'=>'Tiendas','slug'=>'tiendas','active'=>true,'parent_id'=>$root->id]);
  Category::forceCreate(['name'=>'Archivo','slug'=>'archivo','active'=>false]);
  $this->get('/admin/categories')->assertOk()->assertSee('Campamento')->assertDontSee('Datos de demostración');
  Livewire::test(CategoryManager::class)->set('search','tiendas')
   ->assertViewHas('categories',fn($rows)=>$rows->total()===1 && $rows->first()->name==='Tiendas')
   ->call('clearFilters')->set('status','inactive')->assertViewHas('categories',fn($rows)=>$rows->total()===1 && $rows->first()->name==='Archivo')
   ->call('clearFilters')->set('parent',(string)$root->id)->assertViewHas('categories',fn($rows)=>$rows->total()===1 && $rows->first()->name==='Tiendas');
  for($i=0;$i<16;$i++) Category::forceCreate(['name'=>'Grupo '.$i,'slug'=>'grupo-'.$i,'active'=>true]);
  Livewire::test(CategoryManager::class)->assertViewHas('categories',fn($rows)=>$rows->total()===19 && $rows->count()===15)
   ->call('setPage',2)->set('search','tiendas')->assertViewHas('categories',fn($rows)=>$rows->currentPage()===1 && $rows->total()===1);
 }
 public function test_customer_cannot_access_page_or_livewire_component(): void {
  $user=User::factory()->create();$this->actingAs($user);
  $this->get('/admin/categories')->assertForbidden();
  Livewire::test(CategoryManager::class)->assertForbidden();
 }
}
