<?php
namespace Tests\Feature\Auth;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
class AdminAuthenticationTest extends TestCase {
 use RefreshDatabase;
 private function admin(): User {
  app(PermissionRegistrar::class)->forgetCachedPermissions();
  $permission=Permission::findOrCreate('access-admin','web');
  $role=Role::findOrCreate('admin','web');$role->givePermissionTo($permission);
  $user=User::factory()->create();$user->assignRole($role);return $user;
 }
 public function test_guest_is_sent_to_dedicated_admin_login(): void {
  $this->get('/admin')->assertRedirect('/admin/login');
  $this->get('/admin/products')->assertRedirect('/admin/login');
  $this->get('/admin/login')->assertOk()->assertSee('Acceso administrativo')->assertDontSee('Crear cuenta')->assertDontSee('auth/google');
 }
 public function test_client_credentials_do_not_create_admin_session(): void {
  $user=User::factory()->create();
  $this->post('/admin/login',['email'=>$user->email,'password'=>'password'])->assertSessionHasErrors('email');
  $this->assertGuest();
 }
 public function test_administrator_can_log_in_and_access_dashboard(): void {
  $user=$this->admin();
  $this->post('/admin/login',['email'=>$user->email,'password'=>'password'])->assertRedirect('/admin');
  $this->assertAuthenticatedAs($user);$this->get('/admin')->assertOk();
 }
 public function test_wrong_admin_password_is_rejected(): void {
  $user=$this->admin();
  $this->post('/admin/login',['email'=>$user->email,'password'=>'wrong'])->assertSessionHasErrors('email');
  $this->assertGuest();
 }
 public function test_client_logged_in_normally_cannot_access_admin_pages_or_api(): void {
  $user=User::factory()->create();$this->actingAs($user);
  foreach(['/admin','/admin/login','/admin/products','/admin/orders','/admin/dashboard/orders','/admin/dashboard/summary'] as $url) $this->get($url)->assertForbidden();
 }
 public function test_public_registration_cannot_grant_admin_role(): void {
  $this->post('/register',['name'=>'Cliente','email'=>'new@example.test','password'=>'Password123!','password_confirmation'=>'Password123!','role'=>'admin','permissions'=>['access-admin']])->assertRedirect('/dashboard');
  $user=User::where('email','new@example.test')->firstOrFail();$this->assertFalse($user->can('access-admin'));
  $this->get('/admin')->assertForbidden();$this->get('/admin/register')->assertNotFound();
 }
 public function test_admin_login_is_rate_limited(): void {
  for($i=0;$i<6;$i++) $response=$this->post('/admin/login',['email'=>'bad@example.test','password'=>'wrong']);
  $response->assertSessionHasErrors('email');$this->assertGuest();
 }
}
