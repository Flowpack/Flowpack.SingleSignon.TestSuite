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
}

?>