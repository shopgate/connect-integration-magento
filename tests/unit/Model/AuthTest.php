<?php
namespace Tests;
use PHPUnit\Framework\TestCase;
require(MAGE_EXTENSION_DIR . "Model/Auth.php");
/**
 * Class AuthTest.
 */
class AuthTest extends TestCase
{
    /**
     * Auth class instance
     *
     * @var Shopgate_Cloudapi_Model_Auth
     */
    private $auth;
    public function __construct()
    {
        $this->auth = new \Shopgate_Cloudapi_Model_Auth();
        parent::__construct();
    }
    public function testInstanceAuth()
    {
        $this->assertInstanceOf(\Shopgate_Cloudapi_Model_Auth::class, $this->auth);
    }
  
}