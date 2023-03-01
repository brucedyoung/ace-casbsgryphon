<?php

class GoogleAnalyticsCest {

  /**
   * Validate Google Analytics is available.
   */
  public function testGoogleAnalytics(AcceptanceTester $I) {
    // check the value is available from the `google_analytics.settings.php` file.
    $I->logInWithRole('administrator');
    $I->amOnPage('/admin/config/services/google-analytics');
    $I->canSeeResponseCodeIs(200);
    $I->seeInField('form input[type=text]', 'UA-123456-12');

    // check the value is available in the source of the homepage
    $I->amOnPage('/user/logout');
    $I->amOnPage('/');
    $I->seeInSource('UA-123456-12');
  }

}
