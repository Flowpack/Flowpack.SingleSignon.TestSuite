<?php

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\BehatContext,
	Behat\MinkExtension\Context\MinkContext,
	Behat\Behat\Exception\PendingException,
	Behat\Behat\Event\ScenarioEvent,
	Behat\Behat\Event\StepEvent;
use Behat\Gherkin\Node\PyStringNode,
	Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
require_once('PHPUnit/Autoload.php');

use PHPUnit_Framework_Assert as Assert;

/**
 * Features context.
 */
class FeatureContext extends MinkContext {

	/**
	 * @var string
	 */
	protected $instanceBaseUri;

	/**
	 * @var string
	 */
	protected $instance2BaseUri;

	/**
	 * @var string
	 */
	protected $serverBaseUri;

	/**
	 * @var boolean
	 */
	protected $debug = FALSE;

	/**
	 * Initializes context.
	 * Every scenario gets it's own context object.
	 *
	 * @param array $parameters context parameters (set them up through behat.yml)
	 */
	public function __construct(array $parameters) {
		if (isset($parameters['instance_base_uri'])) {
			$this->instanceBaseUri = $parameters['instance_base_uri'];
		}
		if (isset($parameters['instance2_base_uri'])) {
			$this->instance2BaseUri = $parameters['instance2_base_uri'];
		}
		if (isset($parameters['server_base_uri'])) {
			$this->serverBaseUri = $parameters['server_base_uri'];
		}
		if (isset($parameters['debug']) && $parameters['debug'] === TRUE) {
			$this->debug = TRUE;
		}
		$this->testService = new Guzzle\Http\Client($this->serverBaseUri);
	}

	/**
	 * @BeforeScenario @fixtures
	 *
	 * @param Behat\Behat\Event\ScenarioEvent $event
	 */
	public function resetTestFixtures(ScenarioEvent $event) {
		$this->testService->post('test/user/reset')->send();
	}

	/**
	 * @AfterStep
	 */
	public function showResponseOnException(StepEvent $event) {
		if ($this->debug && $event->getResult() === \Behat\Behat\Event\StepEvent::FAILED) {
			$this->printLastResponse();
		}
	}

	/**
	 * @Given /^I am on the instance homepage$/
	 */
	public function iAmOnTheInstanceHomepage() {
		$this->visit($this->instanceBaseUri);
	}

	/**
	 * @Given /^I am not authenticated on the server or the instance$/
	 */
	public function iAmNotAuthenticatedOnTheServerOrTheInstance() {
		// No op, should be the default
	}

	/**
	 * @When /^I click on the link "([^"]*)"$/
	 */
	public function iClickOnTheLink($link) {
		$this->clickLink($link);
	}

	/**
	 * @Then /^I should be redirected to the server$/
	 */
	public function iShouldBeRedirectedToTheServer() {
		Assert::assertStringStartsWith($this->serverBaseUri, $this->getSession()->getCurrentUrl(), 'URI should start with server base URI');
	}

	/**
	 * @Then /^I should see a login form$/
	 */
	public function iShouldSeeALoginForm() {
		$this->assertSession()->elementExists('css', 'form input[value="Login"]');
	}

	/**
	 * @Then /^I should be redirected to the instance$/
	 */
	public function iShouldBeRedirectedToTheInstance() {
		Assert::assertStringStartsWith($this->instanceBaseUri, $this->getSession()->getCurrentUrl(), 'URI should start with instance base URI');
	}

	/**
	 * @Then /^the URI should not contain SSO parameters$/
	 */
	public function theUriShouldNotContainSsoParameters() {
		Assert::assertNotContains('__typo3[singlesignon][accessToken]', $this->getSession()->getCurrentUrl(), 'URI should not contain SSO parameters');
	}

	/**
	 * @Given /^I should be logged in as "([^"]*)"$/
	 */
	public function iShouldBeLoggedInAs($accountIdentifier) {
		$this->assertElementContainsText('#login-status', 'Logged in as: ' . $accountIdentifier);
	}

	/**
	 * @Given /^I should have the role "([^"]*)"$/
	 */
	public function iShouldHaveTheRole($roleIdentifier) {
		$this->assertElementContainsText('#login-status', 'Roles: ' . $roleIdentifier);
	}

	/**
	 * @Given /^I wait so long that my session on the instance expires$/
	 */
	public function iWaitSoLongThatMySessionOnTheInstanceExpires() {
		// This code only works with the Goutte driver which
		// uses a patched version of BrowserKit to fix multi-domain
		// cookie handling
		$client = $this->getSession()->getDriver()->getClient();
		$cookieJar = $client->getCookieJar();
		$instanceCookies = $cookieJar->allValues($this->instanceBaseUri);

		$parts = parse_url($this->instanceBaseUri);
		foreach ($instanceCookies as $key => $value) {
			$cookieJar->expire($key, '/', $parts['host']);
		}
	}

	/**
	 * @Then /^I have the correct session cookie on the server$/
	 */
	public function iHaveTheCorrectSessionCookieOnTheServer() {
		$this->visit($this->serverBaseUri);
	}

