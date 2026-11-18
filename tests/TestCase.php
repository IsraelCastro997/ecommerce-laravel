<?php
namespace Tests;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
abstract class TestCase extends BaseTestCase {
 public function createApplication() {
  $app = parent::createApplication();
  if ($app->environment('testing') && ($app['config']->get('database.default') !== 'mariadb' || $app['config']->get('database.connections.mariadb.database') !== 'ecommerce_frontend_testing')) {
   throw new \RuntimeException('Las pruebas requieren exclusivamente ecommerce_frontend_testing en MariaDB.');
  }
  return $app;
 }
}
