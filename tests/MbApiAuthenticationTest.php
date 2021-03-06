<?php

class MbApiAuthenticationTest extends Orchestra\Testbench\TestCase
{
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
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]);

        $app['config']->set('auth.providers.users.model', UserAuthApi::class);

        $app['config']->set('jwt.secret', 'JWTSECRET');
        $app['config']->set('jwt.ttl', '60');
        $app['config']->set('jwt.refresh_ttl', '20160');
        $app['config']->set('jwt.algo', 'HS256');
        $app['config']->set('jwt.user', UserAuthApi::class);
        $app['config']->set('jwt.identifier', 'id');
        $app['config']->set('jwt.required_claims', ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti']);
        $app['config']->set('jwt.blacklist_enabled', true);
        $app['config']->set('jwt.providers.user', 'Tymon\JWTAuth\Providers\User\EloquentUserAdapter');
        $app['config']->set('jwt.providers.jwt', 'Tymon\JWTAuth\Providers\JWT\NamshiAdapter');
        $app['config']->set('jwt.providers.auth', function ($app) {
            return new Tymon\JWTAuth\Providers\Auth\IlluminateAuthAdapter($app['auth']);
        });
        $app['config']->set('jwt.providers.storage', function ($app) {
            return new Tymon\JWTAuth\Providers\Storage\IlluminateCacheAdapter($app['cache']);
        });

        $app['config']->set('ma_messageboard.model', 'UserAuthApi');
        $app['config']->set('ma_messageboard.api.authentication', true);
        $app['config']->set('ma_messageboard.api.notifications', true);
        $app['config']->set('ma_messageboard.api.v1_enabled', true);
        $app['config']->set('ma_messageboard.api.api_prefix', '/api');
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
            'Mews\Purifier\PurifierServiceProvider',
            'MicheleAngioni\Support\SupportServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\MessageBoardServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\NotificationsServiceProvider',
            'MicheleAngioni\MessageBoard\Providers\MessageBoardAPIServiceProvider',
            'Tymon\JWTAuth\Providers\JWTAuthServiceProvider'
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
            'JWTAuth' => 'Tymon\JWTAuth\Facades\JWTAuth'
        );
    }

    /**
     * Get application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app)
    {
        return 'UTC';
    }

    
	public function testAuthenticateUser()
	{
        $this->withoutMiddleware();

        // Create a new User and add it to the Database
        $user = new UserAuthApi;
        $user->id = 1;
        $user->username = 'Username';
        $user->email = 'email@email.com';
        $user->password = bcrypt('password');
        $user->save();

        // Call the API logout, by adding the Authentication header (i.e., the Token)
        $this->json('POST', '/api/v1/auth',
            [
                'email' => 'email@email.com',
                'password' => 'password'
            ], //parameters
            [
                'X-Requested-With' => 'XMLHttpRequest'
            ] // server
        )
            ->seeStatusCode(200)
            ->see('token');
    }

    /**
     * @expectedException \Tymon\JWTAuth\Exceptions\TokenBlacklistedException
     */
    public function testLogout()
    {
        $this->withoutMiddleware();

        // Create a new User and add it to the Database
        $user = new UserAuthApi;
        $user->id = 1;
        $user->username = 'Username';
        $user->password = bcrypt(str_random(10));
        $user->save();

        // Login as this User
        $token = JWTAuth::fromUser($user);


        // Call the API logout, by adding the Authentication header (i.e., the Token)
        $this->json('DELETE', '/api/v1/auth',
            [
            ], //parameters
            [
                'X-Requested-With' => 'XMLHttpRequest',
                'HTTP_Authorization' => 'Bearer ' . $token
            ] // server
        )
            ->seeStatusCode(200);

        JWTAuth::toUser();
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
class UserAuthApi extends \Illuminate\Database\Eloquent\Model implements \MicheleAngioni\MessageBoard\Contracts\MbUserInterface,
                                                                         \Illuminate\Contracts\Auth\Authenticatable
{
    use Illuminate\Auth\Authenticatable;
    use MicheleAngioni\MessageBoard\MbTrait;
    use MicheleAngioni\MessageBoard\Notifable;

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
