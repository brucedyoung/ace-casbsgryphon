<?php

class SsoLoginRedirectCest {

  public function testLoginRedirect(AcceptanceTester $I) {
    $I->amOnPage('/sso/login');
    $I->canSeeInCurrentUrl('/saml_login');

    $I->amOnPage('/sso/login-documentation');
    $I->canSeeInCurrentUrl('/sso/login-documentation');

    $I->amOnPage('/sso/login?foo=bar');
    $I->canSeeInCurrentUrl('/saml_login?foo=bar');
  }

}
