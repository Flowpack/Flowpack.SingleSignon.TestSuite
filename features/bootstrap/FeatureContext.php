<?php

use Behat\Behat\Context\ClosuredContextInterface,
	Behat\Behat\Context\TranslatedContextInterface,
	Behat\Behat\Context\BehatContext,
	Behat\MinkExtension\Context\MinkContext,
	Behat\Behat\Exception\PendingException;
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
	protected $serverBaseUri;

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
		if (isset($parameters['server_base_uri'])) {
			$this->serverBaseUri = $parameters['server_base_uri'];
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
	 * @Given /^I should see a login form$/
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
     * @Given /^the URI should not contain SSO parameters$/
     */
    public function theUriShouldNotContainSsoParameters() {
		throw new \Behat\Behat\Exception\PendingException();

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
	 * @Given /^There is a server account:$/
	 */
	public function thereIsAServerAccount(TableNode $accounts) {
		var_dump($accounts->getRowsHash());
	}

	/**
	 * @Given /^There is a mapping for the party name$/
	 */
	public function thereIsAMappingForThePartyName() {
		throw new PendingException();
	}

	/**
	 * @When /^I log in to the secured page$/
	 */
	public function iLogInToTheSecuredPage() {
		throw new PendingException();
	}

	/**
	 * @Then /^I should have a login name "([^"]*)"$/
	 */
	public function iShouldHaveALoginName($loginName) {
		throw new PendingException();
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
}
?>