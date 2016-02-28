<?php

class MbCategoryServiceTest extends Orchestra\Testbench\TestCase {

    protected $categoryService;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();

        // Call migrations
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../src/migrations'),
        ]);

        // Call migrations specific to our tests
        $this->artisan('migrate', array(
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/migrations'),
        ));

        // Call seeding
        $this->artisan('db:seed', ['--class' => 'MessageBoardSeeder']);

        $this->categoryService = $this->app->make('MicheleAngioni\MessageBoard\Services\CategoryService');
    }

    /**
     * Define environment setup.
     *
     * @param Illuminate\Foundation\Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', array(
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ));
    }

    /**
     * Get package providers. At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return array(
            'MicheleAngioni\Support\SupportServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\MessageBoardServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\NotificationsServiceProvider'
        );
    }

    /**
     * Get package aliases. In a normal app environment these would be added to
     * the 'aliases' array in the config/app.php file. If your package exposes an
     * aliased facade, you should add the alias here, along with aliases for
     * facades upon which your package depends, e.g. Cartalyst/Sentry
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return array(
            'Config' => 'Illuminate\Support\Facades\Config',
            'Helpers' => 'MicheleAngioni\Support\Facades\Helpers',
        );
    }


	public function testMainCategoryMethods()
	{
        $this->assertEquals(0, $this->categoryService->getCategories()->count());

        $category = $this->categoryService->createCategory('category', 'pic.jpg');
        $this->assertInstanceOf('MicheleAngioni\MessageBoard\Models\Category', $category);
        $this->assertEquals(1, $this->categoryService->getCategories()->count());
        $this->assertEquals($category->getKey(), $this->categoryService->getCategory($category->getKey())->getKey());
        $this->assertEquals($category->getKey(), $this->categoryService->getCategory('category')->getKey());

        $this->categoryService->updateCategory($category->getKey(), ['name' => 'new category']);

        $updatedCategory= $this->categoryService->getCategory($category->getKey());
        $this->assertEquals('new category', $updatedCategory->getName());

        $this->assertTrue($this->categoryService->deleteCategory($category->getKey()));

        $this->assertEquals(0, $this->categoryService->getCategories()->count());
    }

    /**
     * @expectedException \MicheleAngioni\Support\Exceptions\PermissionsException
     */
    public function testNotAllowedDelete()
    {
        $user = new UserCategory();
        $user->id = 1;
        $user->username = 'Username';

        $category = $this->categoryService->createCategory('category', 'pic.jpg');

        $this->categoryService->deleteCategory($category->getKey(), $user);
    }

    public function testAllowedDelete()
    {
        $user = $this->mock('MicheleAngioni\MessageBoard\Contracts\MbUserInterface');
        $user->shouldReceive('canMb')->with('Manage Categories')->andReturn(true);

        $category = $this->categoryService->createCategory('category', 'pic.jpg');

        $this->assertTrue($this->categoryService->deleteCategory($category->getKey(), $user));

        $this->assertEquals(0, $this->categoryService->getCategories()->count());
    }


    public function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
    }

    public function tearDown()
    {
        Mockery::close();
    }

}


/**
 * A stub class that implements MbUserInterface and uses the MbTrait trait.
 */
class UserCategory extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface
{
    use MicheleAngioni\MessageBoard\MbTrait;

    protected $table = 'users';

    public function getPrimaryId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