	/**
	 * @Given /^There is a server user:$/
	 */
	public function thereIsAServerAccount(TableNode $accounts) {
		$userProperties = $accounts->getRowsHash();

		$response = $this->testService->post('test/user/create', NULL, array(
			'user[username]' => $userProperties['username'],
			'user[firstname]' => $userProperties['firstname'],
			'user[lastname]' => $userProperties['lastname'],
			'user[company]' => $userProperties['company'],
			'user[role]' => $userProperties['roles'],
			'password' => $userProperties['password'],
		))->send();
	}

	/**
	 * @Given /^there is a mapping from server to instance users$/
	 */
	public function thereIsAMappingFromFirstnameAndLastnameToFullname() {
		// No op, since it's defined on the server
	}

	/**
	 * @When /^I log in to the secured page with "([^"]*)" and "([^"]*)"$/
	 */
	public function iLogInToTheSecuredPageWithAnd($username, $password) {
		$this->visit($this->instanceBaseUri . 'acme.demoinstance/standard/secure');
		$this->assertSession()->elementExists('css', 'form input[value="Login"]');
		$this->fillField('Username', $username);
		$this->fillField('Password', $password);
		$this->pressButton('Login');
		Assert::assertStringStartsWith($this->instanceBaseUri, $this->getSession()->getCurrentUrl(), 'URI should start with instance base URI after login');
	}

	/**
	 * @Then /^I should have a login name "([^"]*)"$/
	 */
	public function iShouldHaveALoginName($loginName) {
		$this->assertElementContainsText('#login-status', 'Name: ' . $loginName);
	}

	/**
	 * @Given /^I visit a protected resource$/
	 */
	public function iVisitAProtectedResource() {
		$this->visit($this->instanceBaseUri . 'acme.demoinstance/standard/secure');
	}

	/**
	 * @Then /^I should not be redirected$/
	 */
	public function iShouldNotBeRedirected() {
		$client = $this->getSession()->getDriver()->getClient();
		$history = clone $client->getHistory();
		$previousRequest = $history->back();
		Assert::assertStringStartsWith($this->instanceBaseUri, $previousRequest->getUri(), 'Previous request URI should start with instance base URI');
	}

	/**
	 * @Given /^I am logged in to the secured page on the instance?$/
	 */
	public function iAmLoggedInToTheSecuredPage() {
		$this->iLogInToTheSecuredPageWithAnd('admin', 'password');
	}

	/**
	 * @Given /^I should not be authenticated$/
	 */
	public function iShouldNotBeAuthenticated() {
		$this->assertPageContainsText('Not authenticated');
	}

	/**
	 * @When /^I visit the server homepage$/
	 */
	public function iVisitTheServerHomepage() {
		$this->visit($this->serverBaseUri);
	}

	/**
	 * @When /^I visit the instance homepage$/
	 */
	public function iVisitTheInstanceHomepage() {
		$this->visit($this->instanceBaseUri);
	}

	/**
	 * @Given /^I am logged in to the secured page on instance(\d+)$/
	 */
	public function iAmLoggedInToTheSecuredPageOnInstance($instanceNumber) {
		$baseUri = $this->baseUriForInstance($instanceNumber);
		$this->visit($baseUri . 'acme.demoinstance/standard/secure');
		if (strpos($this->getSession()->getCurrentUrl(), $this->serverBaseUri) === 0) {
			$this->assertSession()->elementExists('css', 'form input[value="Login"]');
			$this->fillField('Username', 'admin');
			$this->fillField('Password', 'password');
			$this->pressButton('Login');
		}
		Assert::assertStringStartsWith($baseUri, $this->getSession()->getCurrentUrl(), 'URI should start with instance ' . $instanceNumber . ' base URI after login');

	}

	/**
	 * @When /^I visit the instance(\d+) homepage$/
	 */
	public function iVisitAnInstanceHomepage($instanceNumber) {
		$this->visit($this->baseUriForInstance($instanceNumber));
	}

	/**
	 * @Then /^I should not be authenticated on instance(\d+)$/
	 */
	public function iShouldNotBeAuthenticatedOnInstance($instanceNumber) {
		$this->visit($this->baseUriForInstance($instanceNumber));
		$this->assertPageContainsText('Not authenticated');
	}

	/**
     * @When /^The global session expires somehow$/
     */
    public function theGlobalSessionExpiresSomehow() {
		$response = $this->testService->post('test/session/destroyall')->send();
    }

	/**
     * @Given /^I wait some seconds$/
     */
    public function iWaitSomeSeconds() {
        sleep(5);
    }

	/**
	 * @param integer $instanceNumber
	 * @return string
	 */
	protected function baseUriForInstance($instanceNumber) {
		$baseUri = NULL;
		if ($instanceNumber == 1) {
			$baseUri = $this->instanceBaseUri;
		} elseif ($instanceNumber == 2) {
			$baseUri = $this->instance2BaseUri;
		}
		if ($baseUri === NULL) {
			throw new \Behat\Behat\Exception\PendingException('Base URI for instance ' . $instanceNumber . ' not configured.');
		}
		return $baseUri;
	}

}

?>